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
use Carbon\Carbon;

class ExamClearanceManager extends Component
{
    use WithPagination;

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
        $this->selectedStudentId = $studentId;
        $this->manualOverride = false;
        $this->overrideReason = '';
        $this->showClearanceModal = true;
    }
    
    public function openOverrideModal($studentId)
    {
        $this->selectedStudentId = $studentId;
        $this->manualOverride = true;
        $this->overrideReason = '';
        $this->showOverrideModal = true;
    }
    
    public function clearStudent()
    {
        if ($this->manualOverride) {
            $this->validate([
                'overrideReason' => 'required|min:10'
            ]);
        }
        
        $student = Student::findOrFail($this->selectedStudentId);
        $clearanceManager = new ExamClearanceService();
        
        try {
            $clearance = $clearanceManager->processClearance(
                $student, 
                $this->academicYearId,
                $this->semesterId,
                $this->examTypeId,
                $this->manualOverride,
                $this->overrideReason,
                Auth::id()
            );
            
            session()->flash('message', 'Student has been cleared for the exam.');
            $this->showClearanceModal = false;
            $this->showOverrideModal = false;
        } catch (\Exception $e) {
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
        $clearanceManager = new ExamClearanceService();
        return $clearanceManager->getClearedStudents($this->academicYearId, $this->semesterId, $this->examTypeId);
    }
    
    public function render()
    {
        return view('livewire.finance.exam-clearance-manager', [
            'students' => $this->students,
            'academicYears' => $this->academicYears,
            'semesters' => $this->semesters,
            'examTypes' => $this->examTypes,
            'clearedStudents' => $this->clearedStudents
        ])
        ->extends('components.dashboard.default')
        ->section('content');
    }
}