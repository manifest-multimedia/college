<?php

namespace App\Livewire\Communication;

use App\Jobs\DispatchResultsSmsRows;
use App\Jobs\ValidateResultsSmsUpload;
use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class ResultsSmsFileUpload extends Component
{
    use WithFileUploads;

    public $upload;
    public ?string $batchId = null;
    public bool $confirmed = false;

    public function mount(?string $batch = null): void
    {
        $this->authorizeAccess();
        $this->batchId = $batch;
    }

    public function validateUpload(ResultsSmsUploadService $uploads): void
    {
        $this->authorizeAccess();
        $this->validate(['upload' => 'required|file|mimes:xlsx,csv|max:'.config('results_sms.max_file_kilobytes', 10240)]);

        $extension = strtolower($this->upload->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'csv'], true)) {
            $this->addError('upload', 'Only .xlsx and .csv result-message files are permitted.');

            return;
        }

        try {
            $uploads->assertSafeUpload($this->upload);
        } catch (\RuntimeException $exception) {
            $this->addError('upload', $exception->getMessage());

            return;
        }

        $batch = ResultsSmsUploadBatch::create([
            'uploaded_by' => auth()->id(),
            'original_filename' => '',
            'stored_path' => '',
            'file_hash' => str_repeat('0', 64),
            'file_extension' => $extension,
            'status' => 'validating',
        ]);
        try {
            $uploads->storeEncryptedUpload($this->upload, $batch);
        } catch (\RuntimeException $exception) {
            $batch->delete();
            $this->addError('upload', 'Secure upload storage is unavailable. Please contact your system administrator.');

            return;
        }
        ValidateResultsSmsUpload::dispatch($batch->id);

        $this->batchId = $batch->public_id;
        $this->upload = null;
        $this->confirmed = false;
        session()->flash('success', 'The file is secured and being validated. This page will update when the preview is ready.');
    }

    public function confirmAndSend(): void
    {
        $this->authorizeAccess();
        $this->validate(['confirmed' => 'accepted']);
        $batch = $this->batchOrFail();

        if ($batch->status !== 'validated' || $batch->ready_rows === 0) {
            session()->flash('error', 'This batch is not ready to send. Complete validation and review the report first.');

            return;
        }

        DB::transaction(function () use ($batch): void {
            $locked = ResultsSmsUploadBatch::lockForUpdate()->findOrFail($batch->id);
            if ($locked->status !== 'validated') return;
            $locked->update(['status' => 'queued', 'confirmed_by' => auth()->id(), 'confirmed_at' => now()]);
        });

        DispatchResultsSmsRows::dispatch($batch->id);
        $this->confirmed = false;
        session()->flash('success', 'Ready result messages were queued. Sending continues safely in the background.');
    }

    public function retryValidation(): void
    {
        $this->authorizeAccess();
        $batch = $this->batchOrFail();
        if ($batch->status !== 'failed') {
            return;
        }

        $batch->update([
            'status' => 'validating', 'failure_reason' => null, 'validated_at' => null,
            'total_rows' => 0, 'ready_rows' => 0, 'skipped_rows' => 0, 'pending_review_rows' => 0,
            'missing_student_rows' => 0, 'missing_number_rows' => 0, 'duplicate_id_rows' => 0,
        ]);
        ValidateResultsSmsUpload::dispatch($batch->id);
        session()->flash('success', 'The batch has been queued for validation again.');
    }

    public function retryFailed(): void
    {
        $this->authorizeAccess();
        $batch = $this->batchOrFail();
        $updated = ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'failed')->update([
            'status' => 'queued', 'safe_reason' => null, 'processed_at' => null, 'updated_at' => now(),
        ]);

        if ($updated === 0) {
            session()->flash('error', 'There are no failed messages to retry.');

            return;
        }

        $batch->update(['status' => 'processing', 'completed_at' => null]);
        ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'queued')->pluck('id')->each(
            fn (int $rowId) => \App\Jobs\SendResultsSmsRow::dispatch($rowId)
        );
        session()->flash('success', 'Failed messages were queued for an explicit retry. Sent rows will not be sent again.');
    }

    public function render()
    {
        $batch = $this->batchId
            ? ResultsSmsUploadBatch::where('public_id', $this->batchId)->first()
            : null;

        return view('livewire.communication.results-sms-file-upload', [
            'batch' => $batch,
            'recentBatches' => ResultsSmsUploadBatch::latest()->limit(12)->get(),
        ])->layout('components.dashboard.default', ['title' => 'Results SMS File Upload']);
    }

    private function batchOrFail(): ResultsSmsUploadBatch
    {
        return ResultsSmsUploadBatch::where('public_id', $this->batchId)->firstOrFail();
    }

    private function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Academic Officer']), 403);
    }
}
