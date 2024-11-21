<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Question;

class QuestionBank extends Component
{
    public function render()
    {
        $questions = Question::all();
        return view('livewire.question-bank', [
            'questions' => $questions
        ]);
    }
}
