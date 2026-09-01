<?php

namespace App\Jobs;

use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use App\Models\Student;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class SendResultsSmsRow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $rowId)
    {
        $this->onQueue(config('results_sms.queue', 'default'));
    }

    public function backoff(): array { return [30, 120, 300]; }

    public function handle(SmsServiceInterface $sms, ResultsSmsUploadService $uploads): void
    {
        // Shared limit across workers. 45 messages per minute keeps the
        // configured provider below a conservative throughput threshold.
        $limit = config('results_sms.rate_limit_per_minute', 45);
        if (RateLimiter::tooManyAttempts('results-sms-callbly', $limit)) {
            $this->release(max(5, RateLimiter::availableIn('results-sms-callbly')));

            return;
        }

        $row = DB::transaction(function (): ?ResultsSmsUploadRow {
            $row = ResultsSmsUploadRow::lockForUpdate()->find($this->rowId);
            if (! $row || $row->status !== 'queued') {
                return null;
            }

            $alreadySent = ResultsSmsUploadRow::where('batch_id', $row->batch_id)
                ->where('student_id_hash', $row->student_id_hash)
                ->where('message_hash', $row->message_hash)
                ->where('status', 'sent')
                ->exists();

            if ($alreadySent) {
                $row->update(['status' => 'skipped', 'safe_reason' => 'An identical message in this batch was already sent.', 'processed_at' => now()]);

                return null;
            }

            $row->update(['status' => 'processing', 'attempt_count' => $row->attempt_count + 1]);

            return $row->fresh();
        });

        if (! $row) {
            return;
        }

        $student = Student::active()->find($row->student_record_id);
        $number = $student ? $sms->normalizePhoneNumber((string) $student->mobile_number) : null;
        if (! $student || $number === null || ! $sms->validatePhoneNumber($number)) {
            $row->update(['status' => 'skipped', 'safe_reason' => 'The student is no longer active or no longer has a valid mobile number.', 'processed_at' => now()]);
            $this->refreshBatch($row->batch_id);

            return;
        }

        RateLimiter::hit('results-sms-callbly', 60);
        $result = $sms->sendSingle($number, $row->message, [
            'user_id' => ResultsSmsUploadBatch::find($row->batch_id)?->confirmed_by,
            'suppress_standard_log' => true,
        ]);

        if ($result['success'] ?? false) {
            $row->update([
                'status' => 'sent', 'safe_reason' => null, 'masked_recipient' => $uploads->maskPhone($number),
                'normalized_recipient' => $number, 'provider_response' => $result['data'] ?? $result,
                'sent_at' => now(), 'processed_at' => now(),
            ]);
        } else {
            $error = (string) ($result['message'] ?? 'The SMS provider did not accept the message.');
            $transient = str_contains(mb_strtolower($error), 'unable to connect') || str_contains(mb_strtolower($error), 'http 5');
            $row->update([
                'status' => $transient && $this->attempts() < $this->tries ? 'queued' : 'failed',
                'safe_reason' => 'Provider delivery failed. Use Retry failed messages to try again.',
                'provider_response' => $result['data'] ?? $result,
                'processed_at' => now(),
            ]);
            if ($transient && $this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);

                return;
            }
        }

        $this->refreshBatch($row->batch_id);
    }

    private function refreshBatch(int $batchId): void
    {
        $batch = ResultsSmsUploadBatch::find($batchId);
        if (! $batch) return;

        $counts = ResultsSmsUploadRow::where('batch_id', $batchId)->selectRaw("\n            SUM(status = 'sent') as sent_rows,\n            SUM(status = 'failed') as failed_rows,\n            SUM(status = 'skipped') as skipped_rows,\n            SUM(status IN ('ready', 'queued', 'processing')) as outstanding_rows\n        ")->first();
        $complete = (int) $counts->outstanding_rows === 0;
        $batch->update([
            'status' => $complete ? 'completed' : 'processing',
            'sent_rows' => (int) $counts->sent_rows,
            'failed_rows' => (int) $counts->failed_rows,
            'skipped_rows' => max($batch->skipped_rows, (int) $counts->skipped_rows),
            'completed_at' => $complete ? now() : null,
        ]);
    }
}
