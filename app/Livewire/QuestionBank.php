<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;


class QuestionBank extends Component
{


    public $exam_id;
    public $questions = [];



    public $uploadPath;
    protected $rules = [
        'questions.*.question_text' => 'required|string',
        'questions.*.options.*.option_text' => 'required|string|max:255',
        'questions.*.options.*.is_correct' => 'required|boolean',
        'questions.*.marks' => 'integer|min:1',
        'questions.*.explanation' => 'nullable|string|max:500',
    ];

    public function mount($examId = null)
    {
        $this->exam_id = $examId;
        $this->loadQuestions();
    }



    public function loadQuestions()
    {
        if ($this->exam_id) {
            // Get questions from question sets associated with this exam (new approach)
            $exam = Exam::find($this->exam_id);
            if ($exam) {
                $questionSetIds = $exam->questionSets()->pluck('question_sets.id');
                $this->questions = Question::whereIn('question_set_id', $questionSetIds)
                    ->with('options')
                    ->get()
                    ->toArray(); // Convert to array for Livewire compatibility
            } else {
                $this->questions = [];
            }
        } else {
            $this->questions = [];
        }
    }


    public function addQuestion()
    {
        $this->questions[] = [
            'question_text' => '',
            'exam_section' => '',
            'marks' => 1,
            'explanation' => '',
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

        foreach ($this->questions as $index => $questionData) {
            // Get the question set for this exam
            $exam = Exam::find($this->exam_id);
            $questionSetId = $exam ? $exam->questionSets()->first()?->id : null;
            
            if (!$questionSetId) {
                session()->flash('error', 'No question set found for this exam. Please create a question set first.');
                return;
            }
            
            $question = Question::updateOrCreate(
                ['id' => $questionData['id'] ?? null],
                [
                    'question_set_id' => $questionSetId,
                    'exam_id' => null, // Questions now belong to sets, not directly to exams
                    'question_text' => $questionData['question_text'],
                    'exam_section' => $questionData['exam_section'],
                    'mark' => $questionData['marks'] ?? 1, // Note: field name is 'mark' not 'marks'
                    'explanation' => $questionData['explanation'],
                    'type' => 'MCQ', // Default type
                    'difficulty_level' => 'medium', // Default difficulty
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

    public function saveQuestion($questionIndex)
    {
      
        // Find the question by ID
        $questionData = $this->questions[$questionIndex];
       
        $question = Question::find($questionData['id']);
    
        if (!$question) {
            session()->flash('error', 'Question not found.');
            return;
        }

        // Update the question data
        $question->update([
            'question_text' => $questionData['question_text'],
            'exam_section' => $questionData['exam_section'],
            'mark' => $questionData['mark'],
            'explanation' => $questionData['explanation'],
        ]);

        // Get the existing options for the question
        $existingOptions = $question->options->keyBy('id')->toArray();
       
        // Iterate through the updated options
        foreach ($questionData['options'] as $optionIndex => $optionData) {
            $optionId = Arr::get($optionData, 'id');

            // Update or create the option
            Option::updateOrCreate(
                ['id' => $optionId],
                [
                    'question_id' => $question->id,
                    'option_text' => $optionData['option_text'],
                    'is_correct' => $optionData['is_correct'],
                ]
            );

            // Remove the option from the existing options array if it was updated
            if ($optionId) {
                unset($existingOptions[$optionId]);
            }
        }

        

        session()->flash('message', 'Question saved successfully.');
    }



    public function render()
    {
        if (Auth::user()->role !== 'Super Admin') {

            $exams = Exam::where('user_id', Auth::user()->id)->get();
        } else {
            $exams = Exam::all();
        }

        return view('livewire.question-bank', ['exams' => $exams, 'questions' => $this->questions]);
    }
}
