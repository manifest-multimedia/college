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
                <input type="text" class="form-control ps-8" wire:model.live="search" placeholder="Search departments...">
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus-circle me-2"></i> Create Department
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-label">Department Management</h3>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-bordered table-hover">
                    <thead class="table-light">
                        <tr class="fw-bold fs-6 text-gray-800">
                            <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-125px">
                                Department Name
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th wire:click="sortBy('code')" style="cursor: pointer;" class="min-w-125px">
                                Code
                                @if($sortField === 'code')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th class="min-w-150px">Description</th>
                            <th class="min-w-80px">Status</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departments as $department)
                            <tr>
                                <td class="align-middle">{{ $department->name }}</td>
                                <td class="align-middle">{{ $department->code ?? 'N/A' }}</td>
                                <td class="align-middle">{{ Str::limit($department->description, 50) ?? 'N/A' }}</td>
                                <td class="align-middle">
                                    @if($department->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="align-middle text-end">
                                    <div class="d-inline-flex">
                                        <button type="button" class="btn btn-sm btn-icon btn-light-primary me-1" 
                                            wire:click="viewDepartment({{ $department->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-primary me-1" 
                                            wire:click="editDepartment({{ $department->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-light-danger" 
                                            wire:click="confirmDelete({{ $department->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No departments found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $departments->links() }}
            </div>
        </div>
    </div>

    <!-- Modal for Adding/Editing Department -->
    <div class="modal fade" id="departmentFormModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($viewMode)
                            View Department
                        @elseif($editMode)
                            Edit Department
                        @else
                            Create New Department
                        @endif
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="departmentForm">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Department Name</label>
                                    <input type="text" wire:model="name" id="name" 
                                        class="form-control @error('name') is-invalid @enderror"
                                        {{ $viewMode ? 'disabled' : '' }}>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="code" class="form-label fw-bold">Department Code</label>
                                    <input type="text" wire:model="code" id="code" 
                                        class="form-control @error('code') is-invalid @enderror"
                                        {{ $viewMode ? 'disabled' : '' }}>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea wire:model="description" id="description" rows="3" 
                                        class="form-control @error('description') is-invalid @enderror"
                                        {{ $viewMode ? 'disabled' : '' }}></textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active"
                                        {{ $viewMode ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="is_active">Active Status</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ $viewMode ? 'Close' : 'Cancel' }}</button>
                    @if(!$viewMode)
                        <button type="button" class="btn btn-primary" wire:click="saveDepartment">
                            <span wire:loading.remove wire:target="saveDepartment">Save</span>
                            <span wire:loading wire:target="saveDepartment">Saving...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this department? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteDepartment" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="deleteDepartment">Delete</span>
                        <span wire:loading wire:target="deleteDepartment">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modals
            const departmentFormModalEl = document.getElementById('departmentFormModal');
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');
            
            // Create Bootstrap modal instances
            const departmentFormModal = new bootstrap.Modal(departmentFormModalEl);
            const deleteConfirmationModal = new bootstrap.Modal(deleteConfirmationModalEl);
            
            // Wait for Livewire to be fully initialized
            document.addEventListener('livewire:initialized', () => {
                // Handle departmentDataLoaded event
                Livewire.on('departmentDataLoaded', () => {
                    console.log('Department data loaded event received');
                    setTimeout(() => {
                        departmentFormModal.show();
                    }, 100);
                });
                
                // Handle modal state changes
                Livewire.on('modalStateChanged', (state) => {
                    if (state.isOpen && !departmentFormModalEl.classList.contains('show')) {
                        setTimeout(() => departmentFormModal.show(), 100);
                    } else if (!state.isOpen) {
                        departmentFormModal.hide();
                    }
                });
                
                // Close modal event handler
                Livewire.on('closeModal', () => {
                    departmentFormModal.hide();
                });
                
                // Delete confirmation modal handler
                Livewire.on('showDeleteConfirmation', () => {
                    deleteConfirmationModal.show();
                });
                
                // Modal hidden event - reset component state
                departmentFormModalEl.addEventListener('hidden.bs.modal', () => {
                    Livewire.dispatch('closeModalAction');
                });
            });
        });
    </script>
    @endpush
</div>
