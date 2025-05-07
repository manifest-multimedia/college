<?php

namespace App\Livewire\Settings;

use App\Models\Department;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentManagement extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Form properties
    public $departmentId = null;
    public $name;
    public $code;
    public $description;
    public $is_active = true;
    
    public $isOpen = false;
    public $editMode = false;
    public $viewMode = false;
    
    protected $listeners = [
        'deleteConfirmed' => 'deleteDepartment',
        'editDepartment' => 'editDepartment',
        'viewDepartment' => 'viewDepartment',
        'closeModalAction' => 'closeModal'
    ];
    
    protected $rules = [
        'name' => 'required|string|max:100',
        'code' => 'nullable|string|max:20|unique:departments,code',
        'description' => 'nullable|string|max:255',
        'is_active' => 'boolean',
    ];
    
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    public function openModal($mode = 'add')
    {
        $this->resetValidation();
        $this->reset(['departmentId', 'name', 'code', 'description']);
        $this->is_active = true;
        
        $this->editMode = $mode === 'edit';
        $this->viewMode = $mode === 'view';
        $this->isOpen = true;
        
        // Dispatch event to notify JavaScript to open modal
        $this->dispatch('modalStateChanged', ['isOpen' => true]);
        
        // Also dispatch departmentDataLoaded event for consistency with edit and view flows
        $this->dispatch('departmentDataLoaded');
        
        // Log that openModal was called
        Log::info('openModal called', ['mode' => $mode]);
    }
    
    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['departmentId', 'name', 'code', 'description', 'editMode', 'viewMode']);
        
        // Dispatch event to notify JavaScript to close modal
        $this->dispatch('closeModal');
    }
    
    public function editDepartment($id)
    {
        try {
            // Load the department
            $department = Department::findOrFail($id);
            
            // Reset form values first
            $this->reset(['name', 'code', 'description', 'is_active']);
            
            // Set component properties
            $this->departmentId = $department->id;
            $this->name = $department->name;
            $this->code = $department->code;
            $this->description = $department->description;
            $this->is_active = $department->is_active;
            
            // Log the data loading process
            Log::info('Department data loaded for editing', [
                'department_id' => $department->id,
                'name' => $department->name
            ]);
            
            // Set modal state
            $this->editMode = true;
            $this->viewMode = false;
            $this->isOpen = true;
            
            // Dispatch events
            $this->dispatch('modalStateChanged', ['isOpen' => true]);
            $this->dispatch('departmentDataLoaded');
            
        } catch (\Exception $e) {
            Log::error('Error editing department: ' . $e->getMessage(), [
                'department_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to load department for editing.');
        }
    }
    
    public function viewDepartment($id)
    {
        try {
            // Load the department
            $department = Department::findOrFail($id);
            
            // Reset form values first
            $this->reset(['name', 'code', 'description', 'is_active']);
            
            // Set component properties
            $this->departmentId = $department->id;
            $this->name = $department->name;
            $this->code = $department->code;
            $this->description = $department->description;
            $this->is_active = $department->is_active;
            
            // Log the data loading process
            Log::info('Department data loaded for viewing', [
                'department_id' => $department->id,
                'name' => $department->name
            ]);
            
            // Set modal state
            $this->editMode = false;
            $this->viewMode = true;
            $this->isOpen = true;
            
            // Dispatch events
            $this->dispatch('modalStateChanged', ['isOpen' => true]);
            $this->dispatch('departmentDataLoaded');
            
        } catch (\Exception $e) {
            Log::error('Error viewing department: ' . $e->getMessage(), [
                'department_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to load department details.');
        }
    }
    
    public function saveDepartment()
    {
        // Skip validation in view mode
        if (!$this->viewMode) {
            $validatedData = $this->validate();
        }
        
        try {
            if ($this->editMode) {
                $department = Department::findOrFail($this->departmentId);
                
                // Update unique validation rule for code on edit
                if ($department->code !== $this->code) {
                    $this->validate([
                        'code' => 'nullable|string|max:20|unique:departments,code',
                    ]);
                }
                
                $department->name = $this->name;
                $department->code = $this->code;
                $department->description = $this->description;
                $department->is_active = $this->is_active;
                $department->save();
                
                session()->flash('success', 'Department updated successfully.');
            } else {
                Department::create([
                    'name' => $this->name,
                    'code' => $this->code,
                    'description' => $this->description,
                    'is_active' => $this->is_active,
                ]);
                
                session()->flash('success', 'Department created successfully.');
            }
            
            $this->closeModal();
        } catch (\Exception $e) {
            Log::error('Error saving department: ' . $e->getMessage());
            session()->flash('error', 'Failed to save department. Please try again later.');
        }
    }
    
    public function confirmDelete($id)
    {
        $this->departmentId = $id;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function deleteDepartment()
    {
        try {
            $department = Department::findOrFail($this->departmentId);
            
            // Check if department has associated users
            if ($department->users()->count() > 0) {
                session()->flash('error', 'Department cannot be deleted because it has associated users.');
                return;
            }
            
            $department->delete();
            session()->flash('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting department: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete department. Please try again later.');
        }
    }
    
    public function render()
    {
        $departments = Department::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.settings.department-management', [
            'departments' => $departments
        ]);
    }
}
