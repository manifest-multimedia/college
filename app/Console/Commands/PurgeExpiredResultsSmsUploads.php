<?php

namespace App\Console\Commands;

use App\Models\ResultsSmsUploadBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredResultsSmsUploads extends Command
{
    protected $signature = 'results-sms:purge-expired {--days= : Override the configured retention period}';
    protected $description = 'Purge encrypted results SMS upload files and audit rows after their retention period';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('results_sms.retention_days', 180));
        $count = 0;

        ResultsSmsUploadBatch::where('created_at', '<', now()->subDays($days))->chunkById(100, function ($batches) use (&$count): void {
            foreach ($batches as $batch) {
                Storage::disk('local')->delete($batch->stored_path);
                $batch->delete();
                $count++;
            }
        });

        $this->info("Purged {$count} expired results SMS upload batch(es).");

        return self::SUCCESS;
    }
}
