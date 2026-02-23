<?php

namespace App\Livewire\Finance;

use App\Models\FeeType;
use Livewire\Component;
use Livewire\WithPagination;

class FeeTypesManager extends Component
{
    use WithPagination;

    public $name;

    public $code;

    public $description;

    public $is_active = true;

    public $editingFeeTypeId = null;

    public $feeTypeIdToDelete = null;

    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = FeeType::query();

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('code', 'like', '%'.$this->search.'%');
        }

        $feeTypes = $query->orderBy('name')->paginate(10);

        return view('livewire.finance.fee-types-manager', [
            'feeTypes' => $feeTypes,
        ])
            ->layout('components.dashboard.default');
    }

    public function saveFeeType()
    {
        if ($this->editingFeeTypeId) {
            return $this->updateFeeType();
        }

        $this->validate();

        // Check for uniqueness of code
        $existingCode = FeeType::where('code', $this->code)->first();
        if ($existingCode) {
            $this->addError('code', 'This code is already in use.');

            return;
        }

        FeeType::create([
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->resetInputFields();
        $this->dispatch('close-fee-type-form-modal');
        session()->flash('message', 'Fee type created successfully.');
    }

    public function editFeeType($id)
    {
        $feeType = FeeType::findOrFail($id);
        $this->editingFeeTypeId = $id;
        $this->name = $feeType->name;
        $this->code = $feeType->code;
        $this->description = $feeType->description;
        $this->is_active = $feeType->is_active;
        $this->dispatch('open-fee-type-form-modal');
    }

    public function updateFeeType()
    {
        $this->validate();

        // Check for uniqueness of code (excluding current record)
        $existingCode = FeeType::where('code', $this->code)
            ->where('id', '!=', $this->editingFeeTypeId)
            ->first();
        if ($existingCode) {
            $this->addError('code', 'This code is already in use.');

            return;
        }

        $feeType = FeeType::findOrFail($this->editingFeeTypeId);
        $feeType->update([
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->resetInputFields();
        $this->dispatch('close-fee-type-form-modal');
        session()->flash('message', 'Fee type updated successfully.');
    }

    public function confirmFeeTypeDeletion($id)
    {
        $this->feeTypeIdToDelete = $id;
        $this->dispatch('show-delete-modal');
    }

    public function deleteFeeType()
    {
        $feeType = FeeType::findOrFail($this->feeTypeIdToDelete);

        // Check if fee type has any fee structures
        if ($feeType->feeStructures()->count() > 0) {
            session()->flash('error', 'Fee type cannot be deleted because it is associated with fee structures.');

            return;
        }

        $feeType->delete();
        $this->feeTypeIdToDelete = null;
        $this->dispatch('close-delete-modal');
        session()->flash('message', 'Fee type deleted successfully.');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->is_active = true;
        $this->editingFeeTypeId = null;
        $this->resetErrorBag();
    }

    public function cancelEdit()
    {
        $this->resetInputFields();
    }
}
