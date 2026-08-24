<?php

namespace App\Livewire;

use App\Models\Course;
use App\Models\Exam;
use App\Models\QuestionSet;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class ExamEdit extends Component
{
    public $exam_slug;

    public $exam;

    public $title;

    public $description;

    public $duration;

    public $password;

    public $status;

    public $questions_per_session;

    public $passing_percentage;

    public $device_access_mode = 'open';

    public $allowed_device_types = [];

    // Question Set Management
    public $availableQuestionSets = [];

    public $assignedQuestionSets = [];

    public $selectedQuestionSetId;

    public $questionsToPickPerSet = [];

    public $shuffleQuestionsPerSet = [];

    public $showQuestionSetModal = false;

    public $managingQuestionSets = false;

    protected function rules()
    {
        return [
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string',
            'duration' => 'sometimes|integer|min:1',
            'password' => 'sometimes|string|min:4',
            'status' => 'sometimes|in:upcoming,active,completed',
            'questions_per_session' => 'sometimes|integer|min:1',
            'passing_percentage' => 'sometimes|numeric|min:0|max:100',
            'device_access_mode' => 'required|in:open,registered_devices_only',
            'allowed_device_types' => 'required_if:device_access_mode,registered_devices_only|array|min:1',
            'allowed_device_types.*' => 'in:mobile,laptop,desktop,tablet',
            'selectedQuestionSetId' => 'sometimes|exists:question_sets,id',
            'questionsToPickPerSet.*' => 'sometimes|integer|min:1',
            'shuffleQuestionsPerSet.*' => 'sometimes|boolean',
        ];
    }

    public function mount($exam_slug)
    {
        $this->exam_slug = $exam_slug;
        $this->exam = Exam::where('slug', $exam_slug)->firstOrFail();

        Log::info('Mounting ExamEdit', [
            'exam_status' => $this->exam->status,
            'exam_id' => $this->exam->id,
        ]);

        // Initialize the properties
        $this->title = $this->exam->title;
        $this->description = $this->exam->description;
        $this->duration = $this->exam->duration;
        $this->password = $this->exam->password;
        $this->status = $this->exam->status;
        $this->questions_per_session = $this->exam->questions_per_session;
        $this->passing_percentage = $this->exam->passing_percentage;
        $this->device_access_mode = $this->exam->device_access_mode ?? 'open';
        $this->allowed_device_types = $this->exam->allowed_device_types ?? [];

        // Load question sets
        $this->loadQuestionSets();
        $this->loadAssignedQuestionSets();
    }

    public function loadQuestionSets()
    {
        // Load available question sets based on exam's subject and user permissions
        $examSubjectId = $this->exam->course_id; // Assuming course_id maps to subject

        $query = QuestionSet::with('course')
            ->where('course_id', $examSubjectId);

        // Apply user permissions
        if (! Auth::user()->hasAnyRole(['System', 'Super Admin'])) {
            $query->where('created_by', Auth::id());
        }

        $this->availableQuestionSets = $query->get();
    }

    public function loadAssignedQuestionSets()
    {
        // Load already assigned question sets with their pivot data
        $this->assignedQuestionSets = $this->exam->questionSets()->get();

        // Initialize configuration arrays
        foreach ($this->assignedQuestionSets as $questionSet) {
            $this->questionsToPickPerSet[$questionSet->id] = $questionSet->pivot->questions_to_pick ?? 0;
            $this->shuffleQuestionsPerSet[$questionSet->id] = $questionSet->pivot->shuffle_questions ?? false;
        }
    }

    public function render()
    {
        return view('livewire.exam-edit', [
            'course' => $this->exam->course,
            'totalQuestions' => $this->getTotalQuestionsProperty(),
        ])->layout('layouts.portal');
    }

    public function updateExam()
    {
        try {
            $validatedData = $this->validate();

            $deviceAccessChanged = $this->exam->device_access_mode !== $this->device_access_mode
                || $this->exam->allowed_device_types !== ($this->device_access_mode === 'registered_devices_only' ? $this->allowed_device_types : null);

            if ($deviceAccessChanged && ! Auth::user()->hasAnyRole(['System', 'IT Manager', 'Administrator', 'Super Admin'])) {
                throw new AuthorizationException('Only authorised examination administrators can change device access rules.');
            }

            $validatedData['allowed_device_types'] = $this->device_access_mode === 'registered_devices_only'
                ? $this->allowed_device_types
                : null;
            Log::info('Validation passed', ['status' => $this->status]);

            // Only update fields that have been changed
            $updates = array_filter($validatedData, function ($value) {
                return ! is_null($value);
            });

            if (! empty($updates)) {
                $this->exam->update($updates);
                session()->flash('message', 'Exam updated successfully.');
            } else {
                session()->flash('message', 'No changes were made.');
            }

            $this->dispatch('examUpdated', $this->exam->slug);

        } catch (\Exception $e) {
            Log::error('Exam update failed', [
                'error' => $e->getMessage(),
                'status' => $this->status,
            ]);
            session()->flash('error', 'Failed to update exam. '.$e->getMessage());
        }
    }

    public function toggleQuestionSetManagement()
    {
        $this->managingQuestionSets = ! $this->managingQuestionSets;
    }

    public function assignQuestionSet()
    {
        $this->validate(['selectedQuestionSetId' => 'required|exists:question_sets,id']);

        // Check if already assigned
        if ($this->exam->questionSets()->where('question_set_id', $this->selectedQuestionSetId)->exists()) {
            session()->flash('error', 'Question set is already assigned to this exam.');

            return;
        }

        // Assign the question set
        $this->exam->questionSets()->attach($this->selectedQuestionSetId, [
            'shuffle_questions' => false,
            'questions_to_pick' => 0, // 0 means use all questions
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Reload assigned question sets
        $this->loadAssignedQuestionSets();
        $this->selectedQuestionSetId = null;

        session()->flash('message', 'Question set assigned successfully.');
    }

    public function removeQuestionSet($questionSetId)
    {
        $this->exam->questionSets()->detach($questionSetId);
        $this->loadAssignedQuestionSets();

        // Clean up configuration arrays
        unset($this->questionsToPickPerSet[$questionSetId]);
        unset($this->shuffleQuestionsPerSet[$questionSetId]);

        session()->flash('message', 'Question set removed successfully.');
    }

    public function updateQuestionSetConfig($questionSetId)
    {
        // Update the pivot table with new configuration
        $this->exam->questionSets()->updateExistingPivot($questionSetId, [
            'questions_to_pick' => $this->questionsToPickPerSet[$questionSetId] ?? 0,
            'shuffle_questions' => $this->shuffleQuestionsPerSet[$questionSetId] ?? false,
            'updated_at' => now(),
        ]);

        session()->flash('message', 'Question set configuration updated.');
    }

    public function getTotalQuestionsProperty()
    {
        $totalFromSets = 0;
        foreach ($this->assignedQuestionSets as $questionSet) {
            $questionsInSet = $questionSet->questions()->count();
            $questionsToPick = $this->questionsToPickPerSet[$questionSet->id] ?? 0;

            if ($questionsToPick > 0 && $questionsToPick < $questionsInSet) {
                $totalFromSets += $questionsToPick;
            } else {
                $totalFromSets += $questionsInSet;
            }
        }

        // Add direct questions (backward compatibility)
        $directQuestions = $this->exam->questions()->count();

        return $totalFromSets + $directQuestions;
    }

    public function cancel()
    {
        return redirect()->route('examcenter');
    }
}
