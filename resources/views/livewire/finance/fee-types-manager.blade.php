<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">Fee Types Management</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <!-- Flash Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success mx-3">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger mx-3">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row px-3">
                            <div class="col-md-6">
                                <!-- Search Input -->
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input wire:model.live="search" type="text" class="form-control" placeholder="Search fee types...">
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feeTypeFormModal">
                                    <i class="fas fa-plus"></i> Add New Fee Type
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Code</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feeTypes as $feeType)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $feeType->name }}</h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $feeType->code }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs text-secondary mb-0">{{ $feeType->description ?? 'No description' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm {{ $feeType->is_active ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                    {{ $feeType->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <button wire:click="editFeeType({{ $feeType->id }})" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#feeTypeFormModal">
                                                    <i class="fas fa-edit text-white"></i>
                                                </button>
                                                <button wire:click="confirmFeeTypeDeletion({{ $feeType->id }})" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash text-white"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No fee types found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="px-3 mt-3">
                            {{ $feeTypes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Type Form Modal -->
    <div wire:ignore.self class="modal fade" id="feeTypeFormModal" tabindex="-1" aria-labelledby="feeTypeFormModalLabel" aria-hidden="true">
        <div class="modal-dialog">
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
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control" id="code" wire:model="code">
                            @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" wire:model="description" rows="3"></textarea>
                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                            @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
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
        <div class="modal-dialog">
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

    <!-- Alpine.js for handling modal state -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('show-delete-modal', () => {
                let deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                deleteModal.show();
            });
        });
    </script>
</div>