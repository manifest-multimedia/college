<?php

namespace App\Livewire\Finance;

use App\Models\FeeType;
use App\Models\FeeStructure;
use App\Models\CollegeClass;
use App\Models\AcademicYear;
use App\Models\Semester;
use Livewire\Component;
use Livewire\WithPagination;

class FeeStructureManager extends Component
{
    use WithPagination;

    public $fee_type_id;
    public $college_class_id;
    public $academic_year_id;
    public $semester_id;
    public $amount;
    public $is_mandatory = true;
    public $is_active = true;
    public $editingFeeStructureId = null;
    public $feeStructureIdToDelete = null;
    public $search = '';
    
    public $selectedClass = '';
    public $selectedYear = '';
    public $selectedSemester = '';

    protected $rules = [
        'fee_type_id' => 'required|exists:fee_types,id',
        'college_class_id' => 'required|exists:college_classes,id',
        'academic_year_id' => 'required|exists:academic_years,id',
        'semester_id' => 'required|exists:semesters,id',
        'amount' => 'required|numeric|min:0',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function render()
    {
        $query = FeeStructure::query()
            ->with(['feeType', 'collegeClass', 'academicYear', 'semester']);
        
        if ($this->search) {
            $query->whereHas('feeType', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->selectedClass) {
            $query->where('college_class_id', $this->selectedClass);
        }
        
        if ($this->selectedYear) {
            $query->where('academic_year_id', $this->selectedYear);
        }
        
        if ($this->selectedSemester) {
            $query->where('semester_id', $this->selectedSemester);
        }
        
        $feeStructures = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $feeTypes = FeeType::where('is_active', true)->orderBy('name')->get();
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('livewire.finance.fee-structure-manager', [
            'feeStructures' => $feeStructures,
            'feeTypes' => $feeTypes,
            'collegeClasses' => $collegeClasses,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
        ])
            ->layout('components.default.layout');
    }

    public function resetFilters()
    {
        $this->selectedClass = '';
        $this->selectedYear = '';
        $this->selectedSemester = '';
        $this->search = '';
    }

    public function saveFeeStructure()
    {
        if ($this->editingFeeStructureId) {
            return $this->updateFeeStructure();
        }

        $this->validate();
        
        // Check for duplicate fee structure
        $existingStructure = FeeStructure::where('fee_type_id', $this->fee_type_id)
            ->where('college_class_id', $this->college_class_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester_id', $this->semester_id)
            ->first();
            
        if ($existingStructure) {
            session()->flash('error', 'A fee structure with these details already exists.');
            return;
        }

        FeeStructure::create([
            'fee_type_id' => $this->fee_type_id,
            'college_class_id' => $this->college_class_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'amount' => $this->amount,
            'is_mandatory' => $this->is_mandatory,
            'is_active' => $this->is_active,
        ]);

        $this->resetInputFields();
        session()->flash('message', 'Fee structure created successfully.');
    }

    public function editFeeStructure($id)
    {
        $feeStructure = FeeStructure::findOrFail($id);
        $this->editingFeeStructureId = $id;
        $this->fee_type_id = $feeStructure->fee_type_id;
        $this->college_class_id = $feeStructure->college_class_id;
        $this->academic_year_id = $feeStructure->academic_year_id;
        $this->semester_id = $feeStructure->semester_id;
        $this->amount = $feeStructure->amount;
        $this->is_mandatory = $feeStructure->is_mandatory;
        $this->is_active = $feeStructure->is_active;
    }

    public function updateFeeStructure()
    {
        $this->validate();

        // Check for duplicate fee structure (excluding current record)
        $existingStructure = FeeStructure::where('fee_type_id', $this->fee_type_id)
            ->where('college_class_id', $this->college_class_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester_id', $this->semester_id)
            ->where('id', '!=', $this->editingFeeStructureId)
            ->first();
            
        if ($existingStructure) {
            session()->flash('error', 'A fee structure with these details already exists.');
            return;
        }

        $feeStructure = FeeStructure::findOrFail($this->editingFeeStructureId);
        $feeStructure->update([
            'fee_type_id' => $this->fee_type_id,
            'college_class_id' => $this->college_class_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,
            'amount' => $this->amount,
            'is_mandatory' => $this->is_mandatory,
            'is_active' => $this->is_active,
        ]);

        $this->resetInputFields();
        session()->flash('message', 'Fee structure updated successfully.');
    }

    public function confirmFeeStructureDeletion($id)
    {
        $this->feeStructureIdToDelete = $id;
        $this->dispatch('show-delete-modal');
    }

    public function deleteFeeStructure()
    {
        $feeStructure = FeeStructure::findOrFail($this->feeStructureIdToDelete);
        
        // Check if fee structure has any student billings
        if ($feeStructure->studentBillings()->count() > 0) {
            session()->flash('error', 'Fee structure cannot be deleted because it is associated with student billings.');
            return;
        }

        $feeStructure->delete();
        session()->flash('message', 'Fee structure deleted successfully.');
    }

    public function resetInputFields()
    {
        $this->fee_type_id = null;
        $this->college_class_id = null;
        $this->academic_year_id = null;
        $this->semester_id = null;
        $this->amount = null;
        $this->is_mandatory = true;
        $this->is_active = true;
        $this->editingFeeStructureId = null;
        $this->resetErrorBag();
    }

    public function cancelEdit()
    {
        $this->resetInputFields();
    }
}