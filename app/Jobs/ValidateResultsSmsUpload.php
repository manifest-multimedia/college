<?php

namespace App\Jobs;

use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ValidateResultsSmsUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 900;

    public function __construct(public int $batchId)
    {
        $this->onQueue(config('results_sms.queue', 'default'));
    }

    public function handle(ResultsSmsUploadService $uploads, SmsServiceInterface $sms): void
    {
        $batch = ResultsSmsUploadBatch::findOrFail($this->batchId);
        $data = $uploads->read($batch);
        $required = ['student id', 'sms message'];
        $headerPositions = array_flip($data['headers']);

        if (array_diff($required, array_keys($headerPositions))) {
            $batch->update(['status' => 'failed', 'failure_reason' => 'The upload must include Student ID and SMS Message columns.']);

            return;
        }

        $maxRows = config('results_sms.max_rows', ResultsSmsUploadService::MAX_ROWS);
        if (count($data['rows']) > $maxRows) {
            $batch->update(['status' => 'failed', 'failure_reason' => 'The upload exceeds the '.$maxRows.'-row limit.']);

            return;
        }

        $studentIndex = $headerPositions['student id'];
        $messageIndex = $headerPositions['sms message'];
        $statusIndex = $headerPositions['status'] ?? null;
        $prepared = [];
        $idCounts = [];

        foreach ($data['rows'] as $offset => $values) {
            $studentId = trim((string) ($values[$studentIndex] ?? ''));
            $message = (string) ($values[$messageIndex] ?? '');
            $uploadedStatus = $statusIndex === null ? null : trim((string) ($values[$statusIndex] ?? ''));
            $prepared[] = compact('studentId', 'message', 'uploadedStatus', 'offset');

            if ($studentId !== '') {
                $idCounts[$studentId] = ($idCounts[$studentId] ?? 0) + 1;
            }
        }

        $students = $uploads->findActiveStudents(array_column($prepared, 'studentId'));
        $rows = [];
        $counts = [
            'total_rows' => count($prepared), 'ready_rows' => 0, 'skipped_rows' => 0, 'pending_review_rows' => 0,
            'missing_student_rows' => 0, 'missing_number_rows' => 0, 'duplicate_id_rows' => 0,
        ];

        foreach ($prepared as $item) {
            $studentId = $item['studentId'];
            $message = $item['message'];
            $uploadedStatus = $item['uploadedStatus'];
            $status = 'ready';
            $reason = null;
            $student = null;
            $normalisedPhone = null;

            if ($studentId === '') {
                $status = 'skipped';
                $reason = 'Student ID is missing.';
            } elseif (($idCounts[$studentId] ?? 0) > 1) {
                $status = 'skipped';
                $reason = 'Duplicate Student ID in this upload.';
                $counts['duplicate_id_rows']++;
            } elseif (trim($message) === '') {
                $status = 'skipped';
                $reason = 'SMS Message is missing.';
            } elseif ($statusIndex !== null && mb_strtolower($uploadedStatus) !== 'ready') {
                $status = mb_strtolower($uploadedStatus) === 'pending review' ? 'pending_review' : 'skipped';
                $reason = $status === 'pending_review' ? 'Marked Pending review in the upload.' : 'Status is not Ready.';
            } elseif (! isset($students[$studentId])) {
                $status = 'skipped';
                $reason = 'No active student matches this Student ID.';
                $counts['missing_student_rows']++;
            } else {
                $student = $students[$studentId];
                $normalisedPhone = $sms->normalizePhoneNumber((string) $student->mobile_number);
                if ($normalisedPhone === null || ! $sms->validatePhoneNumber($normalisedPhone)) {
                    $status = 'skipped';
                    $reason = 'The active student has no valid mobile number.';
                    $counts['missing_number_rows']++;
                }
            }

            $counts[$status === 'ready' ? 'ready_rows' : ($status === 'pending_review' ? 'pending_review_rows' : 'skipped_rows')]++;
            $rows[] = [
                'batch_id' => $batch->id,
                'row_number' => $item['offset'] + 2,
                'student_record_id' => $student?->id,
                'student_id' => $studentId,
                'student_id_hash' => hash('sha256', $studentId),
                'message' => $message,
                'message_hash' => hash('sha256', $message),
                'uploaded_status' => $uploadedStatus,
                'status' => $status,
                'safe_reason' => $reason,
                'masked_recipient' => $normalisedPhone ? $uploads->maskPhone($normalisedPhone) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($batch, $rows, $counts): void {
            ResultsSmsUploadRow::where('batch_id', $batch->id)->delete();
            // Bulk insert does not invoke Eloquent casts. Hydrate each model
            // first so student IDs and result messages are encrypted at rest.
            $encryptedRows = array_map(
                fn (array $attributes) => (new ResultsSmsUploadRow())->fill($attributes)->getAttributes(),
                $rows
            );
            foreach (array_chunk($encryptedRows, 250) as $chunk) {
                ResultsSmsUploadRow::insert($chunk);
            }
            $batch->update([...$counts, 'status' => 'validated', 'validated_at' => now(), 'failure_reason' => null]);
        });
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Results SMS upload validation failed', [
            'batch_id' => $this->batchId,
            'exception' => $exception->getMessage(),
        ]);

        $batch = ResultsSmsUploadBatch::find($this->batchId);
        $batch?->update([
            'status' => 'failed',
            'failure_reason' => 'The upload could not be validated. Download the report and try again.',
        ]);
    }
}
