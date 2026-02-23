<?php

namespace App\Livewire\Finance;

use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\FeeStructure;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Services\StudentBillingService;
use Livewire\Component;
use Livewire\WithPagination;

class StudentBillingManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filter properties
    public $academicYearId = '';

    public $semesterId = '';

    public $collegeClassId = '';

    public $cohortId = '';

    public $search = '';

    // New Bill Modal Properties
    public $showNewBillModal = false;

    public $newBillStudentId = null;

    public $newBillAcademicYearId = null;

    public $newBillSemesterId = null;

    public $availableFees = [];

    public $selectedFeeIds = [];

    // Batch Billing Properties
    public $batchAcademicYearId = null;

    public $batchSemesterId = null;

    public $batchClassId = null;

    /** Optional: restrict batch generation to this cohort only. */
    public $batchCohortId = null;

    public $batchAvailableFees = [];

    public $batchSelectedFeeIds = [];

    public $showBatchBillsModal = false;

    /**
     * Effective selected fee IDs = mandatory fees (always included, not submitted when checkboxes are disabled)
     * + any optional fees the user checked. Works when there are zero mandatory fees (user selection only).
     * Uses strict int comparison so IDs match after Livewire serialization (string vs int).
     */
    protected function getEffectiveFeeSelection(array $availableFees, array $selectedIds): array
    {
        $fees = collect($availableFees)->map(fn ($f) => [
            'id' => (int) ($f['id'] ?? 0),
            'amount' => (float) ($f['amount'] ?? 0),
            'is_mandatory' => $f['is_mandatory'] ?? false,
        ]);
        $mandatoryIds = $fees->where('is_mandatory', true)->pluck('id')->values()->toArray();
        $userIds = array_values(array_unique(array_map('intval', $selectedIds)));
        $effectiveIds = array_values(array_unique(array_merge($mandatoryIds, $userIds)));
        $total = $fees->whereIn('id', $effectiveIds)->sum('amount');

        return ['ids' => $effectiveIds, 'total' => round($total, 2)];
    }

    protected $rules = [
        'newBillStudentId' => 'required|exists:students,id',
        'newBillAcademicYearId' => 'required|exists:academic_years,id',
        'newBillSemesterId' => 'required|exists:semesters,id',
    ];

    // Listeners not needed in Livewire v3 for simple refresh

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

    public function updatingCohortId()
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
        $this->reset(['newBillStudentId', 'newBillAcademicYearId', 'newBillSemesterId', 'availableFees', 'selectedFeeIds']);
        $this->resetValidation();
    }

    public function updatedNewBillStudentId()
    {
        $this->loadAvailableFees();
    }

    public function updatedNewBillAcademicYearId()
    {
        $this->loadAvailableFees();
    }

    public function updatedNewBillSemesterId()
    {
        $this->loadAvailableFees();
    }

    public function loadAvailableFees()
    {
        if ($this->newBillStudentId && $this->newBillAcademicYearId && $this->newBillSemesterId) {
            $student = Student::find($this->newBillStudentId);

            if ($student && $student->college_class_id) {
                $studentGender = $student->gender ? strtolower(trim($student->gender)) : '';
                $feeStructures = FeeStructure::with('feeType')
                    ->where('college_class_id', $student->college_class_id)
                    ->where('academic_year_id', $this->newBillAcademicYearId)
                    ->where('semester_id', $this->newBillSemesterId)
                    ->where('is_active', true)
                    ->get();

                // Only include fees applicable to this student's gender (all, or matching male/female)
                $this->availableFees = $feeStructures->filter(function ($fs) use ($studentGender) {
                    $ag = $fs->applicable_gender ?? 'all';
                    if (empty($ag) || $ag === 'all') {
                        return true;
                    }
                    return $studentGender && strtolower($ag) === $studentGender;
                })->values()->toArray();

                // Auto-select all mandatory fees
                $this->selectedFeeIds = collect($this->availableFees)
                    ->filter(fn ($fee) => $fee['is_mandatory'])
                    ->pluck('id')
                    ->toArray();
            } else {
                $this->availableFees = [];
                $this->selectedFeeIds = [];
            }
        } else {
            $this->availableFees = [];
            $this->selectedFeeIds = [];
        }
    }

    public function openBatchBillsModal()
    {
        $this->showBatchBillsModal = true;
    }

    public function closeBatchBillsModal()
    {
        $this->showBatchBillsModal = false;
        $this->reset(['batchAcademicYearId', 'batchSemesterId', 'batchClassId', 'batchCohortId', 'batchAvailableFees', 'batchSelectedFeeIds']);
        $this->resetValidation();
    }

    public function updatedBatchAcademicYearId()
    {
        $this->loadBatchAvailableFees();
    }

    public function updatedBatchSemesterId()
    {
        $this->loadBatchAvailableFees();
    }

    public function updatedBatchClassId()
    {
        $this->loadBatchAvailableFees();
    }

    public function loadBatchAvailableFees()
    {
        if ($this->batchClassId && $this->batchAcademicYearId && $this->batchSemesterId) {
            $this->batchAvailableFees = FeeStructure::with('feeType')
                ->where('college_class_id', $this->batchClassId)
                ->where('academic_year_id', $this->batchAcademicYearId)
                ->where('semester_id', $this->batchSemesterId)
                ->where('is_active', true)
                ->get()
                ->toArray();

            // Auto-select all mandatory fees
            $this->batchSelectedFeeIds = collect($this->batchAvailableFees)
                ->filter(fn ($fee) => $fee['is_mandatory'])
                ->pluck('id')
                ->toArray();
        } else {
            $this->batchAvailableFees = [];
            $this->batchSelectedFeeIds = [];
        }
    }

    public function createNewBill()
    {
        $this->validate();

        $effective = $this->getEffectiveFeeSelection($this->availableFees, $this->selectedFeeIds ?? []);

        if (empty($effective['ids'])) {
            session()->flash('error', 'Please select at least one fee to include in the bill.');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please select at least one fee to include in the bill.']);

            return;
        }

        try {
            $billingService = new StudentBillingService;
            $student = Student::findOrFail($this->newBillStudentId);

            $bill = $billingService->generateBill($student, $this->newBillAcademicYearId, $this->newBillSemesterId, $effective['ids']);

            $this->closeNewBillModal();
            session()->flash('success', 'Student bill created successfully!');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Student bill created successfully!']);
        } catch (\Exception $e) {
            session()->flash('error', 'Error: '.$e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error: '.$e->getMessage()]);
        }
    }

    public function generateBatchBills()
    {
        $this->validate([
            'batchAcademicYearId' => 'required|exists:academic_years,id',
            'batchSemesterId' => 'required|exists:semesters,id',
            'batchClassId' => 'required|exists:college_classes,id',
        ]);

        $effective = $this->getEffectiveFeeSelection($this->batchAvailableFees, $this->batchSelectedFeeIds ?? []);

        if (empty($effective['ids'])) {
            session()->flash('error', 'Please select at least one fee to include in the bills.');
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Please select at least one fee to include in the bills.']);

            return;
        }

        try {
            $billingService = new StudentBillingService;
            $studentsQuery = Student::where('college_class_id', $this->batchClassId);
            if (! empty($this->batchCohortId)) {
                $studentsQuery->where('cohort_id', $this->batchCohortId);
            }
            $students = $studentsQuery->get();

            if ($students->isEmpty()) {
                $scope = ! empty($this->batchCohortId)
                    ? 'in the selected program and batch (cohort)'
                    : 'in the selected program';
                session()->flash('warning', "No active students found {$scope}.");
                $this->dispatch('notify', ['type' => 'warning', 'message' => "No active students found {$scope}."]);

                return;
            }

            $billCount = 0;
            foreach ($students as $student) {
                $billingService->generateBill($student, $this->batchAcademicYearId, $this->batchSemesterId, $effective['ids']);
                $billCount++;
            }

            $this->closeBatchBillsModal();
            session()->flash('success', $billCount.' student bills generated successfully!');
            $this->dispatch('notify', ['type' => 'success', 'message' => $billCount.' student bills generated successfully!']);

        } catch (\Exception $e) {
            session()->flash('error', 'Error generating batch bills: '.$e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Error generating batch bills: '.$e->getMessage()]);
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
            ->when($this->cohortId !== '', function ($query) {
                return $query->whereHas('student', function ($q) {
                    $q->where('cohort_id', $this->cohortId);
                });
            })
            ->when($this->search !== '', function ($query) {
                return $query->whereHas('student', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('student_id', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);

        $newBillEffective = ! empty($this->availableFees)
            ? $this->getEffectiveFeeSelection($this->availableFees, $this->selectedFeeIds ?? [])
            : ['ids' => [], 'total' => 0];
        $batchEffective = ! empty($this->batchAvailableFees)
            ? $this->getEffectiveFeeSelection($this->batchAvailableFees, $this->batchSelectedFeeIds ?? [])
            : ['ids' => [], 'total' => 0];

        return view('livewire.finance.student-billing-manager', [
            'bills' => $bills,
            'academicYears' => AcademicYear::orderBy('name', 'desc')->get(),
            'semesters' => Semester::orderBy('name')->get(),
            'classes' => CollegeClass::orderBy('name')->get(),
            'cohorts' => Cohort::orderBy('name')->get(),
            'students' => Student::select('id', 'student_id', 'first_name', 'last_name', 'other_name')->orderBy('first_name')->get(),
            'newBillTotalSelected' => $newBillEffective['total'],
            'batchTotalSelected' => $batchEffective['total'],
        ]);
    }
}
