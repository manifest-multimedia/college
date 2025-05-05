<?php

namespace App\Livewire\Finance;

use App\Models\Student;
use App\Models\ExamType;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\ExamClearance;
use App\Services\ExamClearanceManager as ExamClearanceService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExamClearanceManager extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $academicYearId;
    public $semesterId;
    public $examTypeId;
    public $searchTerm = '';
    
    public $selectedStudentId;
    public $manualOverride = false;
    public $overrideReason;
    
    public $showClearanceModal = false;
    public $showOverrideModal = false;
    
    protected $rules = [
        'academicYearId' => 'required',
        'semesterId' => 'required',
        'examTypeId' => 'required',
        'overrideReason' => 'required_if:manualOverride,true'
    ];
    
    public function mount()
    {
        // Set default values to the most recent academic year and semester
        $this->academicYearId = AcademicYear::orderBy('year', 'desc')->first()?->id;
        $this->semesterId = Semester::orderBy('id', 'desc')->first()?->id;
        $this->examTypeId = ExamType::first()?->id;
    }
    
    public function updatedSearchTerm()
    {
        $this->resetPage();
    }
    
    public function openClearanceModal($studentId)
    {
        Log::info('openClearanceModal called', [
            'studentId' => $studentId,
            'examTypeId' => $this->examTypeId
        ]);
        
        // Check if examTypeId is set, if not get the first active exam type
        if (!$this->examTypeId) {
            $this->examTypeId = ExamType::where('is_active', true)->first()?->id;
            Log::info('Setting default examTypeId', ['examTypeId' => $this->examTypeId]);
        }
        
        $this->selectedStudentId = $studentId;
        $this->manualOverride = false;
        $this->overrideReason = '';
        $this->showClearanceModal = true;
        
        $this->dispatch('show-clearance-modal');
        
        Log::info('Dispatched show-clearance-modal event');
    }
    
    public function openOverrideModal($studentId)
    {
        Log::info('openOverrideModal called', [
            'studentId' => $studentId,
            'examTypeId' => $this->examTypeId
        ]);
        
        // Check if examTypeId is set, if not get the first active exam type
        if (!$this->examTypeId) {
            $this->examTypeId = ExamType::where('is_active', true)->first()?->id;
            Log::info('Setting default examTypeId', ['examTypeId' => $this->examTypeId]);
        }
        
        $this->selectedStudentId = $studentId;
        $this->manualOverride = true;
        $this->overrideReason = '';
        $this->showOverrideModal = true;
        
        $this->dispatch('show-override-modal');
        
        Log::info('Dispatched show-override-modal event');
    }
    
    public function clearStudent()
    {
        Log::info('clearStudent method entered', [
            'studentId' => $this->selectedStudentId,
            'academicYearId' => $this->academicYearId,
            'semesterId' => $this->semesterId,
            'examTypeId' => $this->examTypeId,
            'manualOverride' => $this->manualOverride
        ]);
        
        // Add additional debug info
        Log::info('Current component state', [
            'showClearanceModal' => $this->showClearanceModal,
            'showOverrideModal' => $this->showOverrideModal
        ]);
        
        try {
            // Validate required fields
            $this->validate([
                'academicYearId' => 'required',
                'semesterId' => 'required',
                'examTypeId' => 'required',
            ]);
            
            // Validate override reason if manual override
            if ($this->manualOverride) {
                Log::info('Validating override reason');
                $this->validate([
                    'overrideReason' => 'required|min:10'
                ]);
                Log::info('Manual override validated successfully', [
                    'overrideReason' => $this->overrideReason
                ]);
            }
            
            // Find student
            Log::info('Finding student with ID: ' . $this->selectedStudentId);
            $student = Student::findOrFail($this->selectedStudentId);
            Log::info('Student found', [
                'studentId' => $student->id,
                'studentName' => $student->first_name . ' ' . $student->last_name
            ]);
            
            // Initialize clearance manager
            $clearanceManager = new ExamClearanceService();
            
            Log::info('About to process clearance', [
                'student' => $student->id,
                'academicYearId' => $this->academicYearId, 
                'semesterId' => $this->semesterId,
                'examTypeId' => $this->examTypeId
            ]);
            
            // Process the clearance
            $clearance = $clearanceManager->processClearance(
                $student, 
                (int) $this->academicYearId, // Ensure integers are passed
                (int) $this->semesterId,
                (int) $this->examTypeId,
                $this->manualOverride,
                $this->overrideReason,
                Auth::id()
            );
            
            Log::info('Clearance processed successfully', [
                'clearanceId' => $clearance->id,
                'clearanceCode' => $clearance->clearance_code,
                'isCleared' => $clearance->is_cleared
            ]);
            
            // Reset modal states
            $this->showClearanceModal = false;
            $this->showOverrideModal = false;
            
            // Close modals using dispatch for Laravel 12
            $this->dispatch('close-modal');
            
            session()->flash('message', 'Student has been cleared for the exam.');
            Log::info('Method complete - success message flashed');
            
        } catch (\Exception $e) {
            Log::error('Failed to clear student', [
                'studentId' => $this->selectedStudentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            session()->flash('error', 'Failed to clear student: ' . $e->getMessage());
        }
    }
    
    public function revokeClearance($clearanceId)
    {
        $clearance = ExamClearance::findOrFail($clearanceId);
        $clearanceManager = new ExamClearanceService();
        
        try {
            $clearanceManager->revokeClearance($clearance);
            session()->flash('message', 'Student clearance has been revoked.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to revoke clearance: ' . $e->getMessage());
        }
    }
    
    public function getStudentsProperty()
    {
        return Student::query()
            ->when($this->searchTerm, function ($query) {
                return $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%')
                      ->orWhere('student_id', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->with(['examClearances' => function ($query) {
                $query->where('academic_year_id', $this->academicYearId)
                      ->where('semester_id', $this->semesterId)
                      ->where('exam_type_id', $this->examTypeId);
            }, 'feeBills' => function ($query) {
                $query->where('academic_year_id', $this->academicYearId)
                      ->where('semester_id', $this->semesterId);
            }])
            ->paginate(10);
    }
    
    public function getAcademicYearsProperty()
    {
        return AcademicYear::orderBy('year', 'desc')->get();
    }
    
    public function getSemestersProperty()
    {
        return Semester::all();
    }
    
    public function getExamTypesProperty()
    {
        return ExamType::where('is_active', true)->get();
    }
    
    public function getClearedStudentsProperty()
    {
        // Only attempt to get cleared students if all required IDs are valid
        if ($this->academicYearId && $this->semesterId && $this->examTypeId) {
            $clearanceManager = new ExamClearanceService();
            return $clearanceManager->getClearedStudents(
                (int)$this->academicYearId, 
                (int)$this->semesterId, 
                (int)$this->examTypeId
            );
        }
        
        // Return an empty collection if any required ID is missing
        return collect();
    }
    
    public function render()
    {
        return view('livewire.finance.exam-clearance-manager', [
            'students' => $this->students,
            'academicYears' => $this->academicYears,
            'semesters' => $this->semesters,
            'examTypes' => $this->examTypes,
            'clearedStudents' => $this->clearedStudents
        ]);
    }
}