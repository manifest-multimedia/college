<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use App\Models\QuestionSet;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;


class QuestionBank extends Component
{
    public $exam_id;
    public $questions = [];
    public $question_set_id;
    public $question_sets = [];
    public $subjects = [];
    public $subject_id;
    public $filtered_question_sets = [];
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
    
    // Mode and routing properties
    public $mode = 'default'; // default, create_set, show_set, edit_set, manage_questions
    public $questionSetId = null;
    public $selectedQuestionSet = null;
    public $viewMode = 'sets'; // sets, questions, create_set



    public $uploadPath;
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
        
        // Load initial data
        $this->loadSubjects();
        $this->loadAllQuestionSets();
        $this->applyQuestionSetFilter();
        
        // If we have a questionSetId, load it
        if ($this->questionSetId) {
            $this->selectedQuestionSet = QuestionSet::find($this->questionSetId);
            if ($this->selectedQuestionSet) {
                $this->subject_id = $this->selectedQuestionSet->course_id;
                $this->viewMode = 'questions';
                $this->question_set_id = $this->questionSetId;
                $this->loadQuestions();
            }
        }
        
        // Set view mode based on mode
        switch ($this->mode) {
            case 'create_set':
                $this->viewMode = 'create_set';
                break;
            case 'show_set':
            case 'edit_set':
            case 'manage_questions':
                $this->viewMode = 'questions';
                break;
            default:
                // For question sets view, ensure we show sets
                $this->viewMode = 'sets';
                break;
        }
    }

    public function loadSubjects()
    {
        // Get subjects based on user role (using Subject model instead of Course)
        if (Auth::user()->role === 'Super Admin') {
            $this->subjects = Subject::all();
        } else {
            // Get subjects for courses the user has access to
            $this->subjects = Subject::all(); // Adjust based on your access control logic
        }
    }

    public function loadAllQuestionSets()
    {
        if (Auth::user()->role === 'Super Admin') {
            $this->question_sets = QuestionSet::with('course')->get();
        } else {
            // Get question sets for subjects the user has access to
            $this->question_sets = QuestionSet::with('course')->get(); // Adjust based on your access control
        }
    }

    public function applyQuestionSetFilter()
    {
        if (!$this->subject_id) {
            $this->filtered_question_sets = collect($this->question_sets);
        } else {
            $this->filtered_question_sets = collect($this->question_sets)->filter(function($set) {
                return $set->course_id == $this->subject_id;
            })->values();
        }
    }

    public function updatedSubjectId()
    {
        $this->applyQuestionSetFilter();
        $this->applyQuestionSetFilter();
        $this->question_set_id = null;
        $this->questions = [];
        $this->viewingQuestionSet = false;
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
                    ->with('options');
                $this->questions = $this->applyFiltersToQuery($query)
                    ->get()
                    ->toArray(); // Convert to array for Livewire compatibility
            } else {
                $this->questions = [];
            }
        } elseif ($this->question_set_id) {
            // Get questions for specific question set with filtering
            $query = Question::where('question_set_id', $this->question_set_id)
                ->with('options');
            $this->questions = $this->applyFiltersToQuery($query)
                ->get()
                ->toArray();
        } else {
            $this->questions = [];
        }
    }
    
    public function applyFiltersToQuery($query)
    {
        // Search by question text
        if (!empty($this->searchTerm)) {
            $query->where('question_text', 'LIKE', '%' . $this->searchTerm . '%');
        }
        
        // Filter by type
        if (!empty($this->filterType)) {
            $query->where('type', $this->filterType);
        }
        
        // Filter by difficulty
        if (!empty($this->filterDifficulty)) {
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
                ['option_text' => '', 'is_correct' => false]
            ]
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
                
                if (!$hasCorrectOption) {
                    session()->flash('error', 'Question #' . ($index + 1) . ' must have at least one correct option.');
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
                
                if (!$targetQuestionSetId) {
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
            
            if (!$hasCorrectOption) {
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
        // Check if the question set has questions
        $questionCount = Question::where('question_set_id', $setId)->count();
        
        if ($questionCount > 0) {
            session()->flash('error', 'Cannot delete question set with questions. Please delete all questions first.');
            return;
        }
        
        QuestionSet::find($setId)->delete();
        session()->flash('message', 'Question set deleted successfully.');
        $this->loadAllQuestionSets();
        $this->applyQuestionSetFilter();
    }
    
    // Advanced Question Set Management Methods
    public function duplicateQuestionSet($setId)
    {
        $this->duplicateSetId = $setId;
        $originalSet = QuestionSet::find($setId);
        $this->newDuplicateSetName = $originalSet->name . ' (Copy)';
    }
    
    public function confirmDuplicate()
    {
        $this->validate(['newDuplicateSetName' => 'required|string|max:255']);
        
        $originalSet = QuestionSet::with('questions.options')->find($this->duplicateSetId);
        
        // Create duplicate question set
        $duplicateSet = QuestionSet::create([
            'name' => $this->newDuplicateSetName,
            'description' => $originalSet->description . ' (Duplicated)',
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
        
        session()->flash('message', 'Question set duplicated successfully with ' . $originalSet->questions->count() . ' questions.');
    }
    
    public function toggleBulkMode()
    {
        $this->bulkMode = !$this->bulkMode;
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
        
        session()->flash('message', count($this->selectedQuestions) . ' questions deleted successfully.');
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
                'exam_id' => null
            ]);
        
        $targetSet = QuestionSet::find($this->targetQuestionSetForMove);
        session()->flash('message', count($this->selectedQuestions) . ' questions moved to "' . $targetSet->name . '" successfully.');
        
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
        
        session()->flash('message', count($this->selectedQuestions) . ' questions updated to ' . ucfirst($difficulty) . ' difficulty.');
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
        
        if (!$questionSet) return null;
        
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
        $availableQuestionSetsForMove = collect($this->question_sets)->filter(function($set) {
            return $set->id !== $this->question_set_id;
        });

        return view('livewire.question-bank-enhanced', [
            'exams' => $exams, 
            'questions' => $this->questions,
            'filteredQuestionSets' => $this->filtered_question_sets,
            'subjects' => $this->subjects,
            'currentQuestionSet' => $currentQuestionSet,
            'questionSetStats' => $questionSetStats,
            'availableQuestionSetsForMove' => $availableQuestionSetsForMove,
        ]);
    }
}
