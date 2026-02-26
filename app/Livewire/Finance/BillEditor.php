<?php

namespace App\Livewire\Finance;

use App\Models\FeeStructure;
use App\Models\StudentFeeBill;
use App\Services\StudentBillingService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BillEditor extends Component
{
    public $billId;

    public $bill;

    public $availableFees = [];

    public $selectedFeeIds = [];

    public $loading = true;

    public $canEdit = false;

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
                'billItems.feeType',
                'billItems.feeStructure',
                'payments',
            ])->findOrFail($this->billId);

            $this->canEdit = $this->bill->payments->isEmpty();

            $this->loadAvailableFees();

            $this->loading = false;
        } catch (\Exception $e) {
            Log::error('Error loading bill for editing', [
                'billId' => $this->billId,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Error loading bill for editing: '.$e->getMessage());
            $this->loading = false;
        }
    }

    protected function loadAvailableFees()
    {
        if (! $this->bill || ! $this->bill->student) {
            $this->availableFees = [];
            $this->selectedFeeIds = [];

            return;
        }

        $student = $this->bill->student;

        if (! $student->college_class_id) {
            $this->availableFees = [];
            $this->selectedFeeIds = [];

            return;
        }

        $studentGender = $student->gender ? strtolower(trim($student->gender)) : '';

        $feeStructures = FeeStructure::with('feeType')
            ->where('college_class_id', $student->college_class_id)
            ->where('academic_year_id', $this->bill->academic_year_id)
            ->where('semester_id', $this->bill->semester_id)
            ->where('is_active', true)
            ->get();

        $this->availableFees = $feeStructures->filter(function ($fs) use ($studentGender) {
            $ag = $fs->applicable_gender ?? 'all';
            if (empty($ag) || $ag === 'all') {
                return true;
            }

            return $studentGender && strtolower($ag) === $studentGender;
        })->values()->toArray();

        $mandatoryIds = collect($this->availableFees)
            ->filter(fn ($fee) => $fee['is_mandatory'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $existingIds = $this->bill->billItems
            ->pluck('fee_structure_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        $this->selectedFeeIds = array_values(array_unique(array_merge($mandatoryIds, $existingIds)));
    }

    public function save()
    {
        if (! $this->bill) {
            session()->flash('error', 'Bill not loaded.');

            return;
        }

        if (! $this->canEdit) {
            session()->flash('error', 'This bill already has payments recorded and cannot be edited.');

            return;
        }

        $effective = $this->getEffectiveFeeSelection($this->availableFees, $this->selectedFeeIds ?? []);

        if (empty($effective['ids'])) {
            session()->flash('error', 'Please select at least one fee to include on the bill.');

            return;
        }

        try {
            $billingService = new StudentBillingService;
            $updatedBill = $billingService->updateBillItems($this->bill, $effective['ids']);

            session()->flash('success', 'Bill updated successfully.');

            return redirect()->route('finance.bill.view', ['id' => $updatedBill->id]);
        } catch (\Exception $e) {
            Log::error('Error updating bill items', [
                'billId' => $this->billId,
                'error' => $e->getMessage(),
            ]);

            session()->flash('error', 'Error updating bill: '.$e->getMessage());
        }
    }

    public function render()
    {
        $effective = ! empty($this->availableFees)
            ? $this->getEffectiveFeeSelection($this->availableFees, $this->selectedFeeIds ?? [])
            : ['ids' => [], 'total' => 0];

        return view('livewire.finance.bill-editor', [
            'totalSelected' => $effective['total'],
        ]);
    }
}

