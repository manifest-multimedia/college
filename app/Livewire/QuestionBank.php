<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;


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
            $this->questions = Question::where('exam_id', $this->exam_id)
                ->with('options')
                ->get()
                ->toArray(); // Convert to array for Livewire compatibility
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
            $question = Question::updateOrCreate(
                ['id' => $questionData['id'] ?? null],
                [
                    'exam_id' => $this->exam_id,
                    'question_text' => $questionData['question_text'],
                    'exam_section' => $questionData['exam_section'],
                    'marks' => $questionData['marks'] ?? 1,
                    'explanation' => $questionData['explanation'],
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

    public function saveQuestion($questionId)
    {

        $question = Question::find($questionId);
        $question->question_text = $this->questions[$questionId]['question_text'];
        $question->marks = $this->questions[$questionId]['marks'];
        $question->explanation = $this->questions[$questionId]['explanation'];
        $question->save();
        session()->flash('message', 'Question saved successfully.');
        $this->loadQuestions();
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
