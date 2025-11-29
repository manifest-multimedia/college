<?php

namespace App\Livewire;

use App\Models\Exam;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ExamDetail extends Component
{
    public $exam;

    public $editing = false;

    public $confirmingDelete = false;

    public $showQuestions = true;

    public $expectedParticipants = 0; // Expected number of students for this exam

    // Edit form properties
    public $examTitle;

    public $examDescription;

    public $examDuration;

    public $startDate;

    public $endDate;

    public $questionsPerSession;

    public $passingPercentage;

    public $status;

    public $title;

    protected $statusOptions = [
        'upcoming' => 'Upcoming',
        'active' => 'Active',
        'completed' => 'Completed',
    ];

    protected $rules = [
        'examDuration' => 'required|integer|min:1',
        'startDate' => 'nullable|date',
        'endDate' => 'nullable|date|after_or_equal:startDate',
        'questionsPerSession' => 'required|integer|min:1',
        'passingPercentage' => 'nullable|numeric|min:0|max:100',
        'status' => 'required|string|in:upcoming,active,completed',
    ];

    /**
     * Check if user is admin or has admin-level role
     */
    protected function isAdminRole()
    {
        $user = Auth::user();

        // Check Spatie roles first (preferred method)
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['admin', 'Super Admin', 'System', 'Administrator']);
        }

        // Fallback to role column for backward compatibility
        return in_array($user->role, ['admin', 'Super Admin', 'System', 'Administrator']);
    }

    /**
     * Check if user can manage the exam (owner or admin)
     */
    protected function canManageExam()
    {
        return Auth::user()->id === $this->exam->user_id || $this->isAdminRole();
    }

    public function mount(Exam $exam)
    {
        $this->exam = $exam;
        if (! $exam) {
            abort(404, 'Exam not found.');
        }

        if ($this->exam->course) {
            $this->title = 'Exam Details - '.$exam->course->name.' ('.$exam->course->course_code.')';
        } else {
            $this->title = 'Exam Details';
        }

        // Authorization check - only owner, admin, System, or Super Admin can view
        if (! $this->canManageExam()) {
            abort(403, 'Unauthorized access.');
        }

        // Calculate expected participants based on course enrollment
        $this->calculateExpectedParticipants();

        $this->initializeEditForm();
    }

    /**
     * Calculate the expected number of participants for this exam.
     * This is used to detect when more students are taking the exam than expected.
     */
    protected function calculateExpectedParticipants()
    {
        // Try to get enrolled students count from the course
        if ($this->exam->course && method_exists($this->exam->course, 'students')) {
            $this->expectedParticipants = $this->exam->course->students()->count();
        } elseif ($this->exam->course && method_exists($this->exam->course, 'enrollments')) {
            $this->expectedParticipants = $this->exam->course->enrollments()->count();
        } else {
            // Fallback: use total sessions count (both completed and active)
            $this->expectedParticipants = $this->exam->sessions()->distinct('student_id')->count();
        }
    }

    public function initializeEditForm()
    {
        $this->examDuration = $this->exam->duration;
        $this->startDate = $this->exam->start_date ? Carbon::parse($this->exam->start_date)->format('Y-m-d\TH:i') : null;
        $this->endDate = $this->exam->end_date ? Carbon::parse($this->exam->end_date)->format('Y-m-d\TH:i') : null;
        $this->questionsPerSession = $this->exam->questions_per_session;
        $this->passingPercentage = $this->exam->passing_percentage;
        $this->status = $this->exam->status;
    }

    public function startEditing()
    {
        // Only owner, admin, System, or Super Admin can edit
        if (! $this->canManageExam()) {
            session()->flash('error', 'You are not authorized to edit this exam.');

            return;
        }

        $this->editing = true;
        $this->dispatch('startEditing');
    }

    public function cancelEditing()
    {
        $this->editing = false;
        $this->initializeEditForm(); // Reset form to original values
    }

    public function updateExam()
    {
        // Only owner, admin, System, or Super Admin can update
        if (! $this->canManageExam()) {
            session()->flash('error', 'You are not authorized to update this exam.');

            return;
        }

        $this->validate();

        try {
            $this->exam->update([
                'duration' => $this->examDuration,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'questions_per_session' => $this->questionsPerSession,
                'passing_percentage' => $this->passingPercentage,
                'status' => $this->status,
            ]);

            $this->editing = false;
            session()->flash('message', 'Exam updated successfully.');
            $this->dispatch('examUpdated');

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database query exceptions
            $errorCode = $e->errorInfo[1] ?? '';

            if ($errorCode == 1452) { // Foreign key constraint violation
                session()->flash('error', 'Reference error: One of the items you selected no longer exists.');
                Log::error('Foreign key constraint violation in ExamDetail:', [
                    'exam_id' => $this->exam->id,
                    'error' => $e->getMessage(),
                ]);
            } else {
                session()->flash('error', 'Database error occurred. Please try again or contact support.');
                Log::error('Database error in ExamDetail:', [
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ]);
            }

        } catch (\Exception $e) {
            // Handle other general exceptions
            session()->flash('error', 'An unexpected error occurred. Please try again or contact support.');
            Log::error('Unexpected error in ExamDetail:', [
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);
        }
    }

    public function confirmDelete()
    {
        // Only owner, admin, System, or Super Admin can delete
        if (! $this->canManageExam()) {
            session()->flash('error', 'You are not authorized to delete this exam.');

            return;
        }

        $this->confirmingDelete = true;
        $this->dispatch('confirmDelete');
    }

    public function deleteExam()
    {
        // Only owner, admin, System, or Super Admin can delete
        if (! $this->canManageExam()) {
            session()->flash('error', 'You are not authorized to delete this exam.');

            return;
        }

        $this->exam->delete();
        session()->flash('message', 'Exam deleted successfully.');
        $this->dispatch('examDeleted');

        return redirect()->route('examcenter');
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
    }

    public function updateStatus($newStatus)
    {
        // Only owner, admin, System, or Super Admin can update status
        if (! $this->canManageExam()) {
            return;
        }

        if (! array_key_exists($newStatus, $this->statusOptions)) {
            return;
        }

        $this->exam->update(['status' => $newStatus]);
        $this->status = $newStatus;
        session()->flash('message', 'Exam status updated successfully.');
    }

    public function getQuestionsProperty()
    {
        // Get all questions from question sets
        $questions = collect();

        foreach ($this->exam->questionSets as $questionSet) {
            $setQuestions = $questionSet->questions()->with('options')->get();
            $questions = $questions->merge($setQuestions);
        }

        // Also include direct questions for backward compatibility
        $directQuestions = $this->exam->questions()->with('options')->get();
        $questions = $questions->merge($directQuestions);

        // Get unique questions and return as collection (pagination removed for simplicity)
        return $questions->unique('id')->values();
    }

    public function toggleQuestionVisibility()
    {
        $this->showQuestions = ! $this->showQuestions;
    }

    public function render()
    {
        // Calculate total questions
        $totalQuestions = 0;

        foreach ($this->exam->questionSets as $questionSet) {
            $questionsInSet = $questionSet->questions()->count();
            $questionsToPick = $questionSet->pivot->questions_to_pick ?? 0;

            if ($questionsToPick > 0 && $questionsToPick < $questionsInSet) {
                $totalQuestions += $questionsToPick;
            } else {
                $totalQuestions += $questionsInSet;
            }
        }

        // Add direct questions
        $totalQuestions += $this->exam->questions()->count();

        return view('livewire.exam-detail', [
            'questions' => $this->showQuestions ? $this->questions : collect([]),
            'statusOptions' => $this->statusOptions,
            'totalQuestions' => $totalQuestions,
        ])->layout('components.dashboard.default', [
            'title' => $this->title,
        ]);
    }
}
