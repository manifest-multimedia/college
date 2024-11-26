<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Exam;
use Livewire\Component;

class ExamLogin extends Component
{
    public $studentId;
    public $examPassword;

    public function render()
    {
        return view('livewire.exam-login');
    }

    public function startExam()
    {
        $this->validate([
            'studentId' => 'required',
            'examPassword' => 'required',
        ]);

        $student = Student::where('student_id', $this->studentId)->first();

        if (!$student) {
            session()->flash('error', 'Invalid Student ID');
            return;
        }

        $exam = Exam::where('password', $this->examPassword)->first();

        if (!$exam) {
            session()->flash('error', 'Invalid Exam Password');
            return;
        }

        // Additional validation: Check if the student is eligible to take the exam
        if (!$student->isEligibleForExam($exam)) {
            session()->flash('error', 'You are not eligible to take this exam');
            return;
        }

        // Login successful, redirect to the exam page
        return redirect()->route('exams', ['slug' => $exam->slug]);
    }
}
