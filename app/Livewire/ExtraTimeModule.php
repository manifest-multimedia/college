<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;

class ExtraTimeModule extends Component
{
    public function render()
    {
        /*
        Get All users whose emails are in the students table
        and whose emails are same as in the users table
        */
        $collect_student_emails = Student::select('email')->get();
        $students = Student::with('user')->get();
        // foreach ($students as $student) {
        //     # code...
        //     dd($student->user->examSessions);
        // }
        $collect_student_ids = $students->pluck('id');
        /*
        Get all Exam Sessions where the student_id is in the students table
        */
        $getSessions = ExamSession::with('exam', 'student')->whereIn('student_id', $collect_student_ids)->get();

        // dd($getSessions);


        return view('livewire.extra-time-module', [
            'students' => $students,
            'examSessions' => $getSessions
        ]);
    }
}
