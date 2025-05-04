<div>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Manage Election Positions</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('elections') }}">Elections</a></li>
            <li class="breadcrumb-item active">Positions</li>
        </ol>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="card-title">
                            <i class="fas fa-info-circle me-1"></i>
                            Election Details
                            </h1>
                        </div>
                        <a href="{{ route('elections') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Elections
                        </a>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">{{ $election->name }}</h5>
                        <p class="card-text">{{ $election->description }}</p>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Start:</strong> {{ $election->start_time->format('M d, Y h:i A') }}</p>
                                <p><strong>Duration:</strong> {{ $election->voting_session_duration }} minutes per voter</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>End:</strong> {{ $election->end_time->format('M d, Y h:i A') }}</p>
                                <p>
                                    <strong>Status:</strong> 
                                    @if($election->isActive())
                                        <span class="badge bg-success">Active & In Progress</span>
                                    @elseif($election->hasEnded())
                                        <span class="badge bg-secondary">Completed</span>
                                    @elseif(!$election->is_active)
                                        <span class="badge bg-warning text-dark">Inactive</span>
                                    @else
                                        <span class="badge bg-info">Upcoming</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">
                        <i class="fas fa-plus-circle me-1"></i>
                        Add New Positions</h1>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <button wire:click="addNewPosition" class="btn btn-sm btn-success mb-2">
                                <i class="fas fa-plus"></i> Add Position
                            </button>
                            @if(count($newPositions) > 0)
                                <form wire:submit.prevent="saveNewPositions">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Description</th>
                                                    <th>Max Votes</th>
                                                    <th>Order</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($newPositions as $index => $position)
                                                    <tr>
                                                        <td>
                                                            <input wire:model="newPositions.{{ $index }}.name" type="text" class="form-control form-control-sm @error('newPositions.'.$index.'.name') is-invalid @enderror">
                                                            @error('newPositions.'.$index.'.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        </td>
                                                        <td>
                                                            <input wire:model="newPositions.{{ $index }}.description" type="text" class="form-control form-control-sm">
                                                        </td>
                                                        <td>
                                                            <input wire:model="newPositions.{{ $index }}.max_votes_allowed" type="number" min="1" class="form-control form-control-sm" style="width: 70px">
                                                        </td>
                                                        <td>
                                                            <input wire:model="newPositions.{{ $index }}.display_order" type="number" min="0" class="form-control form-control-sm" style="width: 70px">
                                                        </td>
                                                        <td>
                                                            <button type="button" wire:click="removeNewPosition({{ $index }})" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Save New Positions
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-info mb-0">
                                    Click "Add Position" to create new positions for this election.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h1 class="card-title">
                <i class="fas fa-list me-1"></i>
                Election Positions
                </h1>
            </div>
            <div class="card-body">
                @if(count($positions) > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Max Votes Allowed</th>
                                    <th>Candidates</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($positions as $position)
                                    <tr>
                                        <td>{{ $position->display_order }}</td>
                                        <td>{{ $position->name }}</td>
                                        <td>{{ $position->description }}</td>
                                        <td>{{ $position->max_votes_allowed }}</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $position->candidates->count() }} candidates
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('election.candidates', ['election' => $election, 'position' => $position]) }}" class="btn btn-outline-primary btn-sm" title="Manage Candidates">
                                                    <i class="fas fa-users"></i>
                                                </a>
                                                @if(!$isEditing || $positionId !== $position->id)
                                                    <button wire:click="edit({{ $position->id }})" class="btn btn-outline-secondary btn-sm" title="Edit Position">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button wire:click="confirmDelete({{ $position->id }})" class="btn btn-outline-danger btn-sm" title="Delete Position">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @if($isEditing && $positionId === $position->id)
                                        <tr>
                                            <td colspan="6" class="bg-light">
                                                <form wire:submit.prevent="save" class="p-2">
                                                    <div class="row">
                                                        <div class="col-md-3 mb-2">
                                                            <label for="name" class="form-label">Name</label>
                                                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name">
                                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        </div>
                                                        <div class="col-md-4 mb-2">
                                                            <label for="description" class="form-label">Description</label>
                                                            <input wire:model="description" type="text" class="form-control @error('description') is-invalid @enderror" id="description">
                                                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        </div>
                                                        <div class="col-md-2 mb-2">
                                                            <label for="max_votes_allowed" class="form-label">Max Votes</label>
                                                            <input wire:model="max_votes_allowed" type="number" min="1" class="form-control @error('max_votes_allowed') is-invalid @enderror" id="max_votes_allowed">
                                                            @error('max_votes_allowed') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        </div>
                                                        <div class="col-md-2 mb-2">
                                                            <label for="display_order" class="form-label">Order</label>
                                                            <input wire:model="display_order" type="number" min="0" class="form-control @error('display_order') is-invalid @enderror" id="display_order">
                                                            @error('display_order') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        </div>
                                                        <div class="col-md-1 d-flex align-items-end mb-2">
                                                            <div class="btn-group">
                                                                <button type="submit" class="btn btn-success btn-sm">
                                                                    <i class="fas fa-save"></i>
                                                                </button>
                                                                <button type="button" wire:click="cancelEdit" class="btn btn-secondary btn-sm">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        No positions have been defined for this election yet.
                    </div>
                @endif
            </div>
        </div>
    </div>
    
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
                        <p>Are you sure you want to delete this position? This action cannot be undone.</p>
                        <p class="text-danger"><strong>Warning:</strong> This will delete all candidates associated with this position.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancelDelete" class="btn btn-secondary">Cancel</button>
                        <button type="button" wire:click="delete" class="btn btn-danger">Delete Position</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>