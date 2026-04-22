<?php

namespace App\Console\Commands;

use App\Jobs\SendUserBackupReadyEmailJob;
use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class BackupUsersCommand extends Command
{
    protected $signature = 'app:backup-users';

    protected $description = 'Backup users table to CSV and notify admins via queued email';

    public function handle(): int
    {
        $columns = Schema::getColumnListing('users');
        if ($columns === []) {
            $this->error('Khong tim thay cot trong bang users.');

            return self::FAILURE;
        }

        $directory = 'backups/users';
        Storage::disk('local')->makeDirectory($directory);
        $fileName = 'users_backup_'.now()->format('Y_m_d_His').'.csv';
        $relativePath = $directory.'/'.$fileName;
        $absolutePath = Storage::disk('local')->path($relativePath);

        $handle = fopen($absolutePath, 'w');
        if ($handle === false) {
            $this->error('Khong tao duoc file backup.');

            return self::FAILURE;
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $columns);

        $rowCount = 0;
        DB::table('users')->orderBy('id')->chunk(500, function ($rows) use ($columns, &$rowCount, $handle): void {
            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $line[] = $row->{$column} ?? null;
                }
                fputcsv($handle, $line);
                $rowCount++;
            }
        });

        fclose($handle);

        $emails = Admin::query()
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($emails !== []) {
            SendUserBackupReadyEmailJob::dispatch($emails, $relativePath, $rowCount, now()->toDateTimeString());
        }

        $this->info('Da backup users: '.$relativePath.' ('.$rowCount.' dong).');
        if ($emails === []) {
            $this->warn('Khong co email admin de gui thong bao.');
        } else {
            $this->info('Da xep hang job gui mail thong bao cho admin.');
        }

        return self::SUCCESS;
    }
}

