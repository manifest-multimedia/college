<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QuestionBank extends Component
{
    public $exam_id;

    public $questions = [];

    public $question_set_id;

    public $question_sets = [];

    public $subjects = [];

    public $subject_id;

    public $filtered_question_sets;

    public $createNewSet = false;

    public $newSetName = '';

    public $newSetDescription = '';

    public $newSetDifficulty = 'medium';

    public $selectedQuestionType = 'MCQ';

    public $viewingQuestionSet = false;

    public $difficultyLevels = [
        'easy' => 'Easy',
        'medium' => 'Medium',
        'hard' => 'Hard',
    ];

    public $questionTypes = [
        'MCQ' => 'Multiple Choice (Single Answer)',
        // Prepared for future types:
        // 'MA' => 'Multiple Answer',
        // 'TF' => 'True/False',
        // 'ESSAY' => 'Essay Question',
        // 'MATCH' => 'Matching Question',
    ];

    // Advanced management features
    public $bulkMode = false;

    public $selectedQuestions = [];

    public $searchTerm = '';

    public $filterType = '';

    public $filterDifficulty = '';

    public $showAdvancedFilters = false;

    public $showStatistics = false;

    public $duplicateSetId = null;

    public $newDuplicateSetName = '';

    public $targetQuestionSetForMove = '';

    public $showQuestionImport = false;

    public $importFile = null;

    // Copy question set properties
    public $isCopyModalOpen = false;
    public $copyingSetId = null;
    public $copySetName = '';
    public $copyTargetCourseId = null;
    public $copyFilterProgram = null;
    public $copyFilterYear = null;
    public $copyFilterSemester = null;
    public $copyFilterAcademicYear = null;
    public $availablePrograms = [];
    public $availableYears = [];
    public $availableSemesters = [];
    public $availableAcademicYears = [];
    public $filteredCourses = [];

    // Mode and routing properties
    public $mode = 'default'; // default, create_set, show_set, edit_set, manage_questions

    public $questionSetId = null;

    public $selectedQuestionSet = null;

    public $viewMode = 'sets'; // sets, questions, create_set

    public $uploadPath;

    protected $queryString = [
        'question_set_id' => ['except' => null],
        'viewMode' => ['except' => 'sets'],
        'subject_id' => ['except' => null],
        'searchTerm' => ['except' => ''],
        'filterDifficulty' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    protected $rules = [
        'questions.*.question_text' => 'required|string',
        'questions.*.options.*.option_text' => 'required|string|max:255',
        'questions.*.options.*.is_correct' => 'required|boolean',
        'questions.*.marks' => 'integer|min:1',
        'questions.*.explanation' => 'nullable|string|max:500',
        'questions.*.type' => 'nullable|string',
        'questions.*.difficulty_level' => 'nullable|string|in:easy,medium,hard',
        'newSetName' => 'required_if:createNewSet,true|string|max:255',
        'newSetDescription' => 'nullable|string|max:500',
        'newSetDifficulty' => 'required|in:easy,medium,hard',
        'newDuplicateSetName' => 'required_if:duplicateSetId,not_null|string|max:255',
        'targetQuestionSetForMove' => 'required_when:bulkMode,true|exists:question_sets,id',
        'searchTerm' => 'nullable|string|max:255',
    ];

    public function mount($exam_id = null, $mode = 'default', $questionSetId = null)
    {
        $this->exam_id = $exam_id;
        $this->mode = $mode;
        $this->questionSetId = $questionSetId;

        // If question_set_id is in query string but not passed as parameter, use it
        if (!$this->questionSetId && $this->question_set_id) {
            $this->questionSetId = $this->question_set_id;
        }

        // Initialize collections
        $this->filtered_question_sets = collect();

        // Load initial data
        $this->loadSubjects();
        $this->loadAllQuestionSets();
        $this->applyQuestionSetFilter();

        // Debug: Log the counts
        Log::info('QuestionBank Mount Debug:', [
            'subjects_count' => count($this->subjects),
            'question_sets_count' => count($this->question_sets),
            'filtered_sets_count' => $this->filtered_question_sets->count(),
            'mode' => $this->mode,
            'viewingQuestionSet' => $this->viewingQuestionSet,
        ]);

        // If we have a questionSetId, load it
        if ($this->questionSetId || $this->question_set_id) {
            $setId = $this->questionSetId ?? $this->question_set_id;
            $this->selectedQuestionSet = QuestionSet::find($setId);
            if ($this->selectedQuestionSet) {
                $this->subject_id = $this->selectedQuestionSet->course_id;
                $this->viewMode = 'questions';
                $this->question_set_id = $setId;
                $this->viewingQuestionSet = true;  // Set this to true!
                $this->loadQuestions();
            }
        }

        // Set view mode based on mode
        switch ($this->mode) {
            case 'create_set':
                $this->viewMode = 'create_set';
                $this->viewingQuestionSet = false;
                break;
            case 'show_set':
            case 'edit_set':
            case 'manage_questions':
                $this->viewMode = 'questions';
                $this->viewingQuestionSet = true;
                break;
            default:
                // For question sets view, ensure we show sets if we're not viewing a specific set
                if (!$this->question_set_id) {
                    $this->viewMode = 'sets';
                    $this->viewingQuestionSet = false;
                }
                break;
        }
    }

    public function loadSubjects()
    {
        // Get subjects based on user role - consistent with exam access control
        if (Auth::user()->hasRole(['Super Admin', 'Administrator', 'admin'])) {
            $this->subjects = Subject::all();
        } else {
            // Regular lecturers get all subjects (they can create question sets for any subject)
            $this->subjects = Subject::all();
        }
    }

    public function loadAllQuestionSets()
    {
        // Apply same role-based filtering as exams
        if (Auth::user()->hasRole(['Super Admin', 'Administrator', 'admin'])) {
            // Super Admin and System roles can see all question sets
            $this->question_sets = QuestionSet::with(['course', 'creator'])->get();
        } else {
            // Lecturers can only see question sets they created
            $this->question_sets = QuestionSet::with(['course', 'creator'])
                ->where('created_by', Auth::id())
                ->get();
        }
    }

    public function applyQuestionSetFilter()
    {
        if (! $this->subject_id) {
            $this->filtered_question_sets = collect($this->question_sets);
        } else {
            $this->filtered_question_sets = collect($this->question_sets)->filter(function ($set) {
                return $set->course_id == $this->subject_id;
            })->values();
        }
    }

    public function updatedSubjectId()
    {
        $this->question_set_id = null;
        $this->questions = [];
        $this->selectedQuestions = [];
        $this->viewingQuestionSet = false;
        $this->applyQuestionSetFilter();
    }

    public function updatedSearchTerm()
    {
        if ($this->viewingQuestionSet && $this->question_set_id) {
            $this->loadQuestions();
        }
    }

    public function updatedFilterType()
    {
        if ($this->viewingQuestionSet && $this->question_set_id) {
            $this->loadQuestions();
        }
    }

    public function updatedFilterDifficulty()
    {
        if ($this->viewingQuestionSet && $this->question_set_id) {
            $this->loadQuestions();
        }
    }

    public function viewQuestionSet($setId)
    {
        $this->question_set_id = $setId;
        $this->loadQuestions();
        $this->viewingQuestionSet = true;
    }

    public function backToQuestionSets()
    {
        $this->viewingQuestionSet = false;
        $this->question_set_id = null;
        $this->questions = [];
    }

    public function loadQuestions()
    {
        if ($this->exam_id) {
            // Get questions from question sets associated with this exam (new approach)
            $exam = Exam::find($this->exam_id);
            if ($exam) {
                $questionSetIds = $exam->questionSets()->pluck('question_sets.id');
                $query = Question::whereIn('question_set_id', $questionSetIds)
                    ->with(['options', 'attachments']);
                $questions = $this->applyFiltersToQuery($query)->get();
                
                // Manually map to array to ensure attachments are included
                $this->questions = $questions->map(function ($question) {
                    $questionArray = $question->toArray();
                    $questionArray['attachments'] = $question->attachments->toArray();
                    return $questionArray;
                })->toArray();
            } else {
                $this->questions = [];
            }
        } elseif ($this->question_set_id) {
            // Get questions for specific question set with filtering
            $query = Question::where('question_set_id', $this->question_set_id)
                ->with(['options', 'attachments']);
            $questions = $this->applyFiltersToQuery($query)->get();
            
            // Manually map to array to ensure attachments are included
            $this->questions = $questions->map(function ($question) {
                $questionArray = $question->toArray();
                $questionArray['attachments'] = $question->attachments->toArray();
                return $questionArray;
            })->toArray();
        } else {
            $this->questions = [];
        }
    }

    public function applyFiltersToQuery($query)
    {
        // Search by question text
        if (! empty($this->searchTerm)) {
            $query->where('question_text', 'LIKE', '%'.$this->searchTerm.'%');
        }

        // Filter by type
        if (! empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }

        // Filter by difficulty
        if (! empty($this->filterDifficulty)) {
            $query->where('difficulty_level', $this->filterDifficulty);
        }

        return $query;
    }

    public function clearFilters()
    {
        $this->searchTerm = '';
        $this->filterType = '';
        $this->filterDifficulty = '';
        $this->loadQuestions();
    }

    public function createQuestionSet()
    {
        $this->validate([
            'newSetName' => 'required|string|max:255',
            'newSetDescription' => 'nullable|string|max:500',
            'subject_id' => 'required',
            'newSetDifficulty' => 'required|in:easy,medium,hard',
        ]);

        $questionSet = QuestionSet::create([
            'name' => $this->newSetName,
            'description' => $this->newSetDescription,
            'course_id' => $this->subject_id,
            'created_by' => Auth::id(),
            'difficulty_level' => $this->newSetDifficulty,
        ]);

        $this->loadAllQuestionSets();
        $this->applyQuestionSetFilter();
        $this->createNewSet = false;
        $this->newSetName = '';
        $this->newSetDescription = '';

        session()->flash('message', 'Question set created successfully.');
    }

    public function addQuestion()
    {
        $this->questions[] = [
            'question_text' => '',
            'exam_section' => '',
            'marks' => 1,
            'explanation' => '',
            'type' => $this->selectedQuestionType,
            'difficulty_level' => 'medium',
            'options' => [
                ['option_text' => '', 'is_correct' => false],
                ['option_text' => '', 'is_correct' => false],
            ],
        ];
    }

    public function addOption($index)
    {
        $this->questions[$index]['options'][] = [
            'option_text' => '',
            'is_correct' => false,
        ];
        $this->questions[$index]['options'] = array_values($this->questions[$index]['options']);
    }

    public function removeOption($index, $optionIndex)
    {
        // Remove the option
        unset($this->questions[$index]['options'][$optionIndex]);
        $this->questions[$index]['options'] = array_values($this->questions[$index]['options']);

        // Check if there are no options left for the question
        if (count($this->questions[$index]['options']) === 0) {
            // Optionally, remove the entire question
            unset($this->questions[$index]);
        }
    }

    public function saveQuestions()
    {
        $this->validate();

        // Validate that MCQ questions have at least one correct option
        foreach ($this->questions as $index => $questionData) {
            $type = $questionData['type'] ?? 'MCQ';

            if ($type === 'MCQ') {
                $hasCorrectOption = false;
                foreach ($questionData['options'] as $option) {
                    if ($option['is_correct']) {
                        $hasCorrectOption = true;
                        break;
                    }
                }

                if (! $hasCorrectOption) {
                    session()->flash('error', 'Question #'.($index + 1).' must have at least one correct option.');

                    return;
                }
            }
        }

        foreach ($this->questions as $index => $questionData) {
            $questionData['type'] = $questionData['type'] ?? 'MCQ';

            // Determine target (exam or question set)
            $targetQuestionSetId = null;
            if ($this->exam_id) {
                $exam = Exam::find($this->exam_id);
                $targetQuestionSetId = $exam ? $exam->questionSets()->first()?->id : null;

                if (! $targetQuestionSetId) {
                    session()->flash('error', 'No question set found for this exam. Please create a question set first.');

                    return;
                }
            } else {
                $targetQuestionSetId = $this->question_set_id;
            }

            $question = Question::updateOrCreate(
                ['id' => $questionData['id'] ?? null],
                [
                    'question_set_id' => $targetQuestionSetId,
                    'exam_id' => null, // Questions now belong to sets, not directly to exams
                    'question_text' => $questionData['question_text'],
                    'exam_section' => $questionData['exam_section'] ?? '',
                    'mark' => $questionData['marks'] ?? 1, // Note: field name is 'mark' not 'marks'
                    'explanation' => $questionData['explanation'] ?? '',
                    'type' => $questionData['type'],
                    'difficulty_level' => $questionData['difficulty_level'] ?? 'medium',
                ]
            );

            // Delete existing options
            Option::where('question_id', $question->id)->delete();

            // Save or update options
            foreach ($questionData['options'] as $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'],
                ]);
            }
        }
        session()->flash('message', 'Questions saved successfully.');
        $this->loadQuestions();
    }

    public function deleteQuestion($questionId)
    {
        Question::find($questionId)->delete();
        session()->flash('message', 'Question deleted successfully.');
        $this->loadQuestions();
    }

    public function saveQuestion($index)
    {
        // Validate single question
        $this->validateOnly("questions.$index");

        $questionData = $this->questions[$index];
        $questionData['type'] = $questionData['type'] ?? 'MCQ';

        // For MCQ, validate that there is at least one correct option
        if ($questionData['type'] === 'MCQ') {
            $hasCorrectOption = false;
            foreach ($questionData['options'] as $option) {
                if ($option['is_correct']) {
                    $hasCorrectOption = true;
                    break;
                }
            }

            if (! $hasCorrectOption) {
                session()->flash('error', 'Question must have at least one correct option.');

                return;
            }
        }

        // Determine target (exam or question set)
        $targetQuestionSetId = null;
        if ($this->exam_id) {
            $exam = Exam::find($this->exam_id);
            $targetQuestionSetId = $exam ? $exam->questionSets()->first()?->id : null;
        } else {
            $targetQuestionSetId = $this->question_set_id;
        }

        $question = Question::updateOrCreate(
            ['id' => $questionData['id'] ?? null],
            [
                'question_set_id' => $targetQuestionSetId,
                'exam_id' => null,
                'question_text' => $questionData['question_text'],
                'exam_section' => $questionData['exam_section'] ?? '',
                'mark' => $questionData['marks'] ?? 1,
                'explanation' => $questionData['explanation'] ?? '',
                'type' => $questionData['type'],
                'difficulty_level' => $questionData['difficulty_level'] ?? 'medium',
            ]
        );

        // Delete existing options for this question
        Option::where('question_id', $question->id)->delete();

        // Save or update options
        foreach ($questionData['options'] as $optionData) {
            Option::create([
                'question_id' => $question->id,
                'option_text' => $optionData['option_text'],
                'is_correct' => $optionData['is_correct'],
            ]);
        }

        // Update the question ID in the array (needed for new questions)
        $this->questions[$index]['id'] = $question->id;

        session()->flash('message', 'Question saved successfully.');
    }

    public function deleteQuestionSet($setId)
    {
        $questionSet = QuestionSet::find($setId);

        if (! $questionSet) {
            session()->flash('error', 'Question set not found.');

            return;
        }

        // Check permissions - only creator or Super Admin can delete
        if (! Auth::user()->hasRole(['Super Admin', 'Administrator', 'admin']) && $questionSet->created_by !== Auth::id()) {
            session()->flash('error', 'You do not have permission to delete this question set.');

            return;
        }

        // Check if the question set has questions
        $questionCount = Question::where('question_set_id', $setId)->count();

        if ($questionCount > 0) {
            session()->flash('error', 'Cannot delete question set with questions. Please delete all questions first.');

            return;
        }

        $questionSet->delete();
        session()->flash('message', 'Question set deleted successfully.');
        $this->loadAllQuestionSets();
        $this->applyQuestionSetFilter();
    }

    // Advanced Question Set Management Methods
    public function duplicateQuestionSet($setId)
    {
        $this->duplicateSetId = $setId;
        $originalSet = QuestionSet::find($setId);
        $this->newDuplicateSetName = $originalSet->name.' (Copy)';
    }

    public function confirmDuplicate()
    {
        $this->validate(['newDuplicateSetName' => 'required|string|max:255']);

        $originalSet = QuestionSet::with('questions.options')->find($this->duplicateSetId);

        if (! $originalSet) {
            session()->flash('error', 'Original question set not found.');

            return;
        }

        // Check permissions - only creator or Super Admin can duplicate
        if (! Auth::user()->hasRole(['Super Admin', 'Administrator', 'admin']) && $originalSet->created_by !== Auth::id()) {
            session()->flash('error', 'You do not have permission to duplicate this question set.');

            return;
        }

        // Create duplicate question set
        $duplicateSet = QuestionSet::create([
            'name' => $this->newDuplicateSetName,
            'description' => $originalSet->description.' (Duplicated)',
            'course_id' => $originalSet->course_id,
            'created_by' => Auth::id(),
            'difficulty_level' => $originalSet->difficulty_level,
        ]);

        // Duplicate all questions and their options
        foreach ($originalSet->questions as $question) {
            $newQuestion = Question::create([
                'question_set_id' => $duplicateSet->id,
                'question_text' => $question->question_text,
                'exam_section' => $question->exam_section,
                'mark' => $question->mark,
                'explanation' => $question->explanation,
                'type' => $question->type,
                'difficulty_level' => $question->difficulty_level,
            ]);

            // Duplicate options
            foreach ($question->options as $option) {
                Option::create([
                    'question_id' => $newQuestion->id,
                    'option_text' => $option->option_text,
                    'is_correct' => $option->is_correct,
                ]);
            }
        }

        $this->loadAllQuestionSets();
        $this->applyQuestionSetFilter();
        $this->duplicateSetId = null;
        $this->newDuplicateSetName = '';

        session()->flash('message', 'Question set duplicated successfully with '.$originalSet->questions->count().' questions.');
    }

    public function toggleBulkMode()
    {
        $this->bulkMode = ! $this->bulkMode;
        $this->selectedQuestions = [];
    }

    public function selectAllQuestions()
    {
        $questionIds = collect($this->questions)->pluck('id')->toArray();
        $this->selectedQuestions = $questionIds;
    }

    public function deselectAllQuestions()
    {
        $this->selectedQuestions = [];
    }

    public function bulkDeleteQuestions()
    {
        if (empty($this->selectedQuestions)) {
            session()->flash('error', 'No questions selected for deletion.');

            return;
        }

        Question::whereIn('id', $this->selectedQuestions)->delete();

        session()->flash('message', count($this->selectedQuestions).' questions deleted successfully.');
        $this->selectedQuestions = [];
        $this->loadQuestions();
    }

    public function bulkMoveQuestions()
    {
        $this->validate(['targetQuestionSetForMove' => 'required|exists:question_sets,id']);

        if (empty($this->selectedQuestions)) {
            session()->flash('error', 'No questions selected for moving.');

            return;
        }

        Question::whereIn('id', $this->selectedQuestions)
            ->update([
                'question_set_id' => $this->targetQuestionSetForMove,
                'exam_id' => null,
            ]);

        $targetSet = QuestionSet::find($this->targetQuestionSetForMove);
        session()->flash('message', count($this->selectedQuestions).' questions moved to "'.$targetSet->name.'" successfully.');

        $this->selectedQuestions = [];
        $this->targetQuestionSetForMove = '';
        $this->loadQuestions();
    }

    public function bulkUpdateDifficulty($difficulty)
    {
        if (empty($this->selectedQuestions)) {
            session()->flash('error', 'No questions selected for difficulty update.');

            return;
        }

        Question::whereIn('id', $this->selectedQuestions)
            ->update(['difficulty_level' => $difficulty]);

        session()->flash('message', count($this->selectedQuestions).' questions updated to '.ucfirst($difficulty).' difficulty.');
        $this->selectedQuestions = [];
        $this->loadQuestions();
    }

    public function getQuestionSets()
    {
        // Ensure we have loaded question sets
        if (empty($this->question_sets)) {
            $this->loadAllQuestionSets();
        }

        // Apply current filter
        $this->applyQuestionSetFilter();

        return $this->filtered_question_sets;
    }

    public function getQuestionSetStatistics($questionSetId)
    {
        $questionSet = QuestionSet::find($questionSetId);

        if (! $questionSet) {
            return null;
        }

        $stats = [
            'total_questions' => $questionSet->questions()->count(),
            'difficulty_breakdown' => [
                'easy' => $questionSet->questions()->where('difficulty_level', 'easy')->count(),
                'medium' => $questionSet->questions()->where('difficulty_level', 'medium')->count(),
                'hard' => $questionSet->questions()->where('difficulty_level', 'hard')->count(),
            ],
            'type_breakdown' => [
                'MCQ' => $questionSet->questions()->where('type', 'MCQ')->count(),
            ],
            'total_marks' => $questionSet->questions()->sum('mark'),
            'avg_marks_per_question' => $questionSet->questions()->avg('mark'),
            'questions_with_explanations' => $questionSet->questions()->whereNotNull('explanation')->where('explanation', '!=', '')->count(),
        ];

        return $stats;
    }

    /**
     * Show copy modal for a question set
     */
    public function showCopyModal($questionSetId)
    {
        Log::info('showCopyModal called', ['question_set_id' => $questionSetId]);
        
        $this->copyingSetId = $questionSetId;
        $questionSet = QuestionSet::find($questionSetId);
        
        if ($questionSet) {
            $this->copySetName = $questionSet->name . ' (Copy)';
            $this->isCopyModalOpen = true;
            
            // Load filter options - convert to arrays for Livewire
            $this->availablePrograms = \App\Models\CollegeClass::all()->toArray();
            $this->availableYears = \App\Models\Year::all()->toArray();
            $this->availableSemesters = \App\Models\Semester::all()->toArray();
            $this->availableAcademicYears = \App\Models\AcademicYear::orderBy('name', 'desc')->get()->toArray();
            
            Log::info('Copy modal opened', [
                'question_set_id' => $questionSetId,
                'copy_set_name' => $this->copySetName,
                'programs_count' => count($this->availablePrograms),
            ]);
        } else {
            Log::error('Question set not found', ['question_set_id' => $questionSetId]);
        }
    }

    /**
     * Update filtered courses based on selected filters
     */
    public function updatedCopyFilterProgram()
    {
        $this->loadFilteredCourses();
    }

    public function updatedCopyFilterYear()
    {
        $this->loadFilteredCourses();
    }

    public function updatedCopyFilterSemester()
    {
        $this->loadFilteredCourses();
    }

    public function updatedCopyFilterAcademicYear()
    {
        $this->loadFilteredCourses();
    }

    /**
     * Load courses based on filters
     */
    public function loadFilteredCourses()
    {
        $query = Subject::query();

        if ($this->copyFilterProgram) {
            $query->where('college_class_id', $this->copyFilterProgram);
        }
        if ($this->copyFilterYear) {
            $query->where('year_id', $this->copyFilterYear);
        }
        if ($this->copyFilterSemester) {
            $query->where('semester_id', $this->copyFilterSemester);
        }

        $this->filteredCourses = $query->orderBy('name', 'asc')->get(['id', 'name', 'course_code'])->toArray();
    }

    /**
     * Copy question set to target course
     */
    public function copyQuestionSet()
    {
        $this->validate([
            'copySetName' => 'required|string|max:255',
            'copyTargetCourseId' => 'required|exists:subjects,id',
        ], [
            'copySetName.required' => 'Please enter a name for the copied question set.',
            'copyTargetCourseId.required' => 'Please select a target course.',
        ]);

        try {
            $originalSet = QuestionSet::with('questions.options')->findOrFail($this->copyingSetId);

            // Create new question set
            $newSet = QuestionSet::create([
                'name' => $this->copySetName,
                'description' => $originalSet->description,
                'course_id' => $this->copyTargetCourseId,
                'difficulty_level' => $originalSet->difficulty_level,
                'created_by' => Auth::id(),
            ]);

            // Copy all questions with their options
            foreach ($originalSet->questions as $question) {
                $newQuestion = Question::create([
                    'question_set_id' => $newSet->id,
                    'question_text' => $question->question_text,
                    'explanation' => $question->explanation,
                    'mark' => $question->mark,
                    'type' => $question->type,
                    'difficulty_level' => $question->difficulty_level,
                ]);

                // Copy options if they exist
                foreach ($question->options as $option) {
                    Option::create([
                        'question_id' => $newQuestion->id,
                        'option_text' => $option->option_text,
                        'is_correct' => $option->is_correct,
                        'option_letter' => $option->option_letter,
                    ]);
                }
            }

            session()->flash('success', "Question set '{$this->copySetName}' copied successfully with {$originalSet->questions->count()} questions!");
            
            // Reset modal
            $this->closeCopyModal();
            
            // Reload question sets
            $this->loadQuestionSets();

        } catch (\Exception $e) {
            Log::error('Error copying question set', [
                'error' => $e->getMessage(),
                'question_set_id' => $this->copyingSetId,
            ]);
            session()->flash('error', 'Failed to copy question set: ' . $e->getMessage());
        }
    }

    /**
     * Close copy modal
     */
    public function closeCopyModal()
    {
        $this->isCopyModalOpen = false;
        $this->copyingSetId = null;
        $this->copySetName = '';
        $this->copyTargetCourseId = null;
        $this->copyFilterProgram = null;
        $this->copyFilterYear = null;
        $this->copyFilterSemester = null;
        $this->copyFilterAcademicYear = null;
        $this->filteredCourses = [];
    }

    public function render()
    {
        $exams = [];
        if (Auth::user()->role !== 'Super Admin') {
            $exams = Exam::where('user_id', Auth::user()->id)->get();
        } else {
            $exams = Exam::all();
        }

        $currentQuestionSet = null;
        $questionSetStats = null;
        if ($this->question_set_id) {
            $currentQuestionSet = QuestionSet::with('course')->find($this->question_set_id);
            if ($this->showStatistics && $currentQuestionSet) {
                $questionSetStats = $this->getQuestionSetStatistics($this->question_set_id);
            }
        }

        // Get available question sets for bulk move operations
        $availableQuestionSetsForMove = collect($this->question_sets)->filter(function ($set) {
            return $set->id !== $this->question_set_id;
        });

        return view('livewire.question-bank-enhanced', [
            'exams' => $exams,
            'questions' => $this->questions,
            'filteredQuestionSets' => $this->getQuestionSets(),
            'subjects' => $this->subjects,
            'currentQuestionSet' => $currentQuestionSet,
            'questionSetStats' => $questionSetStats,
            'availableQuestionSetsForMove' => $availableQuestionSetsForMove,
        ]);
    }
}
