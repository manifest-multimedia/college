<?php

namespace App\Livewire\Finance;

use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\AcademicYear;
use App\Models\Semester;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FeePaymentManager extends Component
{
    use WithPagination;

    // Pagination properties
    protected $paginationTheme = 'simple-bootstrap';
    
    // Search and filter properties
    public $search = '';
    public $academicYearId;
    public $semesterId;
    public $showPaymentForm = false;
    
    // Payment form properties
    public $studentId;
    public $studentFeeBillId;
    public $paymentAmount = 0;
    public $paymentMethod = 'Cash';
    public $referenceNumber = '';
    public $paymentDate;
    public $note = '';
    
    // View payment details
    public $selectedPaymentId;
    public $showPaymentDetails = false;
    
    // Track loaded student details
    public $loadedStudent = null;
    public $loadedBill = null;
    
    protected $rules = [
        'studentFeeBillId' => 'required|exists:student_fee_bills,id',
        'paymentAmount' => 'required|numeric|min:1',
        'paymentMethod' => 'required|string',
        'paymentDate' => 'required|date',
        'referenceNumber' => 'nullable|string|max:50',
        'note' => 'nullable|string|max:255',
    ];
    
    public function mount()
    {
        // Set default values
        $this->academicYearId = AcademicYear::orderBy('year', 'desc')->first()?->id;
        $this->semesterId = Semester::where('is_current', true)->first()?->id ?? 
                            Semester::orderBy('id', 'desc')->first()?->id;
                            
        // Set today's date as default payment date
        $this->paymentDate = now()->format('Y-m-d');
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function loadStudent($id)
    {
        $this->loadedStudent = Student::with(['feeBills' => function($query) {
            $query->where('academic_year_id', $this->academicYearId)
                  ->where('semester_id', $this->semesterId)
                  ->latest();
        }])->findOrFail($id);
        
        $this->studentId = $this->loadedStudent->id;
        
        // If student has any bills for selected semester, load the first one
        if ($this->loadedStudent->feeBills->isNotEmpty()) {
            $this->loadBill($this->loadedStudent->feeBills->first()->id);
        } else {
            $this->loadedBill = null;
            $this->studentFeeBillId = null;
        }
    }
    
    public function loadBill($id)
    {
        $this->loadedBill = StudentFeeBill::with(['payments', 'student', 'academicYear', 'semester'])
            ->findOrFail($id);
        $this->studentFeeBillId = $this->loadedBill->id;
    }
    
    public function openPaymentForm()
    {
        if (!$this->loadedBill) {
            session()->flash('error', 'Please select a student bill first');
            return;
        }
        
        // Calculate remaining balance as default payment amount
        $this->paymentAmount = $this->loadedBill->balance;
        $this->showPaymentForm = true;
        $this->dispatch('show-payment-form');
    }
    
    public function recordPayment()
    {
        $this->validate();
        
        try {
            // Verify the payment doesn't exceed the balance
            if ($this->paymentAmount > $this->loadedBill->balance) {
                $this->addError('paymentAmount', 'Payment amount cannot exceed the remaining balance.');
                return;
            }
            
            // Generate a receipt number
            $receiptNumber = 'FP' . date('Ymd') . strtoupper(Str::random(5));
            
            // Record the payment
            $payment = FeePayment::create([
                'student_fee_bill_id' => $this->studentFeeBillId,
                'student_id' => $this->studentId,
                'amount' => $this->paymentAmount,
                'payment_method' => $this->paymentMethod,
                'reference_number' => $this->referenceNumber,
                'receipt_number' => $receiptNumber,
                'note' => $this->note,
                'recorded_by' => Auth::id(),
                'payment_date' => Carbon::parse($this->paymentDate),
            ]);
            
            // Update bill's total paid amount and payment percentage
            $this->loadedBill->recalculatePaymentStatus();
            
            $this->showPaymentForm = false;
            $this->dispatch('hide-payment-form');
            $this->resetPaymentForm();
            
            // Reload the bill to reflect new payment
            $this->loadBill($this->studentFeeBillId);
            
            // Show success message
            session()->flash('message', 'Payment of ' . number_format($this->paymentAmount, 2) . ' has been recorded successfully. Receipt #: ' . $receiptNumber);
            
            // Select this payment to view the receipt
            $this->selectedPaymentId = $payment->id;
            $this->showPaymentDetails = true;
            $this->dispatch('show-payment-details');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error recording payment: ' . $e->getMessage());
        }
    }
    
    public function viewPayment($id)
    {
        $this->selectedPaymentId = $id;
        $this->showPaymentDetails = true;
        $this->dispatch('show-payment-details');
    }
    
    public function closePaymentDetails()
    {
        $this->showPaymentDetails = false;
        $this->selectedPaymentId = null;
        $this->dispatch('hide-payment-details');
    }
    
    public function resetPaymentForm()
    {
        $this->paymentAmount = 0;
        $this->paymentMethod = 'Cash';
        $this->referenceNumber = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->note = '';
        $this->resetValidation();
    }
    
    public function getSelectedPaymentProperty()
    {
        if (!$this->selectedPaymentId) {
            return null;
        }
        
        return FeePayment::with(['student', 'studentFeeBill', 'recordedBy'])
            ->findOrFail($this->selectedPaymentId);
    }
    
    public function getPaymentMethodsProperty()
    {
        return [
            'Cash' => 'Cash Payment',
            'Bank Transfer' => 'Bank Transfer',
            'Mobile Money' => 'Mobile Money',
            'Cheque' => 'Cheque',
            'Other' => 'Other',
        ];
    }
    
    public function getAcademicYearsProperty()
    {
        return AcademicYear::orderBy('year', 'desc')->get();
    }
    
    public function getSemestersProperty()
    {
        return Semester::orderBy('name')->get();
    }
    
    public function getRecentPaymentsProperty()
    {
        return FeePayment::with(['student', 'studentFeeBill'])
            ->latest()
            ->take(10)
            ->get();
    }
    
    public function render()
    {
        $students = Student::when($this->search, function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('student_id', 'like', '%' . $this->search . '%');
            })
            ->orderBy('first_name')
            ->paginate(10);
            
        return view('livewire.finance.fee-payment-manager', [
            'students' => $students,
            'academicYears' => $this->academicYears,
            'semesters' => $this->semesters,
            'recentPayments' => $this->recentPayments,
            'paymentMethods' => $this->paymentMethods,
        ])->layout('components.dashboard.default');
    }
}
