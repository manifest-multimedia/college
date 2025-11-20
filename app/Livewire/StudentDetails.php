<?php

namespace App\Livewire;

use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class StudentDetails extends Component
{
    public $studentId;

    public $student;

    public $loading = true;

    public function mount($studentId)
    {
        $this->studentId = $studentId;
        $this->loadStudent();
    }

    public function loadStudent()
    {
        try {
            $this->student = Student::with(['CollegeClass', 'Cohort', 'User.roles', 'examSessions.exam.course'])
                ->find($this->studentId);

            if (! $this->student) {
                session()->flash('error', 'Student not found.');
            }

            $this->loading = false;
        } catch (\Exception $e) {
            Log::error('Error loading student: '.$e->getMessage());
            session()->flash('error', 'Failed to load student information.');
            $this->loading = false;
        }
    }

    public function deleteExamSession($sessionId)
    {
        if (! auth()->user()->hasRole(['Super Admin', 'System User'])) {
            session()->flash('error', 'You do not have permission to delete exam sessions.');

            return;
        }

        try {
            $session = \App\Models\ExamSession::find($sessionId);
            if ($session) {
                $session->delete();
                session()->flash('success', 'Exam session deleted successfully.');
                $this->loadStudent(); // Reload to update the list
            } else {
                session()->flash('error', 'Exam session not found.');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting exam session: '.$e->getMessage());
            session()->flash('error', 'Failed to delete exam session.');
        }
    }

    public function render()
    {
        return view('livewire.student-details');
    }
}
