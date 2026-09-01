<?php

namespace App\Jobs;

use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DispatchResultsSmsRows implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $batchId)
    {
        $this->onQueue(config('results_sms.queue', 'default'));
    }

    public function handle(): void
    {
        $batch = ResultsSmsUploadBatch::find($this->batchId);
        if (! $batch || ! in_array($batch->status, ['queued', 'processing'], true)) {
            return;
        }

        $rowIds = DB::transaction(function (): array {
            $rows = ResultsSmsUploadRow::where('batch_id', $this->batchId)
                ->where('status', 'ready')
                ->orderBy('id')
                ->lockForUpdate()
                ->limit(250)
                ->get(['id']);

            if ($rows->isEmpty()) {
                return [];
            }

            ResultsSmsUploadRow::whereIn('id', $rows->pluck('id'))->update(['status' => 'queued', 'updated_at' => now()]);

            return $rows->pluck('id')->all();
        });

        foreach ($rowIds as $rowId) {
            SendResultsSmsRow::dispatch($rowId);
        }

        if ($rowIds !== []) {
            self::dispatch($this->batchId);
        }
    }
}
