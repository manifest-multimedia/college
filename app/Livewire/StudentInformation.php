<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\Student;
use App\Services\StudentAcademicProfileService;
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
        $academicProfile = null;
        $yearEnrolled = null;

        if ($this->student) {
            $courseRegistrations = CourseRegistration::where('student_id', $this->student->id)
                ->with(['subject', 'academicYear', 'semester'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Compute profile only for the view (not stored as Livewire state - unsupported for serialization)
            $academicProfile = app(StudentAcademicProfileService::class)->getProfile($this->student);

            // Year Enrolled: use academic_years.year column. Resolve from cohort batch year, then profile, then created_at year.
            $yearEnrolled = $this->resolveYearEnrolledFromAcademicYear($academicProfile);
        }

        return view('livewire.student-information', [
            'courseRegistrations' => $courseRegistrations,
            'academicProfile' => $academicProfile,
            'yearEnrolled' => $yearEnrolled,
        ]);
    }

    /**
     * Resolve "Year Enrolled" from the academic_years table's name/year columns.
     */
    protected function resolveYearEnrolledFromAcademicYear(?\App\Services\StudentAcademicProfile $profile = null): ?string
    {
        $student = $this->student;
        if (! $student) {
            return null;
        }

        // 1) Cohort batch year (e.g. "Batch 2024") -> find academic_years row where year = '2024', display its name (e.g. "2024-2025")
        if ($student->cohort && preg_match('/\d{4}/', $student->cohort->name ?? '', $m)) {
            $batchYear = $m[0];
            $academicYear = AcademicYear::where('year', $batchYear)
                ->orderBy('start_date')
                ->first();
            if ($academicYear) {
                return $academicYear->name ?? $academicYear->year;
            }
        }

        // 2) Start academic year from profile (from academic_years table)
        if ($profile?->startAcademicYear) {
            return $profile->startAcademicYear->name ?? $profile->startAcademicYear->year;
        }

        // 3) Fallback: created_at year (last resort, still just a year)
        return $student->created_at?->format('Y');
    }
}
