<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserManagement extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $roleFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Form properties for add/edit user
    public $userId = null;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $passwordConfirmation;
    public $selectedRoles = [];
    
    public $isOpen = false;
    public $editMode = false;
    
    protected $listeners = ['deleteConfirmed' => 'deleteUser'];
    
    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'selectedRoles' => 'required|array|min:1',
        ];
        
        if (!$this->editMode || $this->password) {
            $rules['password'] = 'required|min:8|confirmed';
        }
        
        return $rules;
    }
    
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingRoleFilter()
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
        $this->reset(['name', 'email', 'phone', 'password', 'passwordConfirmation', 'selectedRoles']);
        
        $this->editMode = $mode === 'edit';
        $this->isOpen = true;
    }
    
    public function closeModal()
    {
        $this->isOpen = false;
    }
    
    public function editUser($id)
    {
        try {
            $user = User::findOrFail($id);
            
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->password = '';
            $this->passwordConfirmation = '';
            $this->selectedRoles = $user->roles->pluck('id')->toArray();
            
            $this->openModal('edit');
        } catch (\Exception $e) {
            Log::error('Error editing user: ' . $e->getMessage());
            session()->flash('error', 'Failed to load user information for editing.');
        }
    }
    
    public function saveUser()
    {
        $this->validate();
        
        try {
            if ($this->editMode) {
                $user = User::findOrFail($this->userId);
                
                $user->name = $this->name;
                $user->email = $this->email;
                $user->phone = $this->phone;
                
                if ($this->password) {
                    $user->password = Hash::make($this->password);
                }
                
                $user->save();
                
                // Sync roles
                $user->syncRoles($this->selectedRoles);
                
                session()->flash('success', 'User updated successfully.');
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($this->password),
                ]);
                
                // Assign roles
                $user->syncRoles($this->selectedRoles);
                
                session()->flash('success', 'User added successfully.');
            }
            
            $this->closeModal();
        } catch (\Exception $e) {
            Log::error('Error saving user: ' . $e->getMessage());
            session()->flash('error', 'Failed to save user. Please try again later.');
        }
    }
    
    public function confirmDelete($id)
    {
        $this->userId = $id;
        $this->dispatch('showDeleteConfirmation');
    }
    
    public function deleteUser()
    {
        try {
            $user = User::findOrFail($this->userId);
            
            // Check if attempting to delete self
            if ($user->id === auth()->id()) {
                session()->flash('error', 'You cannot delete your own account.');
                return;
            }
            
            $user->delete();
            session()->flash('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            session()->flash('error', 'Failed to delete user. Please try again later.');
        }
    }
    
    public function render()
    {
        $roles = Role::all();
        
        $users = User::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                return $query->whereHas('roles', function ($q) {
                    $q->where('id', $this->roleFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
            
        return view('livewire.settings.user-management', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}