<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ExamCenterWidget extends Component
{
    public $course_code;
    public $exam_type = 'mcq'; // Default to 'mcq'
    public $exam_duration;
    public $exam_password;

    // Validation rules for form input
    protected $rules = [
        'course_code' => 'required|string|max:255',
        'exam_type' => 'required|in:mcq,short_answer,essay', // Allow specific exam types
        'exam_duration' => 'required|integer|min:1', // Exam duration must be at least 1 minute
        'exam_password' => 'nullable|string', // The password will be generated in the component
    ];

    public function mount()
    {
        // Automatically generate an exam password
        $this->exam_password = Str::random(8); // 8-character random password
    }

    // Handle form submission to create the exam
    public function createExam()
    {
        $this->validate(); // Validate the form input

        // Create a new exam in the database
        Exam::create([
            'course_id' => $this->course_code,
            'user_id' => Auth::user()->id,
            'exam_type' => $this->exam_type,
            'exam_duration' => $this->exam_duration,
            'exam_password' => $this->exam_password,
        ]);

        // Flash success message and redirect
        session()->flash('message', 'Exam created successfully!');
        return redirect()->route('exams.index'); // Redirect to the list of exams
    }

    public function render()
    {
        return view('livewire.exam-center-widget');
    }
}
