<?php

namespace App\Livewire;

use App\Models\CourseRegistration;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StudentInformation extends Component
{
    public $student;

    public $isEditing = false;

    public function mount()
    {
        // Get the current authenticated user
        $user = Auth::user();

        // Find the student record associated with this user
        $this->student = Student::where('email', $user->email)->first();
    }

    public function toggleEdit()
    {
        $this->isEditing = ! $this->isEditing;
    }

    public function render()
    {
        $courseRegistrations = [];

        if ($this->student) {
            $courseRegistrations = CourseRegistration::where('student_id', $this->student->id)
                ->with(['subject', 'academicYear', 'semester'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('livewire.student-information', [
            'courseRegistrations' => $courseRegistrations,
        ]);
    }
}
