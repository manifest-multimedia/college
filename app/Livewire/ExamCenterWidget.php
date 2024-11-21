<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;

class ExamCenterWidget extends Component
{

    public function mount() {}

    // Handle form submission to create the exam


    public function render()
    {
        $exams = Exam::where('user_id', Auth::user()->id)->get();

        return view(
            'livewire.exam-center-widget',
            [
                'exams' => $exams,
            ]
        );
    }
}
