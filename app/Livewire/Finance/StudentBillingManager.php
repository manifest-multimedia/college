<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\CollegeClass;
use App\Services\StudentBillingManager as BillingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StudentBillingManager extends Component
{
    use WithPagination, WithFileUploads;
    
    // Filters
    public $search = '';
    public $academicYearId = '';
    public $semesterId = '';
    public $collegeClassId = '';
    public $statusFilter = '';
    
    // Batch Billing Properties
    public $selectedAcademicYearId = '';
    public $selectedSemesterId = '';
    public $selectedCollegeClassId = '';
    public $showBatchBillingConfirmation = false;
    
    // General Properties
    public $studentId = '';
    public $showBillingResult = false;
    public $billingResult = [];
    
    protected $billingService;
    
    public function boot(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }
    
    public function mount()
    {
        // Set defaults to current academic year and semester if any exists
        $currentYear = AcademicYear::where('is_current', true)->first();
        $currentSemester = Semester::where('is_current', true)->first();
        
        $this->academicYearId = $currentYear ? $currentYear->id : '';
        $this->semesterId = $currentSemester ? $currentSemester->id : '';
        
        $this->selectedAcademicYearId = $this->academicYearId;
        $this->selectedSemesterId = $this->semesterId;
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingAcademicYearId()
    {
        $this->resetPage();
    }
    
    public function updatingSemesterId()
    {
        $this->resetPage();
    }
    
    public function updatingCollegeClassId()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function generateBillForStudent($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            $academicYear = AcademicYear::findOrFail($this->academicYearId);
            $semester = Semester::findOrFail($this->semesterId);
            
            $bill = $this->billingService->generateBill($student, $academicYear->id, $semester->id);
            
            $this->dispatchBrowserEvent('notify', [
                'message' => "Fee bill generated successfully for {$student->full_name}!",
                'type' => 'success'
            ]);
            
            return $bill;
            
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'message' => "Failed to generate bill: {$e->getMessage()}",
                'type' => 'error'
            ]);
            
            return null;
        }
    }
    
    public function confirmBatchBilling()
    {
        $this->validate([
            'selectedAcademicYearId' => 'required|exists:academic_years,id',
            'selectedSemesterId' => 'required|exists:semesters,id',
            'selectedCollegeClassId' => 'required|exists:college_classes,id',
        ]);
        
        $this->showBatchBillingConfirmation = true;
    }
    
    public function generateBatchBills()
    {
        try {
            $generatedBills = $this->billingService->generateBillsForClass(
                $this->selectedCollegeClassId,
                $this->selectedAcademicYearId,
                $this->selectedSemesterId
            );
            
            $this->billingResult = [
                'success' => true,
                'totalStudents' => count($generatedBills),
                'academicYear' => AcademicYear::find($this->selectedAcademicYearId)->name,
                'semester' => Semester::find($this->selectedSemesterId)->name,
                'class' => CollegeClass::find($this->selectedCollegeClassId)->name
            ];
            
            $this->showBillingResult = true;
            $this->showBatchBillingConfirmation = false;
            
            $this->dispatchBrowserEvent('notify', [
                'message' => "Generated bills for {$this->billingResult['totalStudents']} students successfully!",
                'type' => 'success'
            ]);
            
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'message' => "Failed to generate batch bills: {$e->getMessage()}",
                'type' => 'error'
            ]);
            
            $this->showBatchBillingConfirmation = false;
        }
    }
    
    public function cancelBatchBilling()
    {
        $this->showBatchBillingConfirmation = false;
    }
    
    public function closeBillingResult()
    {
        $this->showBillingResult = false;
        $this->billingResult = [];
    }
    
    public function viewBill($billId)
    {
        return redirect()->route('finance.bill.view', ['id' => $billId]);
    }
    
    public function render()
    {
        $query = StudentFeeBill::query();
        
        // Apply filters
        if ($this->academicYearId) {
            $query->where('academic_year_id', $this->academicYearId);
        }
        
        if ($this->semesterId) {
            $query->where('semester_id', $this->semesterId);
        }
        
        if ($this->search) {
            $query->whereHas('student', function ($studentQuery) {
                $studentQuery->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('student_id', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->collegeClassId) {
            $query->whereHas('student', function ($studentQuery) {
                $studentQuery->where('college_class_id', $this->collegeClassId);
            });
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        
        $bills = $query->with(['student', 'academicYear', 'semester'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $semesters = Semester::all();
        $classes = CollegeClass::all();
        
        $studentsWithoutBills = collect();
        
        // Get students without bills if filters are set
        if ($this->academicYearId && $this->semesterId && $this->collegeClassId) {
            $studentsWithoutBills = Student::where('college_class_id', $this->collegeClassId)
                ->whereDoesntHave('feeBills', function ($query) {
                    $query->where('academic_year_id', $this->academicYearId)
                        ->where('semester_id', $this->semesterId);
                })
                ->get();
        }
        
        return view('livewire.finance.student-billing-manager', [
            'bills' => $bills,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'classes' => $classes,
            'studentsWithoutBills' => $studentsWithoutBills,
        ])->layout('components.dashboard.layout');
    }
}