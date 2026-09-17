<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
        <div class="d-flex flex-column flex-md-row gap-3 mb-3 mb-md-0">
            <div class="d-flex position-relative me-md-2">
                <span class="position-absolute top-50 translate-middle-y ms-3">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control ps-8" wire:model.live="search" placeholder="Search users...">
            </div>
            <div>
                <select class="form-select" wire:model.live="roleFilter">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(count($selectedUsers) > 0 && auth()->user()->hasRole('System'))
                <button type="button" class="btn btn-warning" wire:click="openStaffPasswordReset('selected')">
                    <i class="fas fa-key me-1"></i> Reset Selected ({{ count($selectedUsers) }})
                </button>
            @endif
            @if(auth()->user()->hasRole('System'))
                <div class="dropdown">
                    <button class="btn btn-light-warning border border-warning text-warning-dark" type="button" id="staffResetDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-key me-1"></i> Reset Passwords <i class="fas fa-chevron-down ms-1 fs-8"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-gray-200 fs-7" aria-labelledby="staffResetDropdown">
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="#" wire:click.prevent="openStaffPasswordReset('all')">
                                <i class="fas fa-user-shield me-2 fs-7"></i>Reset All Staff Passwords
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
            <button type="button" class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus-circle me-2"></i> Add User
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-row-bordered table-hover">
            <thead class="table-light">
                <tr class="fw-bold fs-6 text-gray-800">
                    @role('System')
                        <th style="width: 40px;" class="text-center align-middle">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAllUsers">
                        </th>
                    @endrole
                    <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-125px">
                        Name
                        @if($sortField === 'name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th wire:click="sortBy('email')" style="cursor: pointer;" class="min-w-125px">
                        Email
                        @if($sortField === 'email')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th class="min-w-125px">Phone</th>
                    <th class="min-w-150px">Role(s)</th>
                    <th class="min-w-150px">Direct Permissions</th>
                    <th class="text-end min-w-120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        @role('System')
                            <td class="text-center align-middle">
                                <input type="checkbox" class="form-check-input" value="{{ $user->id }}" wire:model.live="selectedUsers">
                            </td>
                        @endrole
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <div class="symbol-label bg-light-primary text-primary">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <span class="fw-bold d-block">{{ $user->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">{{ $user->email }}</td>
                        <td class="align-middle">{{ $user->phone ?? 'N/A' }}</td>
                        <td class="align-middle">
                            @foreach($user->roles as $role)
                                @if($role->name !== 'System' || auth()->user()->hasRole('System'))
                                    <span class="badge badge-light-primary">{{ $role->name }}</span>
                                @endif
                            @endforeach
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-light-info">{{ $user->permissions->count() }} direct permissions</span>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                @role('System')
                                    <button type="button" class="btn btn-sm btn-icon btn-light-warning" title="Reset Password" wire:click="openStaffPasswordReset('individual', {{ $user->id }})">
                                        <i class="fas fa-key"></i>
                                    </button>
                                @endrole
                                <button type="button" class="btn btn-sm btn-icon btn-light-primary" wire:click="editUser({{ $user->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" wire:click="confirmDelete({{ $user->id }})"
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                                @role('System')
                                @if($user->id !== auth()->id())
                                <form action="{{ route('impersonate.start', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-icon btn-light-secondary" title="Impersonate User">
                                        <i class="fas fa-user-secret"></i>
                                    </button>
                                </form>
                                @endif
                                @endrole
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $users->links() }}
    </div>

    <!-- Modal for Adding/Editing User -->
    <div class="modal fade {{ $isOpen ? 'show d-block' : '' }}" id="userFormModal" tabindex="-1"
        aria-hidden="{{ $isOpen ? 'false' : 'true' }}" aria-modal="{{ $isOpen ? 'true' : 'false' }}"
        style="{{ $isOpen ? 'background-color: rgba(0, 0, 0, 0.5);' : '' }}">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit User' : 'Add New User' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Full Name</label>
                                    <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">Email Address</label>
                                    <input type="email" wire:model="email" id="email" class="form-control @error('email') is-invalid @enderror">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone Number</label>
                                    <input type="text" wire:model="phone" id="phone" class="form-control @error('phone') is-invalid @enderror">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">
                                        Password {{ $editMode ? '(leave blank to keep current)' : '' }}
                                    </label>
                                    <input type="password" wire:model="password" id="password" class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
                                    <input type="password" wire:model="passwordConfirmation" id="password_confirmation" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Roles</label>
                            @error('selectedRoles')
                                <div class="text-danger mb-2">{{ $message }}</div>
                            @enderror
                            <div class="row">
                                @foreach($roles as $role)
                                    <div class="col-md-3 col-sm-4 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="selectedRoles" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                            <label class="form-check-label" for="role_{{ $role->id }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Direct Permissions -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Direct Permissions (Optional)</label>
                            <p class="text-muted">These permissions will be assigned directly to the user, in addition to any permissions from their roles</p>
                            @error('selectedPermissions')
                                <div class="text-danger mb-2">{{ $message }}</div>
                            @enderror
                            
                            <div class="accordion" id="permissionsAccordion">
                                @foreach($permissionGroups as $groupName => $permissions)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ \Str::slug($groupName) }}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ \Str::slug($groupName) }}" aria-expanded="false" 
                                                aria-controls="collapse{{ \Str::slug($groupName) }}">
                                                <div class="d-flex justify-content-between w-100 align-items-center">
                                                    <span>{{ $groupName }} Permissions</span>
                                                    <span class="badge bg-primary rounded-pill ms-2">{{ count($permissions) }}</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ \Str::slug($groupName) }}" class="accordion-collapse collapse" 
                                            aria-labelledby="heading{{ \Str::slug($groupName) }}" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    @foreach($permissions as $permission)
                                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    wire:model="selectedPermissions" 
                                                                    value="{{ $permission->id }}" 
                                                                    id="perm_{{ $permission->id }}">
                                                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                    {{ ucfirst(str_replace(['-', '.', '_'], ' ', $permission->name)) }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveUser" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveUser">Save</span>
                        <span wire:loading wire:target="saveUser">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($isOpen)
        <div class="modal-backdrop fade show"></div>
    @endif

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteUser" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="deleteUser">Delete</span>
                        <span wire:loading wire:target="deleteUser">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    <!-- Staff Password Reset Modal -->
    @if($showPasswordResetModal)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.55); z-index: 1060;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning py-3 px-4 text-dark border-0">
                    <h5 class="modal-title fw-bold fs-5 d-flex align-items-center">
                        <i class="fas fa-key me-2 text-dark"></i>
                        @if($resetScope === 'individual')
                            Reset Password: {{ $resetTargetUserName }} ({{ $resetTargetUserEmail }})
                        @elseif($resetScope === 'selected')
                            Reset Passwords for {{ $resetTargetCount }} Selected Staff Members
                        @elseif($resetScope === 'all')
                            Reset Passwords for All Staff Members ({{ $resetTargetCount }} staff)
                        @endif
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeStaffPasswordReset"></button>
                </div>

                <div class="modal-body p-4">
                    @if($resetSummary)
                        <!-- Summary View after execution -->
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="fas fa-check-circle fs-2 me-3 text-success"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Password Reset Completed</h6>
                                <p class="mb-0 fs-7 text-muted">Summary of the staff credential reset operation:</p>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="bg-light p-3 rounded-2 text-center border">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Targeted</span>
                                    <span class="fs-4 fw-bold text-gray-900">{{ $resetSummary['total'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-success p-3 rounded-2 text-center border border-success border-opacity-25">
                                    <span class="fs-8 text-success d-block text-uppercase fw-semibold">Processed</span>
                                    <span class="fs-4 fw-bold text-success">{{ $resetSummary['processed'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-primary p-3 rounded-2 text-center border border-primary border-opacity-25">
                                    <span class="fs-8 text-primary d-block text-uppercase fw-semibold">Emails Sent</span>
                                    <span class="fs-4 fw-bold text-primary">{{ $resetSummary['emails_sent'] ?? 0 }}</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-info p-3 rounded-2 text-center border border-info border-opacity-25">
                                    <span class="fs-8 text-info d-block text-uppercase fw-semibold">SMS Sent</span>
                                    <span class="fs-4 fw-bold text-info">{{ $resetSummary['sms_sent'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Form Configuration View -->
                        <div class="alert alert-light-warning border border-warning border-opacity-50 p-3 mb-4 rounded-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-warning fs-3 me-3"></i>
                                <div class="fs-7 text-gray-800">
                                    <strong>System Administrator Action:</strong> You are about to reset staff account password credentials. This will overwrite existing passwords and send updated credentials through the selected channels.
                                </div>
                            </div>
                        </div>

                        <!-- Password Generation Option -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-800 fs-7">Temporary Password Setting</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="staff_pwd_random" value="random" wire:model.live="resetPasswordMode">
                                    <label class="form-check-label fs-7 fw-medium" for="staff_pwd_random">
                                        Auto-generate secure random temporary password <span class="badge bg-light text-muted ms-1">Recommended</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="staff_pwd_custom" value="custom" wire:model.live="resetPasswordMode">
                                    <label class="form-check-label fs-7 fw-medium" for="staff_pwd_custom">
                                        Specify a uniform temporary password
                                    </label>
                                </div>
                            </div>

                            @if($resetPasswordMode === 'custom')
                                <div class="mt-2" style="max-width: 320px;">
                                    <input type="text" class="form-control form-control-sm @error('resetCustomPassword') is-invalid @enderror" 
                                           placeholder="Enter temporary password (min 8 chars)" 
                                           wire:model="resetCustomPassword">
                                    @error('resetCustomPassword')
                                        <div class="text-danger fs-8 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <!-- Notification Channel Option -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-800 fs-7">Credential Notification Channel</label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 @if($resetChannel === 'both') bg-light-primary border-primary @endif">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_both" value="both" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_both">
                                            <i class="fas fa-paper-plane text-primary me-1"></i> Both Email & SMS
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 @if($resetChannel === 'email') bg-light-primary border-primary @endif">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_email" value="email" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_email">
                                            <i class="fas fa-envelope text-primary me-1"></i> Email Only
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 @if($resetChannel === 'sms') bg-light-primary border-primary @endif">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_sms" value="sms" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_sms">
                                            <i class="fas fa-comment-dots text-info me-1"></i> SMS Only
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 @if($resetChannel === 'none') bg-light-secondary @endif">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_none" value="none" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_none">
                                            <i class="fas fa-bell-slash text-muted me-1"></i> Do Not Send (Manual)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Force Password Change Toggle -->
                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="staff_force_change" wire:model="resetRequirePasswordChange">
                                <label class="form-check-label fs-7 fw-bold text-gray-800" for="staff_force_change">
                                    Require password change upon initial login
                                </label>
                            </div>
                            <div class="text-muted fs-8 ms-6">
                                When enabled, staff will be redirected to choose a new secure password on next login.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="closeStaffPasswordReset">
                        {{ $resetSummary ? 'Close' : 'Cancel' }}
                    </button>
                    @if(! $resetSummary)
                        <button type="button" class="btn btn-warning text-dark fw-bold px-4" 
                                wire:click="executeStaffPasswordReset" 
                                wire:loading.attr="disabled">
                            <i class="fas fa-check-circle me-1.5" wire:loading.remove wire:target="executeStaffPasswordReset"></i>
                            <span wire:loading.remove wire:target="executeStaffPasswordReset">Confirm & Reset Passwords</span>
                            <span wire:loading wire:target="executeStaffPasswordReset">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');

            const deleteConfirmationModal = new bootstrap.Modal(deleteConfirmationModalEl);
            
            // Fix for permissions and roles checkboxes - stop propagation to prevent accordion issues
            document.addEventListener('click', function(e) {
                if (e.target && (e.target.matches('[wire\\:model="selectedPermissions"]') || 
                                e.target.matches('[wire\\:model="selectedRoles"]'))) {
                    e.stopPropagation();
                }
            }, true);
            
            // Wait for Livewire to be fully initialized
            document.addEventListener('livewire:initialized', () => {
                // Delete confirmation modal handler
                Livewire.on('showDeleteConfirmation', () => {
                    deleteConfirmationModal.show();
                });
            });
        });
    </script>
    @endpush
</div>
