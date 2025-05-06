<div>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manage Candidates</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('elections') }}">Elections</a></li>
            <li class="breadcrumb-item"><a href="{{ route('election.positions', $position->election) }}">Positions</a></li>
            <li class="breadcrumb-item active">Candidates</li>
        </ol>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="card-title">
                            <i class="fas fa-info-circle me-1"></i>
                            Position Details
                            </h1>
                        </div>
                        <a href="{{ route('election.positions', $position->election) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Positions
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $position->name }}</h5>
                        <p class="card-text">{{ $position->description }}</p>
                        <p><strong>Election:</strong> {{ $position->election->name }}</p>
                        <p><strong>Max votes allowed:</strong> {{ $position->max_votes_allowed }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">
                        <i class="fas fa-plus-circle me-1"></i>
                        Add New Candidate
                        </h1>
                    </div>
                    <div class="card-body">
                        <button wire:click="create" class="btn btn-success">
                            <i class="fas fa-plus"></i> Add Candidate
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="card-title">
                    <i class="fas fa-users me-1"></i>
                    Candidates
                </h1>
            </div>
            <div class="card-body">
                @if(count($candidates) > 0)
                    <div class="row">
                        @foreach($candidates as $candidate)
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="position-relative">
                                        @if($candidate->image_path)
                                            <img src="{{ Storage::url($candidate->image_path) }}" alt="{{ $candidate->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary text-white d-flex justify-content-center align-items-center" style="height: 200px;">
                                                <i class="fas fa-user fa-4x"></i>
                                            </div>
                                        @endif
                                        
                                        <div class="position-absolute top-0 end-0 p-2">
                                            <div class="badge {{ $candidate->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $candidate->is_active ? 'Active' : 'Inactive' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $candidate->name }}</h5>
                                        <p class="card-text">{{ \Illuminate\Support\Str::limit($candidate->bio, 100, '...') }}</p>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <div class="btn-group w-100">
                                            <button wire:click="edit({{ $candidate->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button wire:click="toggleActiveStatus({{ $candidate->id }})" class="btn btn-sm {{ $candidate->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                                <i class="fas {{ $candidate->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i> {{ $candidate->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button wire:click="confirmDelete({{ $candidate->id }})" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </div>
                                        @if($candidate->manifesto_path)
                                            <a href="{{ Storage::url($candidate->manifesto_path) }}" target="_blank" class="btn btn-sm btn-outline-info mt-2 w-100">
                                                <i class="fas fa-file-pdf"></i> View Manifesto
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        No candidates have been added for this position yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Create/Edit Candidate Modal -->
    @if($showForm)
        <div class="modal fade show" tabindex="-1" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditing ? 'Edit Candidate' : 'Add New Candidate' }}</h5>
                        <button wire:click="cancelEdit" type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">Name</label>
                                    <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio/Description</label>
                                <textarea wire:model="bio" class="form-control @error('bio') is-invalid @enderror" id="bio" rows="4"></textarea>
                                @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="image" class="form-label">Candidate Image</label>
                                    <input wire:model="image" type="file" class="form-control @error('image') is-invalid @enderror" id="image" accept="image/*">
                                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Maximum file size: 1MB</div>
                                    
                                    @if($imagePreview)
                                        <div class="mt-2">
                                            <img src="{{ $imagePreview }}" alt="Candidate Preview" class="img-thumbnail" style="max-height: 150px">
                                        </div>
                                    @elseif($existingImage)
                                        <div class="mt-2">
                                            <img src="{{ Storage::url($existingImage) }}" alt="Current Image" class="img-thumbnail" style="max-height: 150px">
                                            <div class="form-text">Current image (upload a new one to replace it)</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <label for="manifesto" class="form-label">Manifesto (PDF)</label>
                                    <input wire:model="manifesto" type="file" class="form-control @error('manifesto') is-invalid @enderror" id="manifesto" accept="application/pdf">
                                    @error('manifesto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Maximum file size: 5MB</div>
                                    
                                    @if($manifesto)
                                        <div class="mt-2">
                                            <div class="alert alert-info">
                                                <i class="fas fa-file-pdf"></i> New PDF file selected
                                            </div>
                                        </div>
                                    @elseif($manifestoPreview)
                                        <div class="mt-2">
                                            <a href="{{ $manifestoPreview }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-file-pdf"></i> View Current Manifesto
                                            </a>
                                            <div class="form-text">Current PDF (upload a new one to replace it)</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="display_order" class="form-label">Display Order</label>
                                    <input wire:model="display_order" type="number" min="0" class="form-control @error('display_order') is-invalid @enderror" id="display_order">
                                    @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input wire:model="is_active" type="checkbox" class="form-check-input" id="is_active">
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" wire:click="cancelEdit" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    {{ $isEditing ? 'Update Candidate' : 'Add Candidate' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
    
    <!-- Delete Confirmation Modal -->
    @if($confirmingDeletion)
        <div class="modal fade show" tabindex="-1" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirm Delete</h5>
                        <button wire:click="cancelDelete" type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this candidate? This action cannot be undone.</p>
                        <p class="text-danger"><strong>Warning:</strong> If votes have already been cast for this candidate, you will not be able to delete them.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancelDelete" class="btn btn-secondary">Cancel</button>
                        <button type="button" wire:click="delete" class="btn btn-danger">Delete Candidate</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>