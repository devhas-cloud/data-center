<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutoReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $deviceId,
        public readonly string $deviceCategory,
        public readonly string $scheduleType,  // daily | weekly | monthly
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $pdfContent,    // raw PDF binary string
        public readonly string $generatedAt,
    ) {}

    public function envelope(): Envelope
    {
        $typeLabel = ucfirst($this->scheduleType);
        return new Envelope(
            subject: "[Auto Report] {$typeLabel} Summary — {$this->deviceCategory} — {$this->startDate} to {$this->endDate}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.auto-report',
            with: [
                'deviceId'       => $this->deviceId,
                'deviceCategory' => $this->deviceCategory,
                'scheduleType'   => ucfirst($this->scheduleType),
                'startDate'      => $this->startDate,
                'endDate'        => $this->endDate,
                'generatedAt'    => $this->generatedAt,
            ],
        );
    }

    public function attachments(): array
    {
        $filename = sprintf(
            'summary-report-%s-%s-%s.pdf',
            $this->deviceId,
            strtolower($this->scheduleType),
            now()->format('Ymd-His')
        );

        return [
            Attachment::fromData(
                fn () => $this->pdfContent,
                $filename
            )->withMime('application/pdf'),
        ];
    }
}
