<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Exam;
use Livewire\Component;
use App\Models\User;

class ExamLogin extends Component
{
    public $studentId;
    public $examPassword;

    public function mount()
    {
        // check environment set values for local
        if (env('APP_ENV') == 'local') {

            // $this->studentId = "PNMTC/DA/RGN/24/25/001";
            $this->examPassword = "Tg1ecnvj";
        }
    }

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
        // if (!$student->isEligibleForExam()) {
        //     session()->flash('error', "
        //     Dear " . $student->first_name . ",
        //     You are not eligible to take this exam. You have pending fees to clear. Please see the accounts Office for clearance.");
        //     return;
        // }
        // Login successful, redirect to the exam page

        // Create User for Student
        try {
            // Check if Student has user account, else create one
            if (User::where('email', $student->email)->exists()) {
                return redirect()->route('exams', [
                    'slug' => $exam->slug,

                    'student_id' => $student->id
                ]);
            } else {

                $student->createUser();
                return redirect()->route('exams', [
                    'slug' => $exam->slug,

                    'student_id' => $student->id
                ]);
            }
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
