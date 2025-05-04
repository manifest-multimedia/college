<div>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Election Management</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Elections</li>
        </ol>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Elections
                </div>
                <div>
                    <button wire:click="create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create New Election
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input wire:model.live.debounce.300ms="searchQuery" type="text" class="form-control" placeholder="Search elections...">
                        </div>
                    </div>
                </div>
                
                <!-- Elections Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Session Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($elections as $election)
                                <tr>
                                    <td>{{ $election->name }}</td>
                                    <td>{{ $election->start_time->format('M d, Y h:i A') }}</td>
                                    <td>{{ $election->end_time->format('M d, Y h:i A') }}</td>
                                    <td>{{ $election->voting_session_duration }} minutes</td>
                                    <td>
                                        @if($election->isActive())
                                            <span class="badge bg-success">Active & In Progress</span>
                                        @elseif($election->hasEnded())
                                            <span class="badge bg-secondary">Completed</span>
                                        @elseif(!$election->is_active)
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        @else
                                            <span class="badge bg-info">Upcoming</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('election.positions', $election) }}" class="btn btn-outline-primary btn-sm" title="Manage Positions">
                                                <i class="fas fa-list-ul"></i>
                                            </a>
                                            <a href="{{ route('election.results', $election) }}" class="btn btn-outline-success btn-sm" title="View Results">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <button wire:click="edit({{ $election->id }})" class="btn btn-outline-secondary btn-sm" title="Edit Election">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button wire:click="toggleActiveStatus({{ $election->id }})" 
                                                class="btn btn-sm {{ $election->is_active ? 'btn-outline-warning' : 'btn-outline-info' }}" 
                                                title="{{ $election->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas {{ $election->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                            </button>
                                            <button wire:click="confirmDelete({{ $election->id }})" class="btn btn-outline-danger btn-sm" title="Delete Election">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3">No elections found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div>
                    {{ $elections->links() }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Create/Edit Election Modal -->
    @if($showCreateForm)
        <div class="modal fade show" tabindex="-1" style="display: block;">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $isEditing ? 'Edit Election' : 'Create New Election' }}</h5>
                        <button wire:click="cancelEdit" type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" id="description" rows="3"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input wire:model="start_time" type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" id="start_time">
                                    @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input wire:model="end_time" type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" id="end_time">
                                    @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="voting_session_duration" class="form-label">Voting Session Duration (minutes)</label>
                                    <input wire:model="voting_session_duration" type="number" min="1" max="120" class="form-control @error('voting_session_duration') is-invalid @enderror" id="voting_session_duration">
                                    @error('voting_session_duration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Maximum time a student has to complete voting once they start.</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input wire:model="is_active" type="checkbox" class="form-check-input" id="is_active">
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            
                            @if(!$isEditing)
                                <div class="mb-3">
                                    <label for="templateElectionId" class="form-label">Clone from Existing Election (Optional)</label>
                                    <select wire:model="templateElectionId" class="form-select" id="templateElectionId">
                                        <option value="">-- Select an election to clone --</option>
                                        @foreach($templateElections as $templateElection)
                                            <option value="{{ $templateElection->id }}">{{ $templateElection->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">This will copy all positions from the selected election.</div>
                                </div>
                            @endif
                            
                            <div class="modal-footer">
                                <button type="button" wire:click="cancelEdit" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    {{ $isEditing ? 'Update Election' : 'Create Election' }}
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
                        <p>Are you sure you want to delete this election? This action cannot be undone.</p>
                        <p class="text-danger"><strong>Warning:</strong> This will delete all positions, candidates, and votes associated with this election.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="cancelDelete" class="btn btn-secondary">Cancel</button>
                        <button type="button" wire:click="delete" class="btn btn-danger">Delete Election</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    @endif
</div>