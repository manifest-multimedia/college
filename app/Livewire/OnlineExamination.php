<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSession;

use Livewire\Component;

class OnlineExamination extends Component
{
    public $examPassword = '';

    public function mount($examPassword)
    {
        $this->examPassword = $examPassword;
    }

    public function render()
    {
        $questions = [];
        $exam = Exam::with('course')
            ->where('password', $this->examPassword)->first();
        if ($exam) {

            $examQuestions = $exam->questions()->get();


            $questions = $examQuestions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'question' => $question->question_text,
                    'options' => $question->options()->get()->toArray(),
                    'marks' => $question->mark
                ];
            });
        }
        // dd($questions);

        return view('livewire.online-examination', [
            'questions' => $questions,
            'exam' => $exam
        ]);
    }
}
