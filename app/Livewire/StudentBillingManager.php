<?php

namespace App\Livewire;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\FeeStructure;
use App\Models\FeeType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class StudentFeeBillingManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';

    public $selectedClass = '';

    public $selectedAcademicYear = '';

    public $selectedSemester = '';

    public $statusFilter = '';

    // Bulk Billing
    public $bulkBillingClass = '';

    public $bulkBillingAcademicYear = '';

    public $bulkBillingSemester = '';

    public $bulkBillDate = '';

    // Individual student view
    public $selectedStudentId = '';

    public $selectedStudent = null;

    public $studentFeeBills = [];

    // Modals
    public $feeTypeModalOpen = false;

    public $feeStructureModalOpen = false;

    public $newFeeType = [
        'name' => '',
        'code' => '',
        'description' => '',
    ];

    public $newFeeStructure = [
        'fee_type_id' => '',
        'college_class_id' => '',
        'academic_year_id' => '',
        'semester_id' => '',
        'amount' => 0,
        'is_mandatory' => true,
    ];

    // Messages
    public $successMessage = '';

    public $errorMessage = '';

    protected function rules()
    {
        return [
            'newFeeType.name' => 'required|min:3|max:100',
            'newFeeType.code' => 'required|min:2|max:20|unique:fee_types,code',
            'newFeeType.description' => 'nullable|max:255',

            'newFeeStructure.fee_type_id' => 'required|exists:fee_types,id',
            'newFeeStructure.college_class_id' => 'required|exists:college_classes,id',
            'newFeeStructure.academic_year_id' => 'required|exists:academic_years,id',
            'newFeeStructure.semester_id' => 'required|exists:semesters,id',
            'newFeeStructure.amount' => 'required|numeric|min:0',
            'newFeeStructure.is_mandatory' => 'boolean',

            'bulkBillingClass' => 'required_for_bulk_billing|exists:college_classes,id',
            'bulkBillingAcademicYear' => 'required_for_bulk_billing|exists:academic_years,id',
            'bulkBillingSemester' => 'required_for_bulk_billing|exists:semesters,id',
            'bulkBillDate' => 'required_for_bulk_billing|date',
        ];
    }

    protected function messages()
    {
        return [
            'required_for_bulk_billing' => 'This field is required for bulk billing.',
        ];
    }

    public function mount()
    {
        $this->bulkBillDate = Carbon::now()->format('Y-m-d');
    }

    public function render()
    {
        $bills = $this->getBills();
        $students = Student::orderBy('last_name')->get();
        $feeTypes = FeeType::orderBy('name')->get();
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('livewire.student-billing-manager', [
            'bills' => $bills,
            'students' => $students,
            'feeTypes' => $feeTypes,
            'collegeClasses' => $collegeClasses,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
        ]);
    }

    private function getBills()
    {
        return StudentFeeBill::with(['student.collegeClass', 'academicYear', 'semester'])
            ->when($this->search, function ($query) {
                return $query->whereHas('student', function ($q) {
                    $q->where('first_name', 'like', '%'.$this->search.'%')
                        ->orWhere('last_name', 'like', '%'.$this->search.'%')
                        ->orWhere('student_id', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedClass, function ($query) {
                return $query->whereHas('student', function ($q) {
                    $q->where('college_class_id', $this->selectedClass);
                });
            })
            ->when($this->selectedAcademicYear, function ($query) {
                return $query->where('academic_year_id', $this->selectedAcademicYear);
            })
            ->when($this->selectedSemester, function ($query) {
                return $query->where('semester_id', $this->selectedSemester);
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', $this->statusFilter);
            })
            ->withCount(['payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function loadStudentFeeBills()
    {
        if (empty($this->selectedStudentId)) {
            $this->selectedStudent = null;
            $this->studentFeeBills = [];

            return;
        }

        $this->selectedStudent = Student::find($this->selectedStudentId);
        if ($this->selectedStudent) {
            $this->studentFeeBills = StudentFeeBill::where('student_id', $this->selectedStudentId)
                ->with(['academicYear', 'semester', 'payments'])
                ->orderBy('academic_year_id', 'desc')
                ->orderBy('semester_id', 'desc')
                ->get();
        }
    }

    public function saveFeeType()
    {
        $this->validate([
            'newFeeType.name' => 'required|min:3|max:100',
            'newFeeType.code' => 'required|min:2|max:20|unique:fee_types,code',
            'newFeeType.description' => 'nullable|max:255',
        ]);

        $feeType = new FeeType;
        $feeType->name = $this->newFeeType['name'];
        $feeType->code = strtoupper($this->newFeeType['code']);
        $feeType->description = $this->newFeeType['description'] ?? null;
        $feeType->save();

        $this->resetFeeTypeForm();
        $this->successMessage = 'Fee type created successfully!';
        $this->feeTypeModalOpen = false;
        $this->dispatch('closeModals');
    }

    public function saveFeeStructure()
    {
        $this->validate([
            'newFeeStructure.fee_type_id' => 'required|exists:fee_types,id',
            'newFeeStructure.college_class_id' => 'required|exists:college_classes,id',
            'newFeeStructure.academic_year_id' => 'required|exists:academic_years,id',
            'newFeeStructure.semester_id' => 'required|exists:semesters,id',
            'newFeeStructure.amount' => 'required|numeric|min:0',
            'newFeeStructure.is_mandatory' => 'boolean',
        ]);

        // Check for duplicate fee structure
        $existing = FeeStructure::where('fee_type_id', $this->newFeeStructure['fee_type_id'])
            ->where('college_class_id', $this->newFeeStructure['college_class_id'])
            ->where('academic_year_id', $this->newFeeStructure['academic_year_id'])
            ->where('semester_id', $this->newFeeStructure['semester_id'])
            ->exists();

        if ($existing) {
            $this->errorMessage = 'A fee structure with these criteria already exists!';

            return;
        }

        $feeStructure = new FeeStructure;
        $feeStructure->fee_type_id = $this->newFeeStructure['fee_type_id'];
        $feeStructure->college_class_id = $this->newFeeStructure['college_class_id'];
        $feeStructure->academic_year_id = $this->newFeeStructure['academic_year_id'];
        $feeStructure->semester_id = $this->newFeeStructure['semester_id'];
        $feeStructure->amount = $this->newFeeStructure['amount'];
        $feeStructure->is_mandatory = $this->newFeeStructure['is_mandatory'];
        $feeStructure->save();

        $this->resetFeeStructureForm();
        $this->successMessage = 'Fee structure created successfully!';
        $this->feeStructureModalOpen = false;
        $this->dispatch('closeModals');
    }

    public function generateBills()
    {
        $this->validate([
            'bulkBillingClass' => 'required|exists:college_classes,id',
            'bulkBillingAcademicYear' => 'required|exists:academic_years,id',
            'bulkBillingSemester' => 'required|exists:semesters,id',
            'bulkBillDate' => 'required|date',
        ]);

        // Get all students in the selected class
        $students = Student::where('college_class_id', $this->bulkBillingClass)
            ->where('status', 'active')
            ->get();

        // Get fee structures for this class, academic year and semester
        $feeStructures = FeeStructure::where('college_class_id', $this->bulkBillingClass)
            ->where('academic_year_id', $this->bulkBillingAcademicYear)
            ->where('semester_id', $this->bulkBillingSemester)
            ->where('is_mandatory', true)
            ->get();

        if ($feeStructures->isEmpty()) {
            $this->errorMessage = 'No fee structures found for the selected criteria!';

            return;
        }

        $billCount = 0;
        $duplicateCount = 0;

        // Start a DB transaction
        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // Check if a bill already exists
                $existingBill = StudentFeeBill::where('student_id', $student->id)
                    ->where('academic_year_id', $this->bulkBillingAcademicYear)
                    ->where('semester_id', $this->bulkBillingSemester)
                    ->first();

                if ($existingBill) {
                    $duplicateCount++;

                    continue;
                }

                // Calculate the total amount
                $totalAmount = $feeStructures->sum('amount');

                // Create a new bill
                $bill = new StudentFeeBill;
                $bill->student_id = $student->id;
                $bill->academic_year_id = $this->bulkBillingAcademicYear;
                $bill->semester_id = $this->bulkBillingSemester;
                $bill->billing_date = Carbon::parse($this->bulkBillDate);
                $bill->bill_reference = 'BILL-'.Str::upper(Str::random(8));
                $bill->total_amount = $totalAmount;
                $bill->amount_paid = 0;
                $bill->balance = $totalAmount;
                $bill->payment_percentage = 0;
                $bill->status = 'pending';
                $bill->save();

                // Create bill items for each fee structure
                foreach ($feeStructures as $feeStructure) {
                    StudentFeeBillItem::create([
                        'student_fee_bill_id' => $bill->id,
                        'fee_type_id' => $feeStructure->fee_type_id,
                        'fee_structure_id' => $feeStructure->id,
                        'amount' => $feeStructure->amount,
                    ]);
                }

                $billCount++;
            }

            DB::commit();

            $this->successMessage = "Generated $billCount bills successfully! ($duplicateCount duplicates were skipped)";
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Error generating bills: '.$e->getMessage();
        }
    }

    private function resetFeeTypeForm()
    {
        $this->newFeeType = [
            'name' => '',
            'code' => '',
            'description' => '',
        ];
    }

    private function resetFeeStructureForm()
    {
        $this->newFeeStructure = [
            'fee_type_id' => '',
            'college_class_id' => '',
            'academic_year_id' => '',
            'semester_id' => '',
            'amount' => 0,
            'is_mandatory' => true,
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedClass()
    {
        $this->resetPage();
    }

    public function updatingSelectedAcademicYear()
    {
        $this->resetPage();
    }

    public function updatingSelectedSemester()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
}
