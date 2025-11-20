<?php

namespace App\Livewire\Finance;

use App\Models\FeeType;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class FeeTypeManager extends Component
{
    use WithPagination;

    public $name;

    public $code;

    public $description;

    public $is_active = true;

    public $isEditing = false;

    public $feeTypeId = null;

    public $confirmingDeletion = false;

    public $deleteId = null;

    public $search = '';

    protected $rules = [
        'name' => 'required|min:3|max:255',
        'code' => 'required|min:2|max:20',
        'description' => 'nullable|max:1000',
        'is_active' => 'boolean',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function createFeeType()
    {
        $this->resetExcept('search');
        $this->isEditing = false;
        $this->feeTypeId = null;
        $this->dispatchBrowserEvent('open-form-modal');
    }

    public function editFeeType($id)
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->isEditing = true;
        $this->feeTypeId = $id;

        $feeType = FeeType::findOrFail($id);
        $this->name = $feeType->name;
        $this->code = $feeType->code;
        $this->description = $feeType->description;
        $this->is_active = $feeType->is_active;

        $this->dispatchBrowserEvent('open-form-modal');
    }

    public function saveFeeType()
    {
        if ($this->isEditing) {
            $this->validate([
                'name' => 'required|min:3|max:255',
                'code' => ['required', 'min:2', 'max:20', Rule::unique('fee_types', 'code')->ignore($this->feeTypeId)],
                'description' => 'nullable|max:1000',
                'is_active' => 'boolean',
            ]);

            $feeType = FeeType::findOrFail($this->feeTypeId);
            $feeType->update([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            $this->dispatchBrowserEvent('close-form-modal');
            $this->dispatchBrowserEvent('notify', ['message' => 'Fee type updated successfully!', 'type' => 'success']);
        } else {
            $this->validate([
                'name' => 'required|min:3|max:255',
                'code' => 'required|min:2|max:20|unique:fee_types,code',
                'description' => 'nullable|max:1000',
                'is_active' => 'boolean',
            ]);

            FeeType::create([
                'name' => $this->name,
                'code' => $this->code,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            $this->dispatchBrowserEvent('close-form-modal');
            $this->dispatchBrowserEvent('notify', ['message' => 'Fee type created successfully!', 'type' => 'success']);
        }

        $this->resetExcept('search');
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeletion = true;
        $this->deleteId = $id;

        $this->dispatchBrowserEvent('open-delete-modal');
    }

    public function deleteFeeType()
    {
        try {
            $feeType = FeeType::findOrFail($this->deleteId);

            // Check if the fee type is being used in fee structures
            $usageCount = $feeType->feeStructures()->count();
            if ($usageCount > 0) {
                $this->dispatchBrowserEvent('notify', [
                    'message' => "Cannot delete this fee type. It is being used in {$usageCount} fee structures.",
                    'type' => 'error',
                ]);
                $this->confirmingDeletion = false;
                $this->dispatchBrowserEvent('close-delete-modal');

                return;
            }

            $feeType->delete();

            $this->dispatchBrowserEvent('close-delete-modal');
            $this->dispatchBrowserEvent('notify', [
                'message' => 'Fee type deleted successfully!',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'message' => 'An error occurred while deleting the fee type.',
                'type' => 'error',
            ]);
        }

        $this->confirmingDeletion = false;
    }

    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->deleteId = null;
        $this->dispatchBrowserEvent('close-delete-modal');
    }

    public function toggleActive($id)
    {
        $feeType = FeeType::findOrFail($id);
        $feeType->update([
            'is_active' => ! $feeType->is_active,
        ]);

        $this->dispatchBrowserEvent('notify', [
            'message' => $feeType->is_active ? 'Fee type activated' : 'Fee type deactivated',
            'type' => 'success',
        ]);
    }

    public function render()
    {
        $feeTypes = FeeType::when($this->search, function ($query) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('code', 'like', '%'.$this->search.'%');
        })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.finance.fee-type-manager', [
            'feeTypes' => $feeTypes,
        ])->layout('components.dashboard.default');
    }
}
