<?php

namespace App\Jobs;

use App\Mail\UserBackupReadyMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendUserBackupReadyEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $emails
     */
    public function __construct(
        public array $emails,
        public string $relativePath,
        public int $rowCount,
        public string $generatedAt
    ) {}

    public function handle(): void
    {
        Mail::to($this->emails)->send(new UserBackupReadyMail(
            relativePath: $this->relativePath,
            rowCount: $this->rowCount,
            generatedAt: $this->generatedAt,
        ));
    }
}

