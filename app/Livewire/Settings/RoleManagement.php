<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleManagement extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Form properties for add/edit role
    public $roleId = null;
    public $name;
    public $description;
    public $selectedPermissions = [];
    
    public $isOpen = false;
    public $editMode = false;
    public $viewMode = false;
    
    protected $listeners = [
        'deleteConfirmed' => 'deleteRole',
        'editRole' => 'editRole',
        'viewRole' => 'viewRole',
        'closeModalAction' => 'closeModal'
    ];
    
    protected $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:255',
        'selectedPermissions' => 'array',
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
        $this->reset(['name', 'description', 'selectedPermissions']);
        
        $this->editMode = $mode === 'edit';
        $this->viewMode = $mode === 'view';
        $this->isOpen = true;
        
        // Dispatch event to notify JavaScript to open modal
        $this->dispatch('modalStateChanged', ['isOpen' => true]);
    }
    
    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['roleId', 'name', 'description', 'selectedPermissions', 'editMode', 'viewMode']);
        
        // Dispatch event to notify JavaScript to close modal
        $this->dispatch('closeModal');
    }
    
    public function editRole($id)
    {
        try {
            // Load the role with all its permissions
            $role = Role::with('permissions')->findOrFail($id);
            
            // Reset form values first
            $this->reset(['name', 'description', 'selectedPermissions']);
            
            // Set component properties
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->description = $role->description ?? '';
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
            
            // Log the data loading process according to Laravel 12 standards
            \Illuminate\Support\Facades\Log::info('Role data loaded for editing', [
                'role_id' => $role->id,
                'name' => $role->name,
                'permissions_count' => count($this->selectedPermissions)
            ]);
            
            // Set modal state
            $this->editMode = true;
            $this->viewMode = false;
            $this->isOpen = true;
            
            // Dispatch events
            $this->dispatch('modalStateChanged', ['isOpen' => true]);
            
            // Add a small delay to ensure Livewire state is updated before showing modal
            $this->dispatch('roleDataLoaded');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error editing role: ' . $e->getMessage(), [
                'role_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to load role for editing.');
        }
    }
    
    public function viewRole($id)
    {
        try {
            // Load the role with all its permissions
            $role = Role::with('permissions')->findOrFail($id);
            
            // Reset form values first
            $this->reset(['name', 'description', 'selectedPermissions']);
            
            // Set component properties
            $this->roleId = $role->id;
            $this->name = $role->name;
            $this->description = $role->description ?? '';
            $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
            
            // Log the data loading process according to Laravel 12 standards
            \Illuminate\Support\Facades\Log::info('Role data loaded for viewing', [
                'role_id' => $role->id,
                'name' => $role->name,
                'permissions_count' => count($this->selectedPermissions)
            ]);
            
            // Set modal state
            $this->editMode = false;
            $this->viewMode = true;
            $this->isOpen = true;
            
            // Dispatch events
            $this->dispatch('modalStateChanged', ['isOpen' => true]);
            
            // Add a small delay to ensure Livewire state is updated before showing modal
            $this->dispatch('roleDataLoaded');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error viewing role: ' . $e->getMessage(), [
                'role_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to load role details.');
        }
    }
    
    public function saveRole()
    {
        // Skip validation in view mode
        if (!$this->viewMode) {
            $this->validate();
        }
        
        try {
            if ($this->editMode) {
                $role = Role::findOrFail($this->roleId);
                
                // Check if this is a system role
                if (in_array($role->name, ['Super Admin', 'Administrator'])) {
                    // Allow updating permissions but not the name for system roles
                    if ($role->name !== $this->name) {
                        session()->flash('error', 'System role names cannot be changed.');
                        return;
                    }
                }
                
                $role->name = $this->name;
                $role->description = $this->description;
                $role->save();
                
                // Sync permissions
                $role->syncPermissions($this->selectedPermissions);
                
                session()->flash('success', 'Role updated successfully.');
            } else {
                $role = Role::create([
                    'name' => $this->name,
                    'description' => $this->description,
                    'guard_name' => 'web',
                ]);
                
                // Assign permissions
                $role->syncPermissions($this->selectedPermissions);
                
                session()->flash('success', 'Role created successfully.');
            }
            
            $this->closeModal();
        } catch (\Exception $e) {
            Log::error('Error saving role: ' . $e->getMessage());
            session()->flash('error', 'Failed to save role. Please try again later.');
        }
    }
    
    public function confirmDelete($id)
    {
        $this->roleId = $id;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function deleteRole()
    {
        try {
            $role = Role::findOrFail($this->roleId);
            
            // Prevent deletion of system roles
            if (in_array($role->name, ['Super Admin', 'Administrator', 'Student', 'Lecturer'])) {
                session()->flash('error', 'System roles cannot be deleted.');
                return;
            }
            
            // Check if role has users
            if ($role->users()->count() > 0) {
                session()->flash('error', 'Role cannot be deleted because it is assigned to users.');
                return;
            }
            
            $role->delete();
            session()->flash('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting role: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete role. Please try again later.');
        }
    }
    
    public function render()
    {
        // Get all permissions grouped by category
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Convert from camelCase or snake_case to readable format
            $name = $permission->name;
            
            // Extract category (text before first hyphen or period)
            if (strpos($name, '-') !== false) {
                return ucwords(strtolower(explode('-', $name)[0]));
            } elseif (strpos($name, '.') !== false) {
                return ucwords(strtolower(explode('.', $name)[0]));
            } else {
                // Default case: use the first word
                $words = preg_split('/(?=[A-Z])/', $name);
                return ucwords(strtolower($words[0]));
            }
        });
        
        $roles = Role::query()
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.settings.role-management', [
            'roles' => $roles,
            'permissionGroups' => $permissions,
        ]);
    }
}