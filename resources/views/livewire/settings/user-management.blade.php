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
                <input type="text" class="form-control ps-8" wire:model.debounce.300ms="search" placeholder="Search users...">
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
        <div>
            <button type="button" class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus-circle me-2"></i> Add User
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-row-bordered table-hover">
            <thead class="table-light">
                <tr class="fw-bold fs-6 text-gray-800">
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
                    <th class="text-end min-w-100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
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
                                <span class="badge badge-light-primary">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-light-info">{{ $user->permissions->count() }} direct permissions</span>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-inline-flex">
                                <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" wire:click="editUser({{ $user->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" wire:click="confirmDelete({{ $user->id }})"
                                    {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">No users found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $users->links() }}
    </div>

    <!-- Modal for Adding/Editing User -->
    <div class="modal fade" id="userFormModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editMode ? 'Edit User' : 'Add New User' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveUser">
                        <span wire:loading.remove wire:target="saveUser">Save</span>
                        <span wire:loading wire:target="saveUser">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

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
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the Bootstrap modal elements
            const userFormModalEl = document.getElementById('userFormModal');
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');
            
            // Create modal instances
            const userFormModal = new bootstrap.Modal(userFormModalEl);
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
                // Handle userDataLoaded event - show modal when data is ready
                Livewire.on('userDataLoaded', () => {
                    console.log('User data loaded event received');
                    setTimeout(() => {
                        userFormModal.show();
                    }, 100);
                });
                
                // Create button click handler
                const createButton = document.querySelector('button[wire\\:click="openModal"]');
                if (createButton) {
                    createButton.addEventListener('click', () => {
                        setTimeout(() => {
                            userFormModal.show();
                        }, 100);
                    });
                }
                
                // Close modal event handler
                Livewire.on('closeModal', () => {
                    userFormModal.hide();
                });
                
                // Delete confirmation modal handler
                Livewire.on('showDeleteConfirmation', () => {
                    deleteConfirmationModal.show();
                });
                
                // Modal hidden event - reset component state
                userFormModalEl.addEventListener('hidden.bs.modal', () => {
                    Livewire.dispatch('closeModalAction');
                });
                
                // Handle modal state changes triggered by Livewire
                Livewire.on('userModalStateChanged', (state) => {
                    if (state.isOpen && !userFormModalEl.classList.contains('show')) {
                        // Only show if not already shown
                        setTimeout(() => userFormModal.show(), 100);
                    } else if (!state.isOpen && userFormModalEl.classList.contains('show')) {
                        userFormModal.hide();
                    }
                });
            });
            
            // Debug modal content when shown
            userFormModalEl.addEventListener('shown.bs.modal', () => {
                console.log('Modal shown, roles selected:', @this.selectedRoles);
                console.log('Modal shown, permissions selected:', @this.selectedPermissions);
                
                // Ensure form elements are properly populated
                if (@this.editMode) {
                    // Explicitly set form field values from component properties
                    document.getElementById('name').value = @this.name;
                    document.getElementById('email').value = @this.email;
                    if (document.getElementById('phone')) {
                        document.getElementById('phone').value = @this.phone || '';
                    }
                    
                    // Check the appropriate role checkboxes
                    const roleIds = @this.selectedRoles || [];
                    roleIds.forEach(id => {
                        const checkbox = document.getElementById(`role_${id}`);
                        if (checkbox) checkbox.checked = true;
                    });
                    
                    // Check the appropriate permission checkboxes
                    const permissionIds = @this.selectedPermissions || [];
                    permissionIds.forEach(id => {
                        const checkbox = document.getElementById(`perm_${id}`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            });
        });
    </script>
    @endpush
</div>