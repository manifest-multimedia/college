<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h2 class="card-title fw-bold">
                    <i class="fas fa-vote-yea me-2"></i>
                    {{ $election->title }}
                </h2>
            </div>
        </div>
        <div class="card-body">
            <!-- Election Status Badge -->
            <div class="mb-4">
                @if(now()->between($election->start_time, $election->end_time))
                    <span class="badge badge-lg badge-light-primary">
                        <i class="fas fa-circle fs-8 text-primary me-1"></i> Active Election
                    </span>
                @elseif(now()->lessThan($election->start_time))
                    <span class="badge badge-lg badge-light-warning">
                        <i class="fas fa-circle fs-8 text-warning me-1"></i> Upcoming Election
                    </span>
                @else
                    <span class="badge badge-lg badge-light-danger">
                        <i class="fas fa-circle fs-8 text-danger me-1"></i> Election Closed
                    </span>
                @endif
            </div>

            <!-- Election Details -->
            <div class="row mb-5">
                <div class="col-md-8">
                    <div class="mb-4">
                        <h4 class="mb-3">Election Description</h4>
                        <p>{{ $election->description }}</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">Start Date</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <span>{{ $election->start_time->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-1">End Date</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check text-primary me-2"></i>
                                <span>{{ $election->end_time->format('M j, Y g:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    @if(now()->between($election->start_time, $election->end_time))
                        <div class="alert alert-light-primary mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-info-circle me-3 fs-3"></i>
                                <div>
                                    <h6 class="mb-1">This election is currently active</h6>
                                    <p class="mb-0">You may vote in this election if you haven't already submitted your vote.</p>
                                </div>
                            </div>
                        </div>
                    @elseif(now()->lessThan($election->start_time))
                        <div class="alert alert-light-warning mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock me-3 fs-3"></i>
                                <div>
                                    <h6 class="mb-1">This election has not started yet</h6>
                                    <p class="mb-0">Voting will be available when the election starts.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-light-danger mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lock me-3 fs-3"></i>
                                <div>
                                    <h6 class="mb-1">This election has ended</h6>
                                    <p class="mb-0">Voting is no longer available for this election.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="mb-3">Election Positions</h5>
                            <ul class="list-group list-group-flush">
                                @forelse($positions as $position)
                                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-bottom">
                                        <span>{{ $position->title }}</span>
                                        <span class="badge bg-primary rounded-pill">
                                            {{ $candidateCounts[$position->id] }} {{ Str::plural('candidate', $candidateCounts[$position->id]) }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="list-group-item bg-transparent">
                                        No positions available in this election.
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Start Voting Button -->
            @if(now()->between($election->start_time, $election->end_time))
                <div class="text-center">
                    <button wire:click="startVoting" class="btn btn-lg btn-primary">
                        <i class="fas fa-vote-yea me-2"></i> Start Voting Now
                    </button>
                    <p class="text-muted mt-2">
                        <small>You will be asked to verify your Student ID before voting</small>
                    </p>
                </div>
            @elseif(now()->lessThan($election->start_time))
                <div class="text-center">
                    <button disabled class="btn btn-lg btn-secondary">
                        <i class="fas fa-clock me-2"></i> Voting Not Yet Available
                    </button>
                    <p class="text-muted mt-2">
                        <small>This election will start on {{ $election->start_time->format('M j, Y g:i A') }}</small>
                    </p>
                </div>
            @else
                <div class="text-center">
                    <button disabled class="btn btn-lg btn-danger">
                        <i class="fas fa-lock me-2"></i> Voting Closed
                    </button>
                    <p class="text-muted mt-2">
                        <small>This election ended on {{ $election->end_time->format('M j, Y g:i A') }}</small>
                    </p>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Return to List Link -->
    <div class="d-flex justify-content-center mt-4">
        <a href="{{ route('public.elections.index') }}" class="btn btn-light-primary">
            <i class="fas fa-arrow-left me-2"></i> Back to Elections List
        </a>
    </div>
</div>
