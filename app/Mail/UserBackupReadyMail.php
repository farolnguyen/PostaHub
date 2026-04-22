<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserBackupReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $relativePath,
        public int $rowCount,
        public string $generatedAt
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thong bao backup users moi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-backup-ready',
            with: [
                'relativePath' => $this->relativePath,
                'rowCount' => $this->rowCount,
                'generatedAt' => $this->generatedAt,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

