<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\CourseRegistration as StudentCourseRegistrationModel;
use App\Models\Semester;
use App\Services\StudentAcademicProfileService;
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

    /** Balance display: 'credit' | 'debit' | 'zero' */
    public $balanceDisplayType = 'zero';

    public $balanceDisplayAmount = 0.0;

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
        $this->currentAcademicYear = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('start_date', 'desc')->first();
        $this->currentSemester = Semester::where('is_current', true)->first()
            ?? ($this->currentAcademicYear ? Semester::where('academic_year_id', $this->currentAcademicYear->id)->orderBy('sequence')->first() : null)
            ?? Semester::orderBy('start_date', 'desc')->first();

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
        $feeBill = StudentFeeBill::active()->where('student_id', $this->student->id)
            ->where('academic_year_id', $this->currentAcademicYear->id)
            ->where('semester_id', $this->currentSemester->id)
            ->first();

        if (! $feeBill) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'No fee bill found for the current semester. Please contact the Finance Department.';
            $this->registrationMessageType = 'warning';

            return;
        }

        $this->paymentPercentage = $feeBill->display_payment_percentage;
        $this->balanceDisplayType = $feeBill->balance_display_type;
        $this->balanceDisplayAmount = $feeBill->balance_display_amount;

        if ($feeBill->payment_percentage >= self::PAYMENT_THRESHOLD) {
            $this->registrationAllowed = true;
            $creditPart = $feeBill->balance_display_type === 'credit'
                ? ' — credit (GH₵'.number_format($feeBill->balance_display_amount, 2).')'
                : '';
            $this->registrationMessage = 'You are eligible for course registration ('.number_format($this->paymentPercentage, 1).'% fees paid)'.$creditPart.'.';
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
        $profileService = app(StudentAcademicProfileService::class);

        $profile = $profileService->getProfile($this->student);

        // Resolve candidate semester IDs corresponding to the current semester's term/position.
        // Handles cases where catalog courses hold a semester_id from another academic year or initial import.
        $targetSequence = $this->currentSemester->sequence;
        $targetName = strtolower(trim((string) $this->currentSemester->name));

        $matchingSemesterIds = Semester::query()
            ->where('id', $this->currentSemester->id)
            ->when($targetSequence, function ($q) use ($targetSequence) {
                $q->orWhere('sequence', $targetSequence);
            })
            ->when($targetName, function ($q) use ($targetName) {
                $q->orWhereRaw('LOWER(name) = ?', [$targetName]);
                if (str_contains($targetName, '1') || str_contains($targetName, 'first')) {
                    $q->orWhereRaw('LOWER(name) LIKE ?', ['%1%'])
                      ->orWhereRaw('LOWER(name) LIKE ?', ['%first%']);
                } elseif (str_contains($targetName, '2') || str_contains($targetName, 'second')) {
                    $q->orWhereRaw('LOWER(name) LIKE ?', ['%2%'])
                      ->orWhereRaw('LOWER(name) LIKE ?', ['%second%']);
                }
            })
            ->pluck('id')
            ->toArray();

        if (empty($matchingSemesterIds)) {
            $matchingSemesterIds = [$this->currentSemester->id];
        }

        $query = Subject::whereIn('semester_id', $matchingSemesterIds)
            ->where(function ($q) {
                $q->where('college_class_id', $this->student->college_class_id)
                  ->orWhereNull('college_class_id');
            });

        if ($profile->yearOfStudy) {
            $query->where(function ($q) use ($profile) {
                $q->where('year_id', $profile->yearOfStudy->id)
                  ->orWhereNull('year_id');
            });
        }

        $this->availableSubjects = $query
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
