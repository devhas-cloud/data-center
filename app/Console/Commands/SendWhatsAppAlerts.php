<?php

namespace App\Console\Commands;

use App\Models\LogsModel;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWhatsAppAlerts extends Command
{
    protected $signature   = 'alerts:send-whatsapp';
    protected $description = 'Send WhatsApp reminders to users when sensor parameters exceed their alert threshold.';

    public function __construct(private readonly WhatsAppService $whatsapp)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = Carbon::now(config('app.timezone'));

        // ------------------------------------------------------------------
        // 1. Find all tbl_latest_data rows that breach their alert threshold.
        //    Join tbl_sensor (device_id + parameter_name) for the threshold
        //    and tbl_device for the human-readable device name.
        // ------------------------------------------------------------------
        $alerts = DB::table('tbl_latest_data as ld')
            ->join('tbl_sensor as s', function ($join) {
                $join->on('s.device_id', '=', 'ld.device_id')
                    ->on('s.parameter_name', '=', 'ld.parameter_name');
            })
            ->join('tbl_device as d', 'd.device_id', '=', 'ld.device_id')
            ->whereNotNull('s.parameter_indicator_alert')
            ->where('s.parameter_indicator_alert', '>', 0)
            ->whereRaw('ld.value > s.parameter_indicator_alert')
            ->select([
                'ld.device_id',
                'ld.parameter_name',
                'ld.value',
                's.parameter_indicator_alert as threshold',
                's.sensor_unit',
                's.sensor_name',
                'd.device_name',
            ])
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('No parameter thresholds exceeded. Nothing to send.');
            return self::SUCCESS;
        }

        $this->info("Found {$alerts->count()} parameter breach(es) across " . $alerts->pluck('device_id')->unique()->count() . ' device(s).');

        // ------------------------------------------------------------------
        // 2. Group breached parameters by device_id so we can batch them
        //    into a single message per user per device.
        // ------------------------------------------------------------------
        $byDevice = $alerts->groupBy('device_id');

        $totalSent    = 0;
        $totalSkipped = 0;
        $totalFailed  = 0;

        foreach ($byDevice as $deviceId => $params) {

            // ----------------------------------------------------------------
            // 3. Find users who have access to this device AND have a WA number.
            //    tbl_access.device_id stores tbl_device.id (numeric), so we
            //    must join through tbl_device to resolve the string device_id.
            //    tbl_access.user_id (varchar) joins tbl_user.id (bigint).
            // ----------------------------------------------------------------
            $users = DB::table('tbl_user as u')
                ->join('tbl_access as a', 'a.user_id', '=', 'u.id')
                ->join('tbl_device as d2', 'd2.id', '=', 'a.device_id')
                ->where('d2.device_id', $deviceId)
                ->whereNotNull('u.whatsapp_number')
                ->where('u.whatsapp_number', '!=', '')
                ->select('u.id', 'u.name', 'u.whatsapp_number')
                ->distinct()
                ->get();

            if ($users->isEmpty()) {
                $this->line("  [SKIP] device={$deviceId} — no users with WhatsApp number.");
                continue;
            }

            foreach ($users as $user) {
                $logAction = "wa_user_{$user->id}";

                // ------------------------------------------------------------
                // 4. Cooldown check: skip if we already sent a WA for this
                //    device + user within the last 1 hour.
                // ------------------------------------------------------------
                $recentLog = LogsModel::where('device_id', $deviceId)
                    ->where('category', 'whatsapp_alert')
                    ->where('action', $logAction)
                    ->where('created_at', '>=', $now->copy()->subHour())
                    ->exists();

                if ($recentLog) {
                    $totalSkipped++;
                    $this->line("  [SKIP] device={$deviceId} user={$user->id} ({$user->name}) — cooldown active (< 1 hour).");
                    continue;
                }

                // ------------------------------------------------------------
                // 5. Build the WhatsApp message.
                //    One consolidated message lists all breached parameters.
                // ------------------------------------------------------------
                $deviceName = $params->first()->device_name;
                $lines      = [];

                foreach ($params as $p) {
                    $lines[] = "• {$p->parameter_name}: {$p->value} {$p->sensor_unit} "
                        . "(Limit: {$p->threshold} {$p->sensor_unit})";
                }

                $alertId = 'ALT-' . $now->format('Ymd-His');

                $messageText = "*MONITORING SYSTEM ALERT*\n\n"
                    . "*Alert Level* : HIGH\n"
                    . "*Device* : {$deviceName}\n"
                    . "*Time* : " . $now->format('d/m/Y H:i') . " WIB\n\n"
                    . "*Threshold Violation Detected*\n\n"
                    . implode("\n", $lines)
                    . "\n\n"
                    . "*Recommended Actions*\n"
                    . "1. Verify sensor operation.\n"
                    . "2. Inspect equipment and site conditions.\n"
                    . "3. Take corrective action if required.\n"
                    . "4. Continue monitoring until values normalize.\n\n"
                    . "Reference ID : {$alertId}\n\n"
                    . "_Automated Notification by Monitoring System_";
                // ------------------------------------------------------------
                // 6. Send via Evolution API.
                // ------------------------------------------------------------
                $sent = $this->whatsapp->sendText($user->whatsapp_number, $messageText);

                if ($sent) {
                    // --------------------------------------------------------
                    // 7. Write success log to tbl_logs.
                    //    This record also serves as the cooldown marker.
                    // --------------------------------------------------------
                    LogsModel::create([
                        'log_date'      => $now->toDateString(),
                        'device_id'     => $deviceId,
                        'category'      => 'whatsapp_alert',
                        'message'       => "WA alert dikirim ke {$user->name} ({$user->whatsapp_number}): " . implode('; ', $lines),
                        'action'        => $logAction,
                        'is_read_admin' => 0,
                        'is_read_user'  => 0,
                    ]);

                    $totalSent++;
                    $this->info("  [SENT] device={$deviceId} → user={$user->id} ({$user->name}) {$user->whatsapp_number}");

                    Log::info('WhatsApp alert sent', [
                        'device_id' => $deviceId,
                        'user_id'   => $user->id,
                        'number'    => $user->whatsapp_number,
                        'params'    => $params->pluck('parameter_name')->toArray(),
                    ]);
                } else {
                    $totalFailed++;
                    $this->error("  [FAIL] device={$deviceId} user={$user->id} ({$user->name}) — WhatsApp API call failed.");
                }

                // Jeda acak 3–8 detik antar pengiriman agar tidak terdeteksi
                // sebagai bot/spam oleh Evolution API maupun WhatsApp.
                sleep(rand(3, 8));
            }
        }

        $this->info("Done — sent: {$totalSent}, skipped (cooldown): {$totalSkipped}, failed: {$totalFailed}.");

        return self::SUCCESS;
    }
}
