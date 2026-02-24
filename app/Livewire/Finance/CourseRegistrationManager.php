<?php

namespace App\Livewire\Finance;

use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\Subject;
use App\Models\Year;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CourseRegistrationManager extends Component
{
    use WithPagination;

    public $studentId;

    public $academicYearId;

    public $semesterId;

    /** Year of study (e.g. Year 1, Year 2) – filters available subjects to that level only */
    public $yearId;

    public $selectedCourses = [];

    public $student;

    public $registrationMessage;

    public $registrationAllowed = false;

    public $registrationMessageType = 'danger';

    // Payment threshold for course registration (60%)
    const PAYMENT_THRESHOLD = 60;

    protected $rules = [
        'studentId' => 'required|exists:students,id',
        'academicYearId' => 'required|exists:academic_years,id',
        'semesterId' => 'required|exists:semesters,id',
        'yearId' => 'nullable|exists:years,id',
        'selectedCourses' => 'required|array|min:1',
    ];

    public function mount($studentId = null)
    {
        // Get student ID either from parameter, or try to find from authenticated user
        if (! $studentId && Auth::check()) {
            // Find student associated with current user, if any
            $student = Student::where('user_id', Auth::id())->first();
            if ($student) {
                $studentId = $student->id;
            }
        }

        $this->studentId = $studentId;

        // Set defaults for academic year and semester
        $this->academicYearId = AcademicYear::orderBy('year', 'desc')->first()?->id;
        $this->semesterId = Semester::where('is_current', true)->first()?->id ??
                           Semester::orderBy('id', 'desc')->first()?->id;

        if ($this->studentId) {
            $this->loadStudent();
        }
    }

    public function updatedStudentId()
    {
        $this->loadStudent();
    }

    public function updatedAcademicYearId()
    {
        $this->loadStudent();
    }

    public function updatedSemesterId()
    {
        $this->loadStudent();
    }

    public function updatedYearId()
    {
        // Re-run so available courses refresh
    }

    protected function loadStudent()
    {
        if (! $this->studentId || ! $this->academicYearId || ! $this->semesterId) {
            return;
        }

        $this->student = Student::findOrFail($this->studentId);

        // Check if student has paid at least 60% of fees
        $feeBill = StudentFeeBill::where('student_id', $this->studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->first();

        if (! $feeBill) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'No fee bill found for this semester. Please contact the Finance Department.';
            $this->registrationMessageType = 'warning';

            return;
        }

        $paymentPercentage = $feeBill->payment_percentage;

        if ($paymentPercentage >= self::PAYMENT_THRESHOLD) {
            $this->registrationAllowed = true;
            $this->registrationMessage = 'Student has paid '.number_format($paymentPercentage, 1).'% of fees and is eligible for course registration.';
            $this->registrationMessageType = 'success';
        } else {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'Student has only paid '.number_format($paymentPercentage, 1).'% of fees. At least '.self::PAYMENT_THRESHOLD.'% payment is required for course registration. Please see the Finance Department.';
            $this->registrationMessageType = 'danger';
        }

        // Load previously selected courses
        $existingRegistrations = CourseRegistration::where('student_id', $this->studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->pluck('subject_id')
            ->toArray();

        $this->selectedCourses = $existingRegistrations;
    }

    public function registerCourses()
    {
        if (! $this->registrationAllowed) {
            session()->flash('error', 'Course registration is not allowed due to insufficient fee payment.');

            return;
        }

        $this->validate([
            'studentId' => 'required|exists:students,id',
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterId' => 'required|exists:semesters,id',
            'yearId' => 'required|exists:years,id',
            'selectedCourses' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                // Delete previous registrations for this student/semester
                CourseRegistration::where('student_id', $this->studentId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('semester_id', $this->semesterId)
                    ->delete();

                // Get fee bill for payment percentage
                $feeBill = StudentFeeBill::where('student_id', $this->studentId)
                    ->where('academic_year_id', $this->academicYearId)
                    ->where('semester_id', $this->semesterId)
                    ->first();

                $paymentPercentage = $feeBill ? $feeBill->payment_percentage : 0;

                // Create new registrations
                foreach ($this->selectedCourses as $subjectId) {
                    CourseRegistration::create([
                        'student_id' => $this->studentId,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                        'registered_at' => now(),
                        'payment_percentage_at_registration' => $paymentPercentage,
                        'is_approved' => false, // Require Finance Officer approval
                    ]);
                }
            });

            session()->flash('message', 'Course registration submitted successfully for '.count($this->selectedCourses).' courses. Your registration is pending approval from the Finance Department.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error registering courses: '.$e->getMessage());
        }
    }

    public function getRegisteredCoursesProperty()
    {
        if (! $this->studentId || ! $this->academicYearId || ! $this->semesterId) {
            return collect();
        }

        return CourseRegistration::where('student_id', $this->studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->with('subject')
            ->get();
    }

    public function getAvailableCoursesProperty()
    {
        if (! $this->student || ! $this->yearId) {
            return collect();
        }

        // Subjects are scoped by program (college_class), semester, and year of study
        return Subject::query()
            ->where('semester_id', $this->semesterId)
            ->where('year_id', $this->yearId)
            ->where('college_class_id', $this->student->college_class_id)
            ->orderBy('name')
            ->get();
    }

    public function getAcademicYearsProperty()
    {
        return AcademicYear::orderBy('year', 'desc')->get();
    }

    public function getSemestersProperty()
    {
        return Semester::all();
    }

    public function getYearsProperty()
    {
        return Year::orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.finance.course-registration-manager', [
            'availableCourses' => $this->availableCourses,
            'registeredCourses' => $this->registeredCourses,
            'academicYears' => $this->academicYears,
            'semesters' => $this->semesters,
            'years' => $this->years,
        ]);
    }
}
