<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\User;
use App\Models\ExamSession;
use App\Models\Exam;

class DataMismatch extends Component
{
    use WithPagination;

    public $mode = 'index'; // Modes: index, view, edit
    public $selected_student_id;
    public $selected_user_id;
    public $selected_exam_session_id;

    public $students; // Holds filtered student list
    public $user;     // Holds user details for editing
    public $student;  // Holds student details for editing
    public $examSession; // Holds ExamSession details

    // Filters
    public $filter_student_id;
    public $filter_email;
    public $filter_exam_id;

    public function render()
    {
        // Apply filters for students
        $this->students = Student::query()
            ->when($this->filter_student_id, fn($q) => $q->where('student_id', 'like', '%' . $this->filter_student_id . '%'))
            ->paginate(15);

        return view('livewire.data-mismatch', [
            'students' => $this->students,
        ]);
    }

    public function viewDetails($studentId)
    {
        $this->student = Student::find($studentId);
        $this->user = User::where('email', $this->student->email)->first();
        $this->examSession = ExamSession::where('student_id', $studentId)->first();
        $this->mode = 'view';
    }

    public function editDetails($type)
    {
        // Switch mode to edit
        $this->mode = "edit-{$type}";
    }

    public function updateStudent()
    {
        $this->student->save();
        $this->mode = 'view';
        session()->flash('message', 'Student updated successfully.');
    }

    public function updateUser()
    {
        $this->user->save();
        $this->mode = 'view';
        session()->flash('message', 'User updated successfully.');
    }

    public function updateExamSession()
    {
        $this->examSession->save();
        $this->mode = 'view';
        session()->flash('message', 'ExamSession updated successfully.');
    }
}
