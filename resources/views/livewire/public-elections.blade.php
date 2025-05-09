<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h2 class="card-title fw-bold">
                    <i class="fas fa-vote-yea me-2"></i>
                    College Elections
                </h2>
            </div>
        </div>
        <div class="card-body">
            <div class="text-center mb-5">
                <h4>Welcome to the College Online Voting Platform</h4>
                <p class="text-muted">
                    This platform allows students to participate in college-wide elections by voting for their preferred candidates.
                    <br>All you need is your Student ID to verify your identity and cast your vote.
                </p>
            </div>
            
            <!-- Active Elections Section -->
            <h3 class="mb-3">Active Elections</h3>
            @if($activeElections->count() > 0)
                <div class="row g-4">
                    @foreach($activeElections as $election)
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">{{ $election->title }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column">
                                        <div class="mb-3">
                                            <span class="badge badge-primary bg-primary mb-2">Active</span>
                                            <p>{{ $election->description }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                Ends on {{ $election->end_time->format('M j, Y g:i A') }}
                                            </small>
                                        </div>
                                        
                                        <!-- Time Remaining -->
                                        @php
                                            $hoursRemaining = now()->diffInHours($election->end_time);
                                        @endphp
                                        
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="me-2">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <small class="text-muted">Time remaining</small>
                                                <div class="fw-bold">
                                                    @if($hoursRemaining < 24)
                                                        {{ $hoursRemaining }} hours
                                                    @else
                                                        {{ ceil($hoursRemaining / 24) }} days
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <a href="{{ route('public.elections.show', $election) }}" class="btn btn-primary w-100">
                                                <i class="fas fa-info-circle me-1"></i> View Details & Vote
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    There are currently no active elections. Please check back later.
                </div>
            @endif
            
            <!-- Upcoming Elections Section -->
            @if($upcomingElections->count() > 0)
                <h3 class="mt-5 mb-3">Upcoming Elections</h3>
                <div class="row g-4">
                    @foreach($upcomingElections as $election)
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">{{ $election->title }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column">
                                        <div class="mb-3">
                                            <span class="badge badge-secondary bg-secondary mb-2">Upcoming</span>
                                            <p>{{ $election->description }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="far fa-calendar-alt me-1"></i>
                                                Starts on {{ $election->start_time->format('M j, Y g:i A') }}
                                            </small>
                                        </div>
                                        
                                        <!-- Days Until Start -->
                                        @php
                                            $daysUntil = now()->diffInDays($election->start_time);
                                        @endphp
                                        
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="me-2">
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-hourglass-start"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <small class="text-muted">Starting in</small>
                                                <div class="fw-bold">{{ $daysUntil }} days</div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <a href="{{ route('public.elections.show', $election) }}" class="btn btn-outline-secondary w-100">
                                                <i class="fas fa-info-circle me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <!-- Completed Elections Section -->
            @if($completedElections->count() > 0)
                <h3 class="mt-5 mb-3">Recently Completed Elections</h3>
                <div class="row g-4">
                    @foreach($completedElections as $election)
                        <div class="col-md-6 col-lg-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">{{ $election->title }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex flex-column">
                                        <div class="mb-3">
                                            <span class="badge badge-danger bg-danger mb-2">Completed</span>
                                            <p>{{ $election->description }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted">
                                                <i class="far fa-calendar-check me-1"></i>
                                                Ended on {{ $election->end_time->format('M j, Y g:i A') }}
                                            </small>
                                        </div>
                                        
                                        <div class="mt-auto">
                                            <a href="{{ route('public.elections.show', $election) }}" class="btn btn-outline-danger w-100">
                                                <i class="fas fa-poll me-1"></i> View Results
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
