<?php

namespace App\Livewire\Finance;

use App\Models\StudentFeeBill;
use App\Models\FeePayment;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BillDetailViewer extends Component
{
    public $billId;
    public $bill;
    public $payments;
    public $totalPaid = 0;
    public $balance = 0;
    public $loading = true;

    public function mount($billId)
    {
        $this->billId = $billId;
        $this->loadBillData();
    }

    public function loadBillData()
    {
        try {
            $this->bill = StudentFeeBill::with([
                'student', 
                'academicYear', 
                'semester', 
                'billItems.feeType'
            ])->findOrFail($this->billId);
            
            $this->payments = FeePayment::where('student_fee_bill_id', $this->billId)
                ->orderBy('payment_date', 'desc')
                ->get();
                
            $this->totalPaid = $this->payments->sum('amount');
            $this->balance = $this->bill->total_amount - $this->totalPaid;
            
            $this->loading = false;
        } catch (\Exception $e) {
            Log::error('Error loading bill details', [
                'billId' => $this->billId,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Error loading bill details: ' . $e->getMessage());
            $this->loading = false;
        }
    }

    public function printBill()
    {
        return redirect()->route('bill.print', ['billId' => $this->billId]);
    }

    public function recordPayment()
    {
        return redirect()->route('payment.record', ['billId' => $this->billId]);
    }

    public function render()
    {
        return view('livewire.finance.bill-detail-viewer');
    }
}
