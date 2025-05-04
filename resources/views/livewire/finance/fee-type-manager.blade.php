<div>
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">
                <i class="fas fa-money-bill-wave mr-2"></i> Fee Types Management
            </h1>
            <div class="card-tools">
                <button class="btn btn-primary" wire:click="createFeeType">
                    <i class="fas fa-plus"></i> Add New Fee Type
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search fee types..." wire:model.debounce.300ms="search">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fee Types Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeTypes as $feeType)
                            <tr>
                                <td>{{ $feeType->name }}</td>
                                <td><span class="badge badge-info">{{ $feeType->code }}</span></td>
                                <td>{{ Str::limit($feeType->description, 50) }}</td>
                                <td>
                                    @if($feeType->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info" wire:click="editFeeType({{ $feeType->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm {{ $feeType->is_active ? 'btn-warning' : 'btn-success' }}" 
                                                wire:click="toggleActive({{ $feeType->id }})" 
                                                title="{{ $feeType->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $feeType->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" wire:click="confirmDelete({{ $feeType->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No fee types found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="mt-4">
                {{ $feeTypes->links() }}
            </div>
        </div>
    </div>

    <!-- Fee Type Form Modal -->
    <div class="modal fade" id="feeTypeFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEditing ? 'Edit' : 'Create' }} Fee Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="name">Fee Type Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" wire:model="name" placeholder="Enter fee type name">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="code">Fee Type Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                   id="code" wire:model="code" placeholder="Enter fee type code">
                            @error('code')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" wire:model="description" rows="3" 
                                      placeholder="Enter description"></textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" 
                                       id="is_active" wire:model="is_active">
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveFeeType">
                        {{ $isEditing ? 'Update' : 'Create' }} Fee Type
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this fee type? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteFeeType">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for modals -->
    <script>
        document.addEventListener('livewire:load', function () {
            window.addEventListener('open-form-modal', event => {
                $('#feeTypeFormModal').modal('show');
            });
            
            window.addEventListener('close-form-modal', event => {
                $('#feeTypeFormModal').modal('hide');
            });
            
            window.addEventListener('open-delete-modal', event => {
                $('#deleteConfirmationModal').modal('show');
            });
            
            window.addEventListener('close-delete-modal', event => {
                $('#deleteConfirmationModal').modal('hide');
            });
            
            window.addEventListener('notify', event => {
                toastr[event.detail.type](event.detail.message);
            });
        });
    </script>
</div>