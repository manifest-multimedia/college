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
        <div class="d-flex position-relative me-md-2 mb-3 mb-md-0" style="max-width: 300px;">
            <span class="position-absolute top-50 translate-middle-y ms-3">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" class="form-control ps-8" wire:model.debounce.300ms="search" placeholder="Search roles...">
        </div>
        <div>
            <button type="button" class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus-circle me-2"></i> Create Role
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-row-bordered table-hover">
            <thead class="table-light">
                <tr class="fw-bold fs-6 text-gray-800">
                    <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-125px">
                        Role Name
                        @if($sortField === 'name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                        @endif
                    </th>
                    <th class="min-w-150px">Description</th>
                    <th class="min-w-150px">Permissions</th>
                    <th class="min-w-100px">Users Count</th>
                    <th class="text-end min-w-125px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td class="align-middle fw-bold">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <div class="symbol-label {{ in_array($role->name, ['Super Admin', 'Administrator']) ? 'bg-light-danger text-danger' : 'bg-light-primary text-primary' }}">
                                        {{ strtoupper(substr($role->name, 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    {{ $role->name }}
                                    @if(in_array($role->name, ['Super Admin', 'Administrator', 'Student', 'Lecturer']))
                                        <span class="badge badge-light-warning ms-2">System Role</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">{{ $role->description ?? 'No description' }}</td>
                        <td class="align-middle">
                            <span class="badge badge-light-success">{{ $role->permissions->count() }} permissions</span>
                        </td>
                        <td class="align-middle">{{ $role->users->count() }} users</td>
                        <td class="align-middle text-end">
                            <div class="d-inline-flex">
                                <button type="button" class="btn btn-sm btn-icon btn-light-info me-2" wire:click="viewRole({{ $role->id }})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" wire:click="editRole({{ $role->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" wire:click="confirmDelete({{ $role->id }})"
                                    {{ in_array($role->name, ['Super Admin', 'Administrator', 'Student', 'Lecturer']) ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No roles found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $roles->links() }}
    </div>

    <!-- Modal for Adding/Editing/Viewing Role -->
    <div class="modal fade" id="roleFormModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($viewMode)
                            View Role Details
                        @elseif($editMode)
                            Edit Role
                        @else
                            Create New Role
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="roleForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Role Name</label>
                                    <input type="text" wire:model="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                        {{ $viewMode ? 'disabled' : '' }}
                                        {{ $editMode && in_array($name, ['Super Admin', 'Administrator', 'Student', 'Lecturer']) ? 'disabled' : '' }}>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description (Optional)</label>
                                    <input type="text" wire:model="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                        {{ $viewMode ? 'disabled' : '' }}>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Permissions</label>
                            @error('selectedPermissions')
                                <div class="text-danger mb-2">{{ $message }}</div>
                            @enderror
                            
                            <div class="accordion" id="permissionsAccordion">
                                @foreach($permissionGroups as $groupName => $permissions)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading{{ \Str::slug($groupName) }}">
                                            <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse{{ \Str::slug($groupName) }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" 
                                                aria-controls="collapse{{ \Str::slug($groupName) }}">
                                                <div class="d-flex justify-content-between w-100 align-items-center">
                                                    <span>{{ $groupName }} Permissions</span>
                                                    <span class="badge bg-primary rounded-pill ms-2">{{ count($permissions) }}</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse{{ \Str::slug($groupName) }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                            aria-labelledby="heading{{ \Str::slug($groupName) }}" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    @foreach($permissions as $permission)
                                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    wire:model="selectedPermissions" 
                                                                    value="{{ $permission->id }}" 
                                                                    id="perm_{{ $permission->id }}"
                                                                    {{ $viewMode ? 'disabled' : '' }}>
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
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ $viewMode ? 'Close' : 'Cancel' }}</button>
                    @if(!$viewMode)
                        <button type="button" class="btn btn-primary" wire:click="saveRole">
                            <span wire:loading.remove wire:target="saveRole">Save</span>
                            <span wire:loading wire:target="saveRole">Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Roles with assigned users cannot be deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteRole" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="deleteRole">Delete</span>
                        <span wire:loading wire:target="deleteRole">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Direct initialization of modals
            const roleFormModalEl = document.getElementById('roleFormModal');
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');
            
            // Initialize Bootstrap modals
            const roleFormModal = new bootstrap.Modal(roleFormModalEl);
            const deleteConfirmationModal = new bootstrap.Modal(deleteConfirmationModalEl);
            
            // Wait for Livewire to be fully initialized
            document.addEventListener('livewire:initialized', () => {
                // When role data is loaded, show the modal
                Livewire.on('roleDataLoaded', () => {
                    console.log('Role data loaded event received, showing modal');
                    setTimeout(() => roleFormModal.show(), 100);
                    
                    // Force input field values to match Livewire state
                    setTimeout(() => {
                        console.log('Setting form values from Livewire state');
                        console.log('Name:', @this.name);
                        console.log('Description:', @this.description);
                        console.log('Permissions:', @this.selectedPermissions);
                        
                        if (document.getElementById('name')) {
                            document.getElementById('name').value = @this.name || '';
                        }
                        
                        if (document.getElementById('description')) {
                            document.getElementById('description').value = @this.description || '';
                        }
                        
                        // Check permission checkboxes
                        const permissionIds = @this.selectedPermissions || [];
                        permissionIds.forEach(id => {
                            const checkbox = document.getElementById(`perm_${id}`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }, 150);
                });
                
                // Modal state change events
                Livewire.on('modalStateChanged', (state) => {
                    if (!state.isOpen) {
                        roleFormModal.hide();
                    }
                });
                
                // Close modal event
                Livewire.on('closeModal', () => {
                    roleFormModal.hide();
                });
                
                // Show delete confirmation event
                Livewire.on('showDeleteConfirmation', () => {
                    deleteConfirmationModal.show();
                });
            });
            
            // Create role button (Add New)
            const createButton = document.querySelector('button[wire\\:click="openModal"]');
            if (createButton) {
                createButton.addEventListener('click', function() {
                    setTimeout(() => roleFormModal.show(), 250);
                });
            }
            
            // Fix for checkbox selection in permissions
            document.addEventListener('click', function(e) {
                if (e.target && e.target.matches('[wire\\:model="selectedPermissions"]')) {
                    e.stopPropagation();
                }
            }, true);
            
            // Close modal on ESC key or when clicking the backdrop
            roleFormModalEl.addEventListener('hidden.bs.modal', () => {
                Livewire.dispatch('closeModalAction');
            });
            
            // Direct data population when modal is shown
            roleFormModalEl.addEventListener('shown.bs.modal', () => {
                console.log('Role modal shown, checking if data needs to be loaded');
                console.log('Edit mode:', @this.editMode, 'View mode:', @this.viewMode);
                console.log('Role name:', @this.name);
                console.log('Role description:', @this.description);
                console.log('Selected permissions:', @this.selectedPermissions);
                
                // Double check form elements are properly populated
                if (@this.editMode || @this.viewMode) {
                    // Name field
                    const nameInput = document.getElementById('name');
                    if (nameInput && (!nameInput.value || nameInput.value !== @this.name)) {
                        nameInput.value = @this.name || '';
                    }
                    
                    // Description field
                    const descInput = document.getElementById('description');
                    if (descInput && (!descInput.value || descInput.value !== @this.description)) {
                        descInput.value = @this.description || '';
                    }
                    
                    // Permission checkboxes
                    const permissionIds = @this.selectedPermissions || [];
                    permissionIds.forEach(id => {
                        const checkbox = document.getElementById(`perm_${id}`);
                        if (checkbox && !checkbox.checked) {
                            checkbox.checked = true;
                        }
                    });
                    
                    // Manually trigger checkbox input changes to ensure Livewire knows about them
                    document.querySelectorAll('input[type="checkbox"][wire\\:model="selectedPermissions"]').forEach(checkbox => {
                        if (permissionIds.includes(parseInt(checkbox.value))) {
                            if (!checkbox.checked) {
                                checkbox.checked = true;
                                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    });
                }
            });
        });
    </script>
    @endpush
</div>