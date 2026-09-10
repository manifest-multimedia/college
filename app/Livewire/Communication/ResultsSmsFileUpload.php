<?php

namespace App\Livewire\Communication;

use App\Jobs\DispatchResultsSmsRows;
use App\Jobs\ValidateResultsSmsUpload;
use App\Models\ResultsSmsUploadBatch;
use App\Models\ResultsSmsUploadRow;
use App\Models\Student;
use App\Services\Communication\SMS\ResultsSmsUploadService;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ResultsSmsFileUpload extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $upload;
    public ?string $batchId = null;
    public bool $confirmed = false;

    // Filter and search state for batch rows
    public string $rowFilter = 'all';
    public string $rowSearch = '';
    public int $perPage = 25;

    // Modal state for rectifying contact
    public bool $showRectifyModal = false;
    public ?int $rectifyingRowId = null;
    public ?int $rectifyingStudentDbId = null;
    public string $rectifyingStudentId = '';
    public string $rectifyingStudentName = '';
    public ?string $rectifyingClassName = null;
    public string $rectifyingCurrentPhone = '';
    public string $newMobileNumber = '';
    public string $rectifyReason = '';

    public function mount(?string $batch = null): void
    {
        $this->authorizeAccess();
        $this->batchId = $batch;
    }

    public function updatingRowFilter(): void
    {
        $this->resetPage();
    }

    public function updatingRowSearch(): void
    {
        $this->resetPage();
    }

    public function filterBy(string $filter): void
    {
        $this->rowFilter = $filter;
        $this->resetPage();
    }

    public function openRectifyModal(int $rowId): void
    {
        $this->authorizeAccess();
        $batch = $this->batchOrFail();
        $row = ResultsSmsUploadRow::where('batch_id', $batch->id)->with('student.collegeClass')->findOrFail($rowId);

        $this->rectifyingRowId = $row->id;
        $this->rectifyingStudentDbId = $row->student_record_id;
        $this->rectifyingStudentId = (string) $row->student_id;
        $this->rectifyingStudentName = $row->student?->full_name ?? ($row->student?->name ?? 'Unknown Student');
        $this->rectifyingClassName = $row->student?->collegeClass?->name;
        $this->rectifyingCurrentPhone = (string) ($row->student?->mobile_number ?? '');
        $this->newMobileNumber = $this->rectifyingCurrentPhone;
        $this->rectifyReason = (string) ($row->safe_reason ?? 'Contact needs attention');
        $this->resetErrorBag();
        $this->showRectifyModal = true;
    }

    public function closeRectifyModal(): void
    {
        $this->showRectifyModal = false;
        $this->rectifyingRowId = null;
        $this->rectifyingStudentDbId = null;
        $this->newMobileNumber = '';
        $this->resetErrorBag();
    }

    public function saveRectifiedContact(ResultsSmsUploadService $uploads, SmsServiceInterface $sms): void
    {
        $this->authorizeAccess();
        $this->validate([
            'newMobileNumber' => 'required|string|min:9|max:20',
        ]);

        $normalized = $sms->normalizePhoneNumber($this->newMobileNumber);
        if ($normalized === null || ! $sms->validatePhoneNumber($normalized)) {
            $this->addError('newMobileNumber', 'Please enter a valid Ghanaian mobile phone number (e.g., 0244123456 or 0598036772).');

            return;
        }

        if (! $this->rectifyingStudentDbId) {
            $this->addError('newMobileNumber', 'No linked student record found to update.');

            return;
        }

        $student = Student::findOrFail($this->rectifyingStudentDbId);
        $student->update(['mobile_number' => $this->newMobileNumber]);

        $batch = $this->batchOrFail();

        // Update the specific row directly so the preview flips immediately to ready
        if ($this->rectifyingRowId) {
            $row = ResultsSmsUploadRow::where('batch_id', $batch->id)->find($this->rectifyingRowId);
            if ($row) {
                $row->update([
                    'status' => 'ready',
                    'safe_reason' => null,
                    'masked_recipient' => $uploads->maskPhone($normalized),
                ]);
            }
        }

        // Recalculate batch counts
        $ready = ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'ready')->count();
        $skipped = ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'skipped')->count();
        $missingNumber = ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'skipped')->where('safe_reason', 'like', '%mobile number%')->count();
        $missingStudent = ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'skipped')->where('safe_reason', 'like', '%matches this Student ID%')->count();
        $pendingReview = ResultsSmsUploadRow::where('batch_id', $batch->id)->where('status', 'pending_review')->count();
        $total = ResultsSmsUploadRow::where('batch_id', $batch->id)->count();

        $batch->update([
            'ready_rows' => $ready,
            'skipped_rows' => $skipped,
            'missing_number_rows' => $missingNumber,
            'missing_student_rows' => $missingStudent,
            'pending_review_rows' => $pendingReview,
            'total_rows' => $total,
        ]);

        $this->closeRectifyModal();
        session()->flash('success', "Contact number for {$student->full_name} was updated and the row is now Ready!");
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
            $this->addError('upload', 'The secure upload could not be stored. Please try again or contact your system administrator.');

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
        if (! in_array($batch->status, ['failed', 'validated'], true)) {
            return;
        }

        $batch->update([
            'status' => 'validating', 'failure_reason' => null, 'validated_at' => null,
            'total_rows' => 0, 'ready_rows' => 0, 'skipped_rows' => 0, 'pending_review_rows' => 0,
            'missing_student_rows' => 0, 'missing_number_rows' => 0, 'duplicate_id_rows' => 0,
        ]);
        ValidateResultsSmsUpload::dispatch($batch->id);
        session()->flash('success', 'The batch has been queued for validation again. No messages have been sent.');
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

    public function resumeSending(): void
    {
        $this->authorizeAccess();
        $batch = $this->batchOrFail();

        $queuedRows = ResultsSmsUploadRow::where('batch_id', $batch->id)
            ->where('status', 'queued')
            ->pluck('id');

        if ($queuedRows->isEmpty()) {
            session()->flash('error', 'There are no pending queued messages to send.');

            return;
        }

        $batch->update(['status' => 'processing', 'completed_at' => null]);
        foreach ($queuedRows as $rowId) {
            \App\Jobs\SendResultsSmsRow::dispatch($rowId);
        }

        session()->flash('success', "Resumed sending for {$queuedRows->count()} pending messages. Sending continues safely in the background.");
    }

    public function render()
    {
        $batch = $this->batchId
            ? ResultsSmsUploadBatch::where('public_id', $this->batchId)->first()
            : null;

        $rows = null;
        if ($batch) {
            $query = ResultsSmsUploadRow::where('batch_id', $batch->id)
                ->with(['student.collegeClass']);

            if ($this->rowFilter === 'ready') {
                $query->where('status', 'ready');
            } elseif ($this->rowFilter === 'skipped') {
                $query->where('status', 'skipped');
            } elseif ($this->rowFilter === 'missing_number') {
                $query->where('status', 'skipped')->where('safe_reason', 'like', '%mobile number%');
            } elseif ($this->rowFilter === 'missing_student') {
                $query->where('status', 'skipped')->where('safe_reason', 'like', '%matches this Student ID%');
            } elseif ($this->rowFilter === 'duplicate_id') {
                $query->where('status', 'skipped')->where('safe_reason', 'like', '%Duplicate%');
            } elseif ($this->rowFilter === 'pending_review') {
                $query->where('status', 'pending_review');
            }

            if (filled($this->rowSearch)) {
                $search = trim($this->rowSearch);
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        $q->where('row_number', (int) $search);
                    }
                    $q->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('student_id', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%");
                    });
                });
            }

            if ($this->rowFilter === 'all') {
                $query->orderByRaw("CASE WHEN status IN ('skipped', 'pending_review', 'failed') THEN 0 ELSE 1 END")
                    ->orderBy('row_number');
            } else {
                $query->orderBy('row_number');
            }

            $rows = $query->paginate($this->perPage);
        }

        return view('livewire.communication.results-sms-file-upload', [
            'batch' => $batch,
            'rows' => $rows,
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

