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
            $this->student = Student::with(['CollegeClass', 'Cohort', 'User.roles'])
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

    public function render()
    {
        return view('livewire.student-details');
    }
}
