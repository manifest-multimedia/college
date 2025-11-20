<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\CourseRegistration as StudentCourseRegistrationModel;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StudentCourseRegistration extends Component
{
    public $student;

    public $currentAcademicYear;

    public $currentSemester;

    public $availableSubjects = [];

    public $selectedSubjects = [];

    public $registrationAllowed = false;

    public $registrationMessage = '';

    public $registrationMessageType = 'danger';

    public $paymentPercentage = 0;

    public Collection $existingRegistrations;

    // Payment threshold for course registration (60%)
    const PAYMENT_THRESHOLD = 60;

    public function mount()
    {
        $this->existingRegistrations = new Collection;
        // Get the logged-in user's student record
        $user = Auth::user();
        $this->student = Student::where('user_id', $user->id)->first();

        if (! $this->student) {
            session()->flash('error', 'No student record found for your account. Please contact the administration.');

            return;
        }

        // Get current academic year and semester
        $this->currentAcademicYear = AcademicYear::where('is_current', true)->first();
        $this->currentSemester = Semester::where('is_current', true)->first();

        if (! $this->currentAcademicYear || ! $this->currentSemester) {
            session()->flash('error', 'No active academic year or semester found. Please contact the administration.');

            return;
        }

        $this->checkPaymentStatus();
        $this->loadAvailableSubjects();
        $this->loadExistingRegistrations();
    }

    public function checkPaymentStatus()
    {
        // Get the student's fee bill for current academic year and semester
        $feeBill = StudentFeeBill::where('student_id', $this->student->id)
            ->where('academic_year_id', $this->currentAcademicYear->id)
            ->where('semester_id', $this->currentSemester->id)
            ->first();

        if (! $feeBill) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'No fee bill found for the current semester. Please contact the Finance Department.';
            $this->registrationMessageType = 'warning';

            return;
        }

        $this->paymentPercentage = $feeBill->payment_percentage ?? 0;

        if ($this->paymentPercentage >= self::PAYMENT_THRESHOLD) {
            $this->registrationAllowed = true;
            $this->registrationMessage = 'You are eligible for course registration ('.number_format($this->paymentPercentage, 1).'% fees paid).';
            $this->registrationMessageType = 'success';
        } else {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'You need to pay at least '.self::PAYMENT_THRESHOLD.'% of your fees to register for courses. Current payment: '.number_format($this->paymentPercentage, 1).'%. Please contact the Finance Department.';
            $this->registrationMessageType = 'warning';
        }
    }

    public function loadAvailableSubjects()
    {
        if (! $this->student || ! $this->currentAcademicYear || ! $this->currentSemester) {
            return;
        }

        // Load subjects based on student's class and current semester
        $this->availableSubjects = Subject::where('semester_id', $this->currentSemester->id)
            ->where('college_class_id', $this->student->college_class_id)
            ->orderBy('name')
            ->get();
    }

    public function loadExistingRegistrations()
    {
        if (! $this->student || ! $this->currentAcademicYear || ! $this->currentSemester) {
            return;
        }

        $this->existingRegistrations = StudentCourseRegistrationModel::where('student_id', $this->student->id)
            ->where('academic_year_id', $this->currentAcademicYear->id)
            ->where('semester_id', $this->currentSemester->id)
            ->with('subject')
            ->get();

        // Pre-select already registered subjects
        $this->selectedSubjects = $this->existingRegistrations->pluck('subject_id')->map(function ($id) {
            return (int) $id;
        })->toArray();
    }

    public function toggleSubject($subjectId)
    {
        if (in_array($subjectId, $this->selectedSubjects)) {
            $this->selectedSubjects = array_diff($this->selectedSubjects, [$subjectId]);
        } else {
            $this->selectedSubjects[] = $subjectId;
        }
    }

    public function submitRegistration()
    {
        if (! $this->registrationAllowed) {
            session()->flash('error', 'Course registration is not allowed due to insufficient fee payment.');

            return;
        }

        if (empty($this->selectedSubjects)) {
            session()->flash('error', 'Please select at least one subject to register for.');

            return;
        }

        try {
            DB::transaction(function () {
                // Delete existing registrations for this student/semester
                StudentCourseRegistrationModel::where('student_id', $this->student->id)
                    ->where('academic_year_id', $this->currentAcademicYear->id)
                    ->where('semester_id', $this->currentSemester->id)
                    ->delete();

                // Create new registrations
                foreach ($this->selectedSubjects as $subjectId) {
                    StudentCourseRegistrationModel::create([
                        'student_id' => $this->student->id,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $this->currentAcademicYear->id,
                        'semester_id' => $this->currentSemester->id,
                        'registered_at' => now(),
                        'payment_percentage_at_registration' => $this->paymentPercentage,
                        'is_approved' => false, // Requires Finance Officer approval
                    ]);
                }
            });

            // Reload existing registrations
            $this->loadExistingRegistrations();

            session()->flash('success', 'Course registration submitted successfully for '.count($this->selectedSubjects).' subjects. Your registration is pending approval from the Finance Department.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error submitting course registration: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.student-course-registration', [
            'student' => $this->student,
            'currentAcademicYear' => $this->currentAcademicYear,
            'currentSemester' => $this->currentSemester,
            'availableSubjects' => $this->availableSubjects,
            'existingRegistrations' => $this->existingRegistrations,
        ]);
    }
}
