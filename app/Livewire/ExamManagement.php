<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;
use App\Models\CollegeClass;
use App\Models\Year;
use App\Models\Semester;

class ExamManagement extends Component
{
    public $course_code;
    public $exam_type = 'mcq'; // Default to 'mcq'
    public $exam_duration;
    public $exam_password;
    public $semester;
    public $class;
    public $year;
    public $slug;


    // Validation rules for form input
    protected $rules = [
        'course_code' => 'required|string|max:255',
        'exam_type' => 'required|in:mcq,short_answer,essay', // Allow specific exam types
        'exam_duration' => 'required|integer|min:1', // Exam duration must be at least 1 minute
        'exam_password' => 'nullable|string', // The password will be generated in the component
    ];

    protected $messages = [

        'course_code.required' => 'Please select a course.',
        'exam_type.required' => 'Please select an exam type.',
        'exam_duration.required' => 'Please enter an exam duration.',
        'exam_duration.min' => 'Exam duration must be at least 1 minute.',

    ];

    public function mount()
    {
        // Automatically generate an exam password
        $this->exam_password = $this->regeneratePassword();
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
            'duration' => $this->exam_duration,
            'password' => $this->exam_password,
            'slug' => $this->generateSlug(),
        ]);

        $this->reset(['course_code', 'exam_type', 'exam_duration', 'exam_password']);

        $this->regeneratePassword();

        // Flash success message and redirect
        session()->flash('message', 'Exam created successfully!');
        return redirect()->route('examcenter'); // Redirect to the list of exams
    }

    public function render()
    {
        // Load courses in ascending order
        $courses = Subject::when(
            $this->class,
            function ($query) {
                return $query->where('college_class_id', $this->class);
            }
        )->when(
            $this->year,
            function ($query) {
                return $query->where('year_id', $this->year);
            }
        )->when(
            $this->semester,
            function ($query) {
                return $query->where('semester_id', $this->semester);
            }
        )->orderBy('name', 'asc')->get();
        $classes = CollegeClass::all();
        $years = Year::all();
        $semesters = Semester::all();

        return view('livewire.exam-management', [
            'courses' => $courses,
            'classes' => $classes,
            'years' => $years,
            'semesters' => $semesters,

        ]);
    }

    public function regeneratePassword()
    {
        $password = Str::random(8);

        while (Exam::where('password', $password)->exists()) {
            $password = Str::random(8);
        }

        $this->exam_password = $password;
    }

    public function generateSlug()
    {
        // Generate Unique Slug from Course Name, Date and Time
        $slug  = Str::slug($this->course_code . '-' . now()->format('Y-m-d H:i:s'));
        while (Exam::where('slug', $slug)->exists()) {
            $slug = Str::slug($this->course_code . '-' . now()->format('Y-m-d H:i:s'));
        }
        return $slug;
    }
}
