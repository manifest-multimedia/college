<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Fee Types Management</h6>
                    </div>
                    <div class="card-body">
                        <!-- Flash Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <!-- Search Input -->
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                    <input wire:model.live="search" type="text" class="form-control" placeholder="Search fee types...">
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feeTypeFormModal">
                                    <i class="fas fa-plus"></i> Add New Fee Type
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th class="text-center">Description</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feeTypes as $feeType)
                                        <tr>
                                            <td>{{ $feeType->name }}</td>
                                            <td>{{ $feeType->code }}</td>
                                            <td class="text-center">{{ $feeType->description ?? 'No description' }}</td>
                                            <td class="text-center">
                                                <span class="badge {{ $feeType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $feeType->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button wire:click="editFeeType({{ $feeType->id }})" class="btn btn-info btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="confirmFeeTypeDeletion({{ $feeType->id }})" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No fee types found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $feeTypes->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Type Form Modal -->
    <div wire:ignore.self class="modal fade" id="feeTypeFormModal" tabindex="-1" aria-labelledby="feeTypeFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feeTypeFormModalLabel">{{ $editingFeeTypeId ? 'Edit Fee Type' : 'Add New Fee Type' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveFeeType">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" wire:model="name">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" wire:model="code">
                            @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" wire:model="description" rows="3"></textarea>
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                            @error('is_active') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="cancelEdit">Cancel</button>
                            <button type="submit" class="btn btn-primary">{{ $editingFeeTypeId ? 'Update' : 'Save' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this fee type? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteFeeType">Delete</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('open-fee-type-form-modal', () => {
                const modal = document.getElementById('feeTypeFormModal');
                if (modal) {
                    new bootstrap.Modal(modal).show();
                }
            });
            Livewire.on('close-fee-type-form-modal', () => {
                const modal = document.getElementById('feeTypeFormModal');
                if (modal) {
                    const instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                }
            });
            Livewire.on('show-delete-modal', () => {
                const modal = document.getElementById('confirmDeleteModal');
                if (modal) {
                    new bootstrap.Modal(modal).show();
                }
            });
            Livewire.on('close-delete-modal', () => {
                const modal = document.getElementById('confirmDeleteModal');
                if (modal) {
                    const instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                }
            });
        });
    </script>
    @endpush
</div>
