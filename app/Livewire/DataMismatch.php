<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\User;
use App\Models\ExamSession;
use App\Models\Exam;
use App\Models\CollegeClass;
use App\Models\Response;

class DataMismatch extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $mode = 'index'; // Modes: index, view, edit
    public $selected_student_id;
    public $selected_user_id;
    public $selected_exam_session_id;


    public $user;     // Holds user details for editing
    public $student;  // Holds student details for editing
    public $examSessions; // Holds ExamSession details

    public $examSession;

    // Filters
    public $filter_student_id;
    public $filter_email;

    public $filter_exam_id;
    public $filter_by_class;

    public function render()
    {
        // Apply filters for students
        $students = Student::query()
            ->when($this->filter_student_id, fn($q) => $q->where('student_id', 'like', '%' . $this->filter_student_id . '%'))
            ->when($this->filter_email, fn($q) => $q->where('email', 'like', '%' . $this->filter_email . '%'))
            ->when($this->filter_exam_id, fn($q) => $q->where('exam_id', 'like', '%' . $this->filter_exam_id . '%'))
            ->with('user')
            ->paginate(15);

        $exams = Exam::all();
        $classes = CollegeClass::all();

        return view('livewire.data-mismatch', [
            'students' => $students,
            'exams' => $exams,
            'classes' => $classes

        ]);
    }

    public function viewDetails($studentId)
    {
        $this->student = Student::find($studentId);
        $this->user = User::where('email', $this->student->email)->first();
        $this->examSessions = ExamSession::where('student_id', $this->user->ID)->get();
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
        dd('clicked');
        $this->examSession->save();
        $this->mode = 'view';
        session()->flash('message', 'ExamSession updated successfully.');
    }

    public function back()
    {

        $this->mode = 'index';
    }

    public function removeSession($sessionId)
    {
        $this->examSession = ExamSession::find($sessionId);
        $this->examSession->delete();
        session()->flash('message', 'ExamSession deleted successfully.');
    }


    public function downloadResults()
    {
        return response()->streamDownload(function () {
            // Fetch all students
            $students = Student::all();

            // Define the CSV header
            echo "Index,Date,Student ID,Name,Course,Score,Answered,Percentage\n";

            // Loop through each student
            foreach ($students as $index => $student) {
                // Fetch user details for the student
                $user = User::where('email', $student->email)->first();

                // Fetch exam sessions for the student
                $examSessions = ExamSession::where('student_id', optional($user)->id)
                    ->with('exam.course', 'responses')
                    ->get();

                // Loop through each exam session
                foreach ($examSessions as $examSession) {
                    // Extract relevant data
                    $courseName = optional($examSession->exam)->course->name ?? 'N/A';
                    $score = computeResults($examSession->id, 'score') ?? 0;
                    $answered = computeResults($examSession->id, 'total_answered');
                    $percentage = computeResults($examSession->id, 'percentage') ?? 0;

                    // Write the row to the CSV
                    echo "$index," .           // Index
                        "$examSession->created_at," .   // Date
                        "$student->student_id," .       // Student ID
                        "\"$student->first_name $student->last_name\"," . // Name (escaped quotes for names with commas)
                        "\"$courseName\"," .           // Course (escaped quotes for course names with commas)
                        "$score," .                   // Score
                        "$answered," .                // Answered
                        "$percentage\n";              // Percentage
                }
            }
        }, 'results.csv'); // Filename for the downloaded file
    }

    public function updated($property)
    {
        $this->resetPage();
    }
}
