<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use App\Models\User;
use Livewire\Component;

class AdminExamModule extends Component
{
    public $selected_student = null;

    public $selected_exam = null;

    public $selected_session = null;

    public function render()
    {
        $responses = ExamSession::with(['responses.question', 'exam', 'student'])
            ->when($this->selected_student, function ($query) {
                return $query->where('student_id', $this->selected_student);
            })
            ->when($this->selected_exam, function ($query) {
                return $query->where('exam_id', $this->selected_exam);
            })
            ->when($this->selected_session, function ($query) {
                return $query->where('id', $this->selected_session);
            })
            ->get()
            ->flatMap(function ($session) {
                return $session->responses; // Flatten responses for easier display
            });

        $users = User::all();
        $exams = Exam::all();
        $examSessions = ExamSession::all();
        $students = Student::all();

        return view('livewire.admin-exam-module', [
            'exams' => $exams,
            'examSessions' => $examSessions,
            'students' => $students,
            'users' => $users,
            'responses' => $responses,
        ]);
    }
}
