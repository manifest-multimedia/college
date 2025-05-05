<div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="card-title">
                            <i class="fas fa-list-alt me-2"></i> Manage Exam Types
                        </h1>
                        <div class="d-flex">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm me-2" placeholder="Search Exam Types...">
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
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

                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        {{ $editingExamTypeId ? 'Edit Exam Type' : 'Add New Exam Type' }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form wire:submit.prevent="saveExamType">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name">
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" wire:model="code">
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">A short unique code for this exam type (e.g. MID, END, REASS)</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="payment_threshold" class="form-label">Payment Threshold (%) <span class="text-danger">*</span></label>
                                            <input type="number" min="0" max="100" step="0.01" class="form-control @error('payment_threshold') is-invalid @enderror" id="payment_threshold" wire:model="payment_threshold">
                                            @error('payment_threshold')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Minimum percentage of fees a student must have paid to be eligible for this exam type</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" wire:model="description" rows="3"></textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                            <label class="form-check-label" for="is_active">Active</label>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> {{ $editingExamTypeId ? 'Update Exam Type' : 'Save Exam Type' }}
                                            </button>
                                            
                                            @if($editingExamTypeId)
                                                <button type="button" wire:click="cancelEdit" class="btn btn-secondary">
                                                    <i class="fas fa-times me-1"></i> Cancel
                                                </button>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Payment Threshold</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($examTypes as $examType)
                                            <tr>
                                                <td>{{ $examType->name }}</td>
                                                <td>{{ $examType->code }}</td>
                                                <td>{{ number_format($examType->payment_threshold, 2) }}%</td>
                                                <td>
                                                    <span class="badge bg-{{ $examType->is_active ? 'success' : 'danger' }}">
                                                        {{ $examType->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <button wire:click="editExamType({{ $examType->id }})" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button wire:click="confirmExamTypeDeletion({{ $examType->id }})" class="btn btn-sm btn-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No exam types found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $examTypes->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this exam type? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" wire:click="deleteExamType" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('show-delete-modal', () => {
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            });
        });
    </script>
    @endpush
</div>
