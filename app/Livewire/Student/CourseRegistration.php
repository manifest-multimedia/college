<?php

namespace App\Livewire\Student;

use App\Models\AcademicYear;
use App\Models\CourseRegistration as CourseRegistrationModel;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CourseRegistrationForm extends Component
{
    public $student;

    public $currentAcademicYear;

    public $currentSemester;

    public $selectedSubjects = [];

    public $registrationAllowed = false;

    public $registrationMessage = '';

    public $registrationMessageType = 'info';

    // Payment threshold for course registration (60%)
    const PAYMENT_THRESHOLD = 60;

    protected $rules = [
        'selectedSubjects' => 'required|array|min:1',
        'selectedSubjects.*' => 'exists:subjects,id',
    ];

    protected $messages = [
        'selectedSubjects.required' => 'Please select at least one course to register.',
        'selectedSubjects.min' => 'Please select at least one course to register.',
        'selectedSubjects.*.exists' => 'One or more selected courses are invalid.',
    ];

    public function mount()
    {
        // Get the student associated with the current authenticated user
        $this->student = Student::where('user_id', Auth::id())->first();

        if (! $this->student) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'Student profile not found. Please contact the administrator.';
            $this->registrationMessageType = 'danger';

            return;
        }

        // Get current academic year and semester
        $this->currentAcademicYear = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('name', 'desc')->first();

        $this->currentSemester = Semester::where('is_current', true)->first()
            ?? Semester::orderBy('id', 'desc')->first();

        if (! $this->currentAcademicYear || ! $this->currentSemester) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'No active academic year or semester found. Please contact the administrator.';
            $this->registrationMessageType = 'danger';

            return;
        }

        $this->checkEligibilityAndLoadExistingRegistrations();
    }

    protected function checkEligibilityAndLoadExistingRegistrations()
    {
        // Check if student has paid at least 60% of fees
        $feeBill = StudentFeeBill::where('student_id', $this->student->id)
            ->where('academic_year_id', $this->currentAcademicYear->id)
            ->where('semester_id', $this->currentSemester->id)
            ->first();

        if (! $feeBill) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'No fee bill found for the current semester. Please contact the Finance Department to generate your bill.';
            $this->registrationMessageType = 'warning';

            return;
        }

        $paymentPercentage = $feeBill->payment_percentage;

        if ($paymentPercentage >= self::PAYMENT_THRESHOLD) {
            $this->registrationAllowed = true;
            $this->registrationMessage = 'You have paid '.number_format($paymentPercentage, 1).'% of your fees and are eligible for course registration.';
            $this->registrationMessageType = 'success';
        } else {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'You have only paid '.number_format($paymentPercentage, 1).'% of your fees. At least '.self::PAYMENT_THRESHOLD.'% payment is required for course registration. Please visit the Finance Department to make payment.';
            $this->registrationMessageType = 'danger';

            return;
        }

        // Load existing registrations for this semester
        $existingRegistrations = CourseRegistrationModel::where('student_id', $this->student->id)
            ->where('academic_year_id', $this->currentAcademicYear->id)
            ->where('semester_id', $this->currentSemester->id)
            ->pluck('subject_id')
            ->toArray();

        $this->selectedSubjects = $existingRegistrations;
    }

    public function submitRegistration()
    {
        if (! $this->registrationAllowed) {
            session()->flash('error', 'Course registration is not allowed due to insufficient fee payment.');

            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                // Delete previous registrations for this student/semester
                CourseRegistrationModel::where('student_id', $this->student->id)
                    ->where('academic_year_id', $this->currentAcademicYear->id)
                    ->where('semester_id', $this->currentSemester->id)
                    ->delete();

                // Get fee bill for payment percentage
                $feeBill = StudentFeeBill::where('student_id', $this->student->id)
                    ->where('academic_year_id', $this->currentAcademicYear->id)
                    ->where('semester_id', $this->currentSemester->id)
                    ->first();

                $paymentPercentage = $feeBill ? $feeBill->payment_percentage : 0;

                // Create new registrations
                foreach ($this->selectedSubjects as $subjectId) {
                    CourseRegistrationModel::create([
                        'student_id' => $this->student->id,
                        'subject_id' => $subjectId,
                        'academic_year_id' => $this->currentAcademicYear->id,
                        'semester_id' => $this->currentSemester->id,
                        'registered_at' => now(),
                        'payment_percentage_at_registration' => $paymentPercentage,
                        'is_approved' => false, // Require Finance Officer approval
                    ]);
                }
            });

            session()->flash('success', 'Course registration submitted successfully for '.count($this->selectedSubjects).' courses. Your registration is pending approval from the Finance Department.');

            // Refresh the component to show updated data
            $this->checkEligibilityAndLoadExistingRegistrations();

        } catch (\Exception $e) {
            session()->flash('error', 'Error submitting course registration: '.$e->getMessage());
        }
    }

    public function getAvailableSubjectsProperty()
    {
        if (! $this->student || ! $this->currentSemester) {
            return collect();
        }

        // Get subjects available for this student's class and current semester
        return Subject::where('semester_id', $this->currentSemester->id)
            ->where(function ($query) {
                $query->where('college_class_id', $this->student->college_class_id)
                    ->orWhereNull('college_class_id'); // Subjects available to all classes
            })
            ->orderBy('course_code')
            ->get();
    }

    public function getCurrentRegistrationsProperty()
    {
        if (! $this->student || ! $this->currentAcademicYear || ! $this->currentSemester) {
            return collect();
        }

        return CourseRegistrationModel::where('student_id', $this->student->id)
            ->where('academic_year_id', $this->currentAcademicYear->id)
            ->where('semester_id', $this->currentSemester->id)
            ->with(['subject', 'approvedBy', 'rejectedBy'])
            ->orderBy('registered_at', 'desc')
            ->get();
    }

    public function getTotalCreditsProperty()
    {
        if (empty($this->selectedSubjects)) {
            return 0;
        }

        return Subject::whereIn('id', $this->selectedSubjects)
            ->sum('credit_hours');
    }

    public function render()
    {
        return view('livewire.student.course-registration', [
            'availableSubjects' => $this->availableSubjects,
            'currentRegistrations' => $this->currentRegistrations,
            'totalCredits' => $this->totalCredits,
        ]);
    }
}
