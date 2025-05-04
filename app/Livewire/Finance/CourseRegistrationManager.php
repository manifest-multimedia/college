<?php

namespace App\Livewire\Finance;

use App\Models\Student;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\CourseRegistration;
use App\Models\StudentFeeBill;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseRegistrationManager extends Component
{
    use WithPagination;
    
    public $studentId;
    public $academicYearId;
    public $semesterId;
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
        'selectedCourses' => 'required|array|min:1'
    ];
    
    public function mount($studentId = null)
    {
        // Get student ID either from parameter, or try to find from authenticated user
        if (!$studentId && Auth::check()) {
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
    
    protected function loadStudent()
    {
        if (!$this->studentId || !$this->academicYearId || !$this->semesterId) {
            return;
        }
        
        $this->student = Student::findOrFail($this->studentId);
        
        // Check if student has paid at least 60% of fees
        $feeBill = StudentFeeBill::where('student_id', $this->studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->first();
        
        if (!$feeBill) {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'No fee bill found for this semester. Please contact the Finance Department.';
            $this->registrationMessageType = 'warning';
            return;
        }
        
        $paymentPercentage = $feeBill->payment_percentage;
        
        if ($paymentPercentage >= self::PAYMENT_THRESHOLD) {
            $this->registrationAllowed = true;
            $this->registrationMessage = 'Student has paid ' . number_format($paymentPercentage, 1) . '% of fees and is eligible for course registration.';
            $this->registrationMessageType = 'success';
        } else {
            $this->registrationAllowed = false;
            $this->registrationMessage = 'Student has only paid ' . number_format($paymentPercentage, 1) . '% of fees. At least ' . self::PAYMENT_THRESHOLD . '% payment is required for course registration. Please see the Finance Department.';
            $this->registrationMessageType = 'danger';
        }
        
        // Load previously selected courses
        $existingRegistrations = CourseRegistration::where('student_id', $this->studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->pluck('course_id')
            ->toArray();
            
        $this->selectedCourses = $existingRegistrations;
    }
    
    public function registerCourses()
    {
        if (!$this->registrationAllowed) {
            session()->flash('error', 'Course registration is not allowed due to insufficient fee payment.');
            return;
        }
        
        $this->validate();
        
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
                foreach ($this->selectedCourses as $courseId) {
                    CourseRegistration::create([
                        'student_id' => $this->studentId,
                        'course_id' => $courseId,
                        'academic_year_id' => $this->academicYearId,
                        'semester_id' => $this->semesterId,
                        'registration_date' => now(),
                        'payment_percentage_at_registration' => $paymentPercentage,
                        'registered_by' => Auth::id(),
                    ]);
                }
            });
            
            session()->flash('message', 'Course registration successful for ' . count($this->selectedCourses) . ' courses.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error registering courses: ' . $e->getMessage());
        }
    }
    
    public function getRegisteredCoursesProperty()
    {
        if (!$this->studentId || !$this->academicYearId || !$this->semesterId) {
            return collect();
        }
        
        return CourseRegistration::where('student_id', $this->studentId)
            ->where('academic_year_id', $this->academicYearId)
            ->where('semester_id', $this->semesterId)
            ->with('course')
            ->get();
    }
    
    public function getAvailableCoursesProperty()
    {
        if (!$this->student) {
            return collect();
        }
        
        // Get courses applicable to this student's program and class
        return Course::where('is_active', true)
            ->where(function($query) {
                $query->where('program_id', $this->student->program_id)
                    ->orWhereNull('program_id');
            })
            ->where(function($query) {
                $query->where('college_class_id', $this->student->college_class_id)
                    ->orWhereNull('college_class_id');
            })
            ->where('semester_id', $this->semesterId)
            ->orderBy('title')
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
    
    public function render()
    {
        return view('livewire.finance.course-registration-manager', [
            'availableCourses' => $this->availableCourses,
            'registeredCourses' => $this->registeredCourses,
            'academicYears' => $this->academicYears,
            'semesters' => $this->semesters,
        ]);
    }
}