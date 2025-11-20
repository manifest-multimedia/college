<?php

namespace App\Livewire\Admin;

use App\Jobs\ProcessExamClearanceJob;
use App\Models\ExamType;
use App\Models\OfflineExam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class OfflineExams extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Properties for the offline exam form
    public $title;

    public $description;

    public $date;

    public $duration;

    public $course_id;

    public $type_id;

    public $proctor_id;

    public $venue;

    public $clearance_threshold = 60;

    public $passing_percentage = 50;

    public $status = 'draft';

    // Properties for the exam details modal
    public $selectedExam = null;

    public $examTypes = [];

    // Properties for filtering
    public $search = '';

    public $statusFilter = '';

    public $typeFilter = '';

    public $perPage = 10;

    // Component state
    public $showForm = false;

    public $isEditing = false;

    public $formMode = 'create';

    public $editingId = null;

    // For confirmation modals
    public $confirmingDeletion = false;

    public $examToDelete = null;

    public $confirmingClearanceProcess = false;

    public $examToProcess = null;

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'date' => 'required|date|after:today',
        'duration' => 'required|integer|min:15|max:300',
        'course_id' => 'required|exists:subjects,id',
        'type_id' => 'nullable|exists:exam_types,id',
        'proctor_id' => 'nullable|exists:users,id',
        'venue' => 'required|string|max:255',
        'clearance_threshold' => 'nullable|integer|min:0|max:100',
        'passing_percentage' => 'nullable|integer|min:0|max:100',
        'status' => 'required',
    ];

    protected $messages = [
        'date.after' => 'The exam date must be set for a future date.',
        'duration.min' => 'The exam duration must be at least 15 minutes.',
        'duration.max' => 'The exam duration cannot exceed 300 minutes (5 hours).',
        'clearance_threshold.min' => 'The clearance threshold must be between 0 and 100 percent.',
        'clearance_threshold.max' => 'The clearance threshold must be between 0 and 100 percent.',
        'course_id.required' => 'Please select a course for this exam.',
        'venue.required' => 'The exam venue is required for offline exams.',
        'status.in' => 'The selected status is invalid.',
    ];

    public function mount()
    {
        // Load exam types for dropdown
        $this->loadExamTypes();
    }

    public function loadExamTypes()
    {
        $this->examTypes = ExamType::orderBy('name')->get();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->formMode = 'create';
        $this->showForm = true;
        $this->isEditing = false;
    }

    public function edit($examId)
    {
        $this->resetForm();
        $this->formMode = 'edit';
        $this->editingId = $examId;
        $this->isEditing = true;

        $exam = OfflineExam::findOrFail($examId);

        $this->title = $exam->title;
        $this->description = $exam->description;
        $this->date = $exam->date->format('Y-m-d\TH:i');
        $this->duration = $exam->duration;
        $this->course_id = $exam->course_id;
        $this->type_id = $exam->type_id;
        $this->proctor_id = $exam->proctor_id;
        $this->venue = $exam->venue;
        $this->clearance_threshold = $exam->clearance_threshold;
        $this->passing_percentage = $exam->passing_percentage;
        $this->status = $exam->status;

        $this->showForm = true;
    }

    public function rules()
    {
        // Merge the basic rules with dynamic rules
        return array_merge($this->rules, [
            'status' => ['required', Rule::in(['draft', 'published', 'completed', 'canceled'])],
        ]);
    }

    public function save()
    {
        $this->validate($this->rules());

        if ($this->formMode === 'create') {
            $exam = new OfflineExam;
            $exam->user_id = Auth::id();
        } else {
            $exam = OfflineExam::findOrFail($this->editingId);

            // Check if status is changing to published
            $wasPublished = $exam->status === 'published';
            $isNowPublished = $this->status === 'published';

            // Determine if we need to process clearances after save
            $needsClearanceProcessing = ($isNowPublished && ! $wasPublished) ||
                ($isNowPublished && $exam->clearance_threshold != $this->clearance_threshold);
        }

        $exam->title = $this->title;
        $exam->description = $this->description;
        $exam->date = $this->date;
        $exam->duration = $this->duration;
        $exam->course_id = $this->course_id;
        $exam->type_id = $this->type_id;
        $exam->proctor_id = $this->proctor_id;
        $exam->venue = $this->venue;
        $exam->clearance_threshold = $this->clearance_threshold;
        $exam->passing_percentage = $this->passing_percentage;
        $exam->status = $this->status;

        $exam->save();

        // If publishing for the first time or changing clearance threshold, process clearances
        if (isset($needsClearanceProcessing) && $needsClearanceProcessing) {
            ProcessExamClearanceJob::dispatch($exam)
                ->onQueue('exam_clearances');

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Exam published. Student clearance processing started.',
                'timer' => 3000,
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $this->formMode === 'create'
                    ? 'Offline exam created successfully.'
                    : 'Offline exam updated successfully.',
                'timer' => 3000,
            ]);
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function confirmDeletion($examId)
    {
        $this->examToDelete = $examId;
        $this->confirmingDeletion = true;
    }

    public function delete()
    {
        $exam = OfflineExam::findOrFail($this->examToDelete);

        // Only allow deletion if no clearances or tickets exist
        $hasClearances = $exam->clearances()->count() > 0;
        $hasTickets = $exam->examEntryTickets()->count() > 0;

        if ($hasClearances || $hasTickets) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Cannot delete exam with associated clearances or tickets.',
                'timer' => 3000,
            ]);
        } else {
            $exam->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Offline exam deleted successfully.',
                'timer' => 3000,
            ]);
        }

        $this->confirmingDeletion = false;
        $this->examToDelete = null;
    }

    public function cancelDeletion()
    {
        $this->confirmingDeletion = false;
        $this->examToDelete = null;
    }

    public function viewDetails($examId)
    {
        $this->selectedExam = OfflineExam::with(['course', 'type', 'proctor', 'user'])
            ->withCount(['clearances', 'examEntryTickets'])
            ->findOrFail($examId);

        $this->dispatch('open-details-modal');
    }

    public function confirmClearanceProcess($examId)
    {
        $this->examToProcess = $examId;
        $this->confirmingClearanceProcess = true;
    }

    public function processClearance()
    {
        $exam = OfflineExam::findOrFail($this->examToProcess);

        ProcessExamClearanceJob::dispatch($exam)
            ->onQueue('exam_clearances');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Clearance processing started for the exam.',
            'timer' => 3000,
        ]);

        $this->confirmingClearanceProcess = false;
        $this->examToProcess = null;
    }

    public function cancelClearanceProcess()
    {
        $this->confirmingClearanceProcess = false;
        $this->examToProcess = null;
    }

    public function resetForm()
    {
        $this->reset([
            'title', 'description', 'date', 'duration', 'course_id',
            'type_id', 'proctor_id', 'venue', 'status', 'editingId',
        ]);

        $this->clearance_threshold = 60;
        $this->passing_percentage = 50;
    }

    public function cancelForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        $examsQuery = OfflineExam::query()
            ->with(['course', 'type', 'proctor'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('venue', 'like', '%'.$this->search.'%')
                        ->orWhereHas('course', function ($q) {
                            $q->where('title', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('type_id', $this->typeFilter);
            })
            ->latest();

        $exams = $examsQuery->paginate($this->perPage);
        $examTypes = $this->examTypes;

        return view('livewire.admin.offline-exams', [
            'exams' => $exams,
            'examTypes' => $examTypes,
        ]);
    }
}
