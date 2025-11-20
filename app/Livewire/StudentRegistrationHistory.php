<?php

namespace App\Livewire;

use App\Models\CourseRegistration as StudentCourseRegistrationModel;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class StudentRegistrationHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $student;

    public $selectedAcademicYear = '';

    public $selectedSemester = '';

    public function mount()
    {
        // Get the logged-in user's student record
        $user = Auth::user();
        $this->student = Student::where('user_id', $user->id)->first();
    }

    public function updatingSelectedAcademicYear()
    {
        $this->resetPage();
    }

    public function updatingSelectedSemester()
    {
        $this->resetPage();
    }

    public function render()
    {
        $registrations = collect();
        $academicYears = collect();
        $semesters = collect();

        if ($this->student) {
            $query = StudentCourseRegistrationModel::where('student_id', $this->student->id)
                ->with(['subject', 'academicYear', 'semester', 'approvedBy', 'rejectedBy']);

            if ($this->selectedAcademicYear) {
                $query->where('academic_year_id', $this->selectedAcademicYear);
            }

            if ($this->selectedSemester) {
                $query->where('semester_id', $this->selectedSemester);
            }

            $registrations = $query->orderBy('registered_at', 'desc')->paginate(10);

            // Get unique academic years and semesters for filters
            $academicYears = StudentCourseRegistrationModel::where('student_id', $this->student->id)
                ->with('academicYear')
                ->get()
                ->pluck('academicYear')
                ->unique('id')
                ->sortByDesc('name');

            $semesters = StudentCourseRegistrationModel::where('student_id', $this->student->id)
                ->with('semester')
                ->get()
                ->pluck('semester')
                ->unique('id')
                ->sortBy('name');
        }

        return view('livewire.student-registration-history', [
            'registrations' => $registrations,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
        ]);
    }
}
