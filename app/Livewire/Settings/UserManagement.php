<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
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

    public $selectedPermissions = []; // New property for direct permissions

    public $isOpen = false;

    public $editMode = false;

    protected $listeners = [
        'deleteConfirmed' => 'deleteUser',
        'editUser' => 'editUser',
        'closeModalAction' => 'closeModal',
    ];

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'selectedRoles' => 'required|array|min:1',
            'selectedPermissions' => 'nullable|array',
        ];

        if (! $this->editMode) {
            // Only require password for new users
            $rules['password'] = 'required|min:8';
            $rules['passwordConfirmation'] = 'required|same:password';
        } elseif ($this->password) {
            // If editing and password is provided (optional)
            $rules['password'] = 'nullable|min:8';
            $rules['passwordConfirmation'] = 'nullable|same:password';
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
        $this->reset(['name', 'email', 'phone', 'password', 'passwordConfirmation', 'selectedRoles', 'selectedPermissions']);

        $this->editMode = $mode === 'edit';
        $this->isOpen = true;

        // Dispatch event to notify JavaScript to open modal
        $this->dispatch('userModalStateChanged', ['isOpen' => true]);
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['userId', 'name', 'email', 'phone', 'password', 'passwordConfirmation', 'selectedRoles', 'selectedPermissions', 'editMode']);

        // Dispatch event to notify JavaScript to close modal
        $this->dispatch('closeModal');
    }

    public function editUser($id)
    {
        try {
            // Get user with both roles and permissions
            $user = User::with(['roles', 'permissions'])->findOrFail($id);

            // Set user form data
            $this->userId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->password = '';
            $this->passwordConfirmation = '';

            // Get role and permission IDs
            $this->selectedRoles = $user->roles->pluck('id')->toArray();
            $this->selectedPermissions = $user->permissions->pluck('id')->toArray();

            // Log loading process for debugging
            Log::info('Loading user data for editing', [
                'user_id' => $id,
                'name' => $this->name,
                'roles_count' => count($this->selectedRoles),
                'permissions_count' => count($this->selectedPermissions),
            ]);

            // Set modal state
            $this->editMode = true;
            $this->isOpen = true;

            // Dispatch events to signal data is loaded and modal should open
            $this->dispatch('modalStateChanged', ['isOpen' => true]);
            $this->dispatch('userDataLoaded');

        } catch (\Exception $e) {
            Log::error('Error editing user: '.$e->getMessage(), [
                'user_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
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

                // Use the Role class to ensure we're assigning by ID correctly
                $roles = Role::whereIn('id', $this->selectedRoles)->get();
                $user->syncRoles($roles);

                // Sync direct permissions - only get valid permission objects
                $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                $user->syncPermissions($permissions);

                // Log successful update using proper Laravel 12 logging
                \Illuminate\Support\Facades\Log::info('User updated successfully', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                ]);

                session()->flash('success', 'User updated successfully.');
            } else {
                $user = User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'password' => Hash::make($this->password),
                ]);

                // Use the Role class to ensure we're assigning by ID correctly
                $roles = Role::whereIn('id', $this->selectedRoles)->get();
                $user->syncRoles($roles);

                // Sync direct permissions - only get valid permission objects
                if (! empty($this->selectedPermissions)) {
                    $permissions = Permission::whereIn('id', $this->selectedPermissions)->get();
                    $user->syncPermissions($permissions);
                }

                // Log successful creation using proper Laravel 12 logging
                \Illuminate\Support\Facades\Log::info('User created successfully', [
                    'user_id' => $user->id,
                    'name' => $user->name,
                ]);

                session()->flash('success', 'User added successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error saving user: '.$e->getMessage(), [
                'user_id' => $this->userId ?? 'new',
                'trace' => $e->getTraceAsString(),
            ]);
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
            Log::error('Error deleting user: '.$e->getMessage());
            session()->flash('error', 'Failed to delete user. Please try again later.');
        }
    }

    public function render()
    {
        $roles = Role::all();
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group permissions by category (similar to RoleManagement)
            $name = $permission->name;

            if (strpos($name, '-') !== false) {
                return ucwords(strtolower(explode('-', $name)[0]));
            } elseif (strpos($name, '.') !== false) {
                return ucwords(strtolower(explode('.', $name)[0]));
            } else {
                $words = preg_split('/(?=[A-Z])/', $name);

                return ucwords(strtolower($words[0]));
            }
        });

        $users = User::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
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
            'permissionGroups' => $permissions,
        ]);
    }
}
