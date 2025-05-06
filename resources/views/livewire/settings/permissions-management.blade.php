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

    @if(session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
        <div class="d-flex flex-column flex-md-row gap-3 mb-3 mb-md-0">
            <div class="d-flex position-relative me-md-2">
                <span class="position-absolute top-50 translate-middle-y ms-3">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control form-control-solid ps-10" 
                    placeholder="Search permissions" wire:model.live.debounce.300ms="search">
            </div>
            
            <div class="d-flex">
                <select class="form-select form-select-solid" wire:model.live="categoryFilter">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category }}">{{ $category }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <div>
            <button type="button" class="btn btn-primary" wire:click="openModal()">
                <i class="fas fa-plus"></i> New Permission
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3>System Permissions</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-200px">
                                Permission Name
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th class="min-w-200px">Description</th>
                            <th class="min-w-100px">Guard</th>
                            <th class="min-w-100px text-center">Used By Roles</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $permission)
                            <tr>
                                <td>{{ $permission->name }}</td>
                                <td>{{ $permission->description ?? 'N/A' }}</td>
                                <td>{{ $permission->guard_name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $permission->roles->count() }}</span>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-icon btn-sm btn-light-primary me-1" 
                                        wire:click="viewPermission({{ $permission->id }})" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-sm btn-light-success me-1" 
                                        wire:click="editPermission({{ $permission->id }})" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-icon btn-sm btn-light-danger" 
                                        wire:click="confirmDelete({{ $permission->id }})" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No permissions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-5">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>

    <!-- Permission Form Modal -->
    <div class="modal fade" id="permissionFormModal" tabindex="-1" aria-labelledby="permissionFormModalLabel" 
        aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionFormModalLabel">
                        @if($editMode)
                            Edit Permission
                        @elseif($viewMode)
                            View Permission
                        @else
                            Add New Permission
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="permissionForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="name" class="form-label required">Permission Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                        id="name" wire:model="name" @if($viewMode) disabled @endif>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted mt-1">
                                        Use format: category.action (e.g., users.create)
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="category" class="form-label required">Category</label>
                                    <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                        id="category" wire:model="category" @if($viewMode) disabled @endif>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="guardName" class="form-label required">Guard Name</label>
                                    <select class="form-select @error('guardName') is-invalid @enderror" 
                                        id="guardName" wire:model="guardName" @if($viewMode) disabled @endif>
                                        <option value="web">Web</option>
                                        <option value="api">API</option>
                                    </select>
                                    @error('guardName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                        id="description" rows="3" wire:model="description" @if($viewMode) disabled @endif></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        @if($editMode && $permissionId)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Editing permission names may affect existing role assignments.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        @if($viewMode) Close @else Cancel @endif
                    </button>
                    @if(!$viewMode)
                        <button type="button" class="btn btn-primary" wire:click="savePermission">
                            <span wire:loading.remove wire:target="savePermission">Save</span>
                            <span wire:loading wire:target="savePermission">Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this permission? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Define bootstrap first to ensure it's available
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap is not loaded');
                return;
            }
            
            // Modal elements
            const permissionFormModalEl = document.getElementById('permissionFormModal');
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');
            
            // Initialize Bootstrap modals once
            const permissionFormModal = permissionFormModalEl ? new bootstrap.Modal(permissionFormModalEl) : null;
            const deleteConfirmationModal = deleteConfirmationModalEl ? new bootstrap.Modal(deleteConfirmationModalEl) : null;
            
            // Debug log
            console.log('Modal elements initialized:', {
                permissionFormModal: !!permissionFormModal,
                deleteConfirmationModal: !!deleteConfirmationModal
            });
            
            // Set up Livewire event listeners after initialization
            document.addEventListener('livewire:initialized', () => {
                console.log('Livewire initialized, setting up event listeners');
                
                // Permission modal events
                Livewire.on('permissionModalStateChanged', (event) => {
                    console.log('Modal state change event received:', event);
                    
                    if (permissionFormModal) {
                        if (event.isOpen) {
                            console.log('Showing permission form modal');
                            permissionFormModal.show();
                        } else {
                            console.log('Hiding permission form modal');
                            permissionFormModal.hide();
                        }
                    } else {
                        console.error('Permission form modal not initialized');
                    }
                });
                
                // Permission data loaded event
                Livewire.on('permissionDataLoaded', () => {
                    console.log('Permission data loaded successfully');
                    // Ensure modal is visible when data is loaded
                    if (permissionFormModal) {
                        permissionFormModal.show();
                    }
                });
                
                // Close modals event
                Livewire.on('closeModal', () => {
                    console.log('Close modal event received');
                    if (permissionFormModal) {
                        permissionFormModal.hide();
                    }
                    if (deleteConfirmationModal) {
                        deleteConfirmationModal.hide();
                    }
                });
                
                // Delete confirmation modal event
                Livewire.on('showDeleteConfirmation', () => {
                    console.log('Show delete confirmation event received');
                    if (deleteConfirmationModal) {
                        deleteConfirmationModal.show();
                    } else {
                        console.error('Delete confirmation modal not initialized');
                    }
                });
            });
            
            // Modal event handlers
            if (permissionFormModalEl) {
                permissionFormModalEl.addEventListener('hidden.bs.modal', () => {
                    console.log('Modal hidden event - notifying Livewire');
                    Livewire.dispatch('closeModalAction');
                });
                
                permissionFormModalEl.addEventListener('shown.bs.modal', () => {
                    console.log('Modal shown - form is visible');
                });
            }
            
            // Delete confirmation button handler
            if (deleteConfirmationModalEl) {
                const confirmDeleteBtn = document.getElementById('confirmDelete');
                if (confirmDeleteBtn) {
                    confirmDeleteBtn.addEventListener('click', () => {
                        console.log('Delete confirmed');
                        Livewire.dispatch('deleteConfirmed');
                        if (deleteConfirmationModal) {
                            deleteConfirmationModal.hide();
                        }
                    });
                }
            }
        });
    </script>
    @endpush
</div>
