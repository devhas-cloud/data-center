<?php

namespace App\Console\Commands;

use App\Mail\AutoReportMail;
use App\Models\AutoReportModel;
use App\Services\SummaryReportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAutoReports extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:send-auto
                            {--type=daily : Schedule type: daily, weekly, or monthly}';

    /**
     * The console command description.
     */
    protected $description = 'Send automated summary report PDFs to registered email addresses.';

    public function __construct(private readonly SummaryReportService $reportService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = strtolower($this->option('type'));

        if (!in_array($type, ['daily', 'weekly', 'monthly'], true)) {
            $this->error("Invalid type '{$type}'. Use: daily, weekly, or monthly.");
            return self::FAILURE;
        }

        [$startDate, $endDate] = $this->getDateRange($type);

        $this->info("Sending {$type} reports | Period: {$startDate} → {$endDate}");

        // Columns per migration: schedule_report (daily|weekly|monthly),
        //   auto_report ('Active'|'Inactive'), email_report (string)
        $reports = AutoReportModel::where('schedule_report', $type)
            ->where('auto_report', 'Active')
            ->whereNotNull('email_report')
            ->whereNotNull('device_id')
            ->with('device')
            ->get();

        if ($reports->isEmpty()) {
            $this->info("No active {$type} reports found.");
            return self::SUCCESS;
        }

        $sent   = 0;
        $failed = 0;

        foreach ($reports as $report) {
            $deviceId = $report->device_id;
            $email    = $report->email_report;
            $category = $report->device?->device_category ?? $deviceId;

            $this->line("  • Device: {$deviceId} → {$email}");

            try {
                $pdfContent = $this->reportService->generatePdfOutput($deviceId, $startDate, $endDate, $type);

                Mail::to($email)->send(new AutoReportMail(
                    deviceId:       $deviceId,
                    deviceCategory: $category,
                    scheduleType:   $type,
                    startDate:      Carbon::parse($startDate)->format('d/m/Y H:i'),
                    endDate:        Carbon::parse($endDate)->format('d/m/Y H:i'),
                    pdfContent:     $pdfContent,
                    generatedAt:    now()->format('d/m/Y H:i'),
                ));

                $this->line("    ✓ Sent successfully.");
                Log::info("[AutoReport] {$type} report sent", [
                    'device_id' => $deviceId,
                    'email'     => $email,
                    'start'     => $startDate,
                    'end'       => $endDate,
                ]);

                $sent++;
            } catch (\Throwable $e) {
                $this->error("    ✗ Failed: {$e->getMessage()}");
                Log::error("[AutoReport] Failed to send {$type} report", [
                    'device_id' => $deviceId,
                    'email'     => $email,
                    'error'     => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        $this->info("Done — sent: {$sent}, failed: {$failed}.");
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Compute the start/end date strings for the given schedule type.
     * All dates are in the app timezone.
     *
     * - daily:   Previous 24 hours (yesterday 00:00 → 23:59:59)
     * - weekly:  Last full week Mon 00:00 → Sun 23:59:59 (sent on Monday 08:00)
     * - monthly: Last full calendar month, first day 00:00 → last day 23:59:59
     *
     * @return array{string, string}  [startDate, endDate]
     */
    private function getDateRange(string $type): array
    {
        $tz  = config('app.timezone', 'UTC');
        $now = Carbon::now($tz);

        switch ($type) {
            case 'daily':
                $start = $now->copy()->subDay()->startOfDay();
                $end   = $now->copy()->subDay()->endOfDay();
                break;

            case 'weekly':
                // Previous Monday → Previous Sunday (completed week before today)
                $end   = $now->copy()->previous(Carbon::SUNDAY)->endOfDay();
                $start = $end->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                break;

            case 'monthly':
            default:
                $start = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
                $end   = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
                break;
        }

        return [$start->toDateTimeString(), $end->toDateTimeString()];
    }
}
