<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StudentFeeBill;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\CollegeClass;
use App\Services\StudentBillingService;

class StudentBillingManager extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    // Filter properties
    public $academicYearId = '';
    public $semesterId = '';
    public $collegeClassId = '';
    public $search = '';
    
    // New Bill Modal Properties
    public $showNewBillModal = false;
    public $newBillStudentId = null;
    public $newBillAcademicYearId = null;
    public $newBillSemesterId = null;
    
    // Batch Billing Properties
    public $batchAcademicYearId = null;
    public $batchSemesterId = null;
    public $batchClassId = null;
    public $showBatchBillsModal = false;
    
    protected $rules = [
        'newBillStudentId' => 'required|exists:students,id',
        'newBillAcademicYearId' => 'required|exists:academic_years,id',
        'newBillSemesterId' => 'required|exists:semesters,id',
    ];
    
    protected $listeners = ['refreshBillingList' => '$refresh'];
    
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
    
    public function openNewBillModal()
    {
        $this->showNewBillModal = true;
    }
    
    public function closeNewBillModal()
    {
        $this->showNewBillModal = false;
        $this->reset(['newBillStudentId', 'newBillAcademicYearId', 'newBillSemesterId']);
        $this->resetValidation();
    }
    
    public function openBatchBillsModal()
    {
        $this->showBatchBillsModal = true;
    }
    
    public function closeBatchBillsModal()
    {
        $this->showBatchBillsModal = false;
        $this->reset(['batchAcademicYearId', 'batchSemesterId', 'batchClassId']);
        $this->resetValidation();
    }
    
    public function createNewBill()
    {
        $this->validate();
        
        try {
            $billingService = new StudentBillingService();
            $student = Student::findOrFail($this->newBillStudentId);
            
            $bill = $billingService->generateBill($student, $this->newBillAcademicYearId, $this->newBillSemesterId);
            
            $this->closeNewBillModal();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Student bill created successfully!']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    public function generateBatchBills()
    {
        $this->validate([
            'batchAcademicYearId' => 'required|exists:academic_years,id',
            'batchSemesterId' => 'required|exists:semesters,id',
            'batchClassId' => 'required|exists:college_classes,id',
        ]);
        
        try {
            $billingService = new StudentBillingService();
            $students = Student::where('college_class_id', $this->batchClassId)
                              ->where('status', 'active')
                              ->get();
                              
            if ($students->isEmpty()) {
                $this->dispatch('notify', ['type' => 'warning', 'message' => 'No active students found in the selected class!']);
                return;
            }
            
            $billCount = 0;
            foreach ($students as $student) {
                $billingService->generateBill($student, $this->batchAcademicYearId, $this->batchSemesterId);
                $billCount++;
            }
            
            $this->reset(['batchAcademicYearId', 'batchSemesterId', 'batchClassId']);
            $this->dispatch('notify', ['type' => 'success', 'message' => $billCount . ' student bills generated successfully!']);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error generating batch bills: ' . $e->getMessage()]);
        }
    }
    
    public function viewBill($billId)
    {
        return redirect()->route('finance.bill.view', ['id' => $billId]);
    }
    
    public function render()
    {
        $bills = StudentFeeBill::with(['student', 'academicYear', 'semester'])
            ->when($this->academicYearId !== '', function ($query) {
                return $query->where('academic_year_id', $this->academicYearId);
            })
            ->when($this->semesterId !== '', function ($query) {
                return $query->where('semester_id', $this->semesterId);
            })
            ->when($this->collegeClassId !== '', function ($query) {
                return $query->whereHas('student', function ($q) {
                    $q->where('college_class_id', $this->collegeClassId);
                });
            })
            ->when($this->search !== '', function ($query) {
                return $query->whereHas('student', function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('student_id', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);
        
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();
        $classes = CollegeClass::orderBy('name')->get();
        $students = Student::orderBy('first_name')->get();
        
        return view('livewire.finance.student-billing-manager', [
            'bills' => $bills,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'classes' => $classes,
            'students' => $students,
        ])->layout('components.dashboard.default');
    }
}