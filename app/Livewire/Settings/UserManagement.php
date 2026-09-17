<?php

namespace App\Livewire\Settings;

use App\Jobs\ProcessBatchPasswordResetJob;
use App\Models\User;
use App\Services\PasswordResetManagementService;
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

    public $selectedUsers = [];

    public $selectAllUsers = false;

    // Staff Password Reset properties
    public $showPasswordResetModal = false;

    public $resetScope = 'individual'; // 'individual', 'selected', 'all'

    public $resetTargetUserId = null;

    public $resetTargetUserName = '';

    public $resetTargetUserEmail = '';

    public $resetTargetCount = 0;

    public $resetPasswordMode = 'random'; // 'random', 'custom'

    public $resetCustomPassword = '';

    public $resetChannel = 'both'; // 'both', 'email', 'sms', 'none'

    public $resetRequirePasswordChange = true;

    public $resetSummary = null;

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

    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['userId', 'name', 'email', 'phone', 'password', 'passwordConfirmation', 'selectedRoles', 'selectedPermissions', 'editMode']);

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

            // Hide System role from selection for non-System users
            if (! auth()->user()->hasRole('System')) {
                $systemRole = Role::where('name', 'System')->first();
                if ($systemRole) {
                    $this->selectedRoles = array_values(array_diff($this->selectedRoles, [$systemRole->id]));
                }
            }

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
            if (! auth()->user()->hasRole('System')) {
                $systemRole = Role::where('name', 'System')->first();
                if ($systemRole) {
                    $this->selectedRoles = array_values(array_diff($this->selectedRoles, [$systemRole->id]));
                    if ($this->editMode) {
                        $existingUser = User::find($this->userId);
                        if ($existingUser && $existingUser->hasRole('System')) {
                            $this->selectedRoles[] = $systemRole->id;
                        }
                    }
                }
            }

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

    public function updatedSelectAllUsers($value)
    {
        if ($value) {
            $this->selectedUsers = $this->getFilteredUsersQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedUsers = [];
        }
    }

    public function updatedSelectedUsers()
    {
        $allIds = $this->getFilteredUsersQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        if (count($allIds) > 0 && count(array_intersect($allIds, $this->selectedUsers)) === count($allIds)) {
            $this->selectAllUsers = true;
        } else {
            $this->selectAllUsers = false;
        }
    }

    private function getFilteredUsersQuery()
    {
        return User::query()
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
            });
    }

    /**
     * Open the password reset modal for staff.
     */
    public function openStaffPasswordReset(string $scope, ?int $userId = null)
    {
        if (! auth()->user() || ! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized. Only System users can reset passwords.');
        }

        $this->resetScope = $scope;
        $this->resetPasswordMode = 'random';
        $this->resetCustomPassword = '';
        $this->resetChannel = 'both';
        $this->resetRequirePasswordChange = true;
        $this->resetSummary = null;

        if ($scope === 'individual' && $userId) {
            $user = User::findOrFail($userId);
            $this->resetTargetUserId = $user->id;
            $this->resetTargetUserName = $user->name;
            $this->resetTargetUserEmail = $user->email;
            $this->resetTargetCount = 1;
        } elseif ($scope === 'selected') {
            $this->resetTargetCount = count($this->selectedUsers);
            if ($this->resetTargetCount === 0) {
                session()->flash('error', 'Please select at least one staff user.');
                return;
            }
        } elseif ($scope === 'all') {
            $this->resetTargetCount = User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['Student', 'Parent']))->count();
        }

        $this->showPasswordResetModal = true;
    }

    /**
     * Close the password reset modal for staff.
     */
    public function closeStaffPasswordReset()
    {
        $this->showPasswordResetModal = false;
        $this->resetScope = 'individual';
        $this->resetTargetUserId = null;
        $this->resetTargetUserName = '';
        $this->resetTargetUserEmail = '';
        $this->resetSummary = null;
    }

    /**
     * Execute staff password reset.
     */
    public function executeStaffPasswordReset(PasswordResetManagementService $service)
    {
        if (! auth()->user() || ! auth()->user()->hasRole('System')) {
            abort(403, 'Unauthorized. Only System users can reset passwords.');
        }

        if ($this->resetPasswordMode === 'custom') {
            $this->validate([
                'resetCustomPassword' => 'required|string|min:8',
            ]);
        }

        $options = [
            'channel' => $this->resetChannel,
            'require_change' => $this->resetRequirePasswordChange,
            'custom_password' => $this->resetPasswordMode === 'custom' ? $this->resetCustomPassword : null,
            'initiated_by' => auth()->id(),
        ];

        try {
            if ($this->resetScope === 'individual') {
                $user = User::findOrFail($this->resetTargetUserId);
                $result = $service->resetStaff($user, $options);

                if ($result['success']) {
                    $channelText = match ($this->resetChannel) {
                        'both' => 'via Email & SMS',
                        'email' => 'via Email',
                        'sms' => 'via SMS',
                        default => 'without notification',
                    };
                    session()->flash('success', "Password for {$this->resetTargetUserName} reset successfully {$channelText}. Temporary password: {$result['temporary_password']}");
                    $this->closeStaffPasswordReset();
                } else {
                    session()->flash('error', $result['message'] ?? 'Failed to reset password.');
                }
            } elseif ($this->resetScope === 'selected') {
                $count = count($this->selectedUsers);
                if ($count > 10) {
                    ProcessBatchPasswordResetJob::dispatch('staff_bulk', $this->selectedUsers, $options);
                    session()->flash('success', "Password reset for {$count} selected staff members has been queued in the background.");
                    $this->closeStaffPasswordReset();
                } else {
                    $summary = $service->resetStaffBulk($this->selectedUsers, $options);
                    $this->resetSummary = $summary;
                    session()->flash('success', "Successfully processed password resets for {$summary['processed']} of {$summary['total']} staff members.");
                }
            } elseif ($this->resetScope === 'all') {
                ProcessBatchPasswordResetJob::dispatch('all_staff', null, $options);
                session()->flash('success', "Password reset for all staff members ({$this->resetTargetCount}) has been queued in the background.");
                $this->closeStaffPasswordReset();
            }
        } catch (\Throwable $e) {
            Log::error('Error executing staff password reset: '.$e->getMessage(), [
                'scope' => $this->resetScope,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'An error occurred while resetting passwords: '.$e->getMessage());
        }
    }

    public function render()
    {
        $roles = Role::query()
            ->when(! auth()->user()->hasRole('System'), function ($q) {
                return $q->where('name', '!=', 'System');
            })
            ->get();
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
