<?php

namespace App\Livewire\Academics;

use App\Models\ExamType;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ExamTypeManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $name;

    public $code;

    public $payment_threshold = 50.00; // Default 50%

    public $description;

    public $is_active = true;

    public $editingExamTypeId = null;

    public $examTypeIdToDelete = null;

    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50',
        'payment_threshold' => 'required|numeric|min:0|max:100',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = ExamType::query();

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('code', 'like', '%'.$this->search.'%');
        }

        $examTypes = $query->orderBy('name')->paginate(10);

        return view('livewire.academics.exam-type-manager', [
            'examTypes' => $examTypes,
        ])->layout('components.dashboard.default');
    }

    public function saveExamType()
    {
        if ($this->editingExamTypeId) {
            return $this->updateExamType();
        }

        $this->validate();

        // Check for uniqueness of code
        $existingCode = ExamType::where('code', $this->code)->first();
        if ($existingCode) {
            $this->addError('code', 'This code is already in use.');

            return;
        }

        try {
            ExamType::create([
                'name' => $this->name,
                'code' => $this->code,
                'payment_threshold' => $this->payment_threshold,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            Log::info('Exam type created successfully', [
                'name' => $this->name,
                'code' => $this->code,
            ]);

            $this->resetInputFields();
            session()->flash('message', 'Exam type created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating exam type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Failed to create exam type: '.$e->getMessage());
        }
    }

    public function editExamType($id)
    {
        $examType = ExamType::findOrFail($id);
        $this->editingExamTypeId = $id;
        $this->name = $examType->name;
        $this->code = $examType->code;
        $this->payment_threshold = $examType->payment_threshold;
        $this->description = $examType->description;
        $this->is_active = $examType->is_active;
    }

    public function updateExamType()
    {
        $this->validate();

        // Check for uniqueness of code (excluding current record)
        $existingCode = ExamType::where('code', $this->code)
            ->where('id', '!=', $this->editingExamTypeId)
            ->first();

        if ($existingCode) {
            $this->addError('code', 'This code is already in use.');

            return;
        }

        try {
            $examType = ExamType::findOrFail($this->editingExamTypeId);
            $examType->update([
                'name' => $this->name,
                'code' => $this->code,
                'payment_threshold' => $this->payment_threshold,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            Log::info('Exam type updated successfully', [
                'id' => $examType->id,
                'name' => $examType->name,
            ]);

            $this->resetInputFields();
            session()->flash('message', 'Exam type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating exam type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Failed to update exam type: '.$e->getMessage());
        }
    }

    public function confirmExamTypeDeletion($id)
    {
        $this->examTypeIdToDelete = $id;
        $this->dispatch('show-delete-modal');
    }

    public function deleteExamType()
    {
        try {
            $examType = ExamType::findOrFail($this->examTypeIdToDelete);

            // Check if exam type has any clearances
            if ($examType->examClearances()->count() > 0) {
                session()->flash('error', 'Exam type cannot be deleted because it is associated with exam clearances.');

                return;
            }

            // Check if exam type has any exams
            if ($examType->exams()->count() > 0) {
                session()->flash('error', 'Exam type cannot be deleted because it is associated with exams.');

                return;
            }

            $examType->delete();
            Log::info('Exam type deleted successfully', ['id' => $this->examTypeIdToDelete]);
            session()->flash('message', 'Exam type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting exam type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', 'Failed to delete exam type: '.$e->getMessage());
        }
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->code = '';
        $this->payment_threshold = 50.00;
        $this->description = '';
        $this->is_active = true;
        $this->editingExamTypeId = null;
        $this->resetErrorBag();
    }

    public function cancelEdit()
    {
        $this->resetInputFields();
    }
}
