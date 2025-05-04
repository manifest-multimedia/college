<x-default-layout>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Student Elections</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Elections</li>
        </ol>
        
        <div class="row">
            <div class="col-xl-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-vote-yea me-1"></i>
                            Available Elections
                        </div>
                        <div>
                            <button wire:click="$refresh" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($activeElections) > 0)
                            <div class="row">
                                @foreach($activeElections as $election)
                                    <div class="col-md-6 col-xl-4 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="card-title mb-0">{{ $election->name }}</h5>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <p class="card-text">{{ $election->description }}</p>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <strong>Start:</strong> {{ $election->start_time->format('M d, Y h:i A') }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <strong>End:</strong> {{ $election->end_time->format('M d, Y h:i A') }}
                                                    </small>
                                                </div>
                                                
                                                @php
                                                    $now = now();
                                                    $totalDuration = $election->end_time->diffInSeconds($election->start_time);
                                                    $elapsedDuration = $now->diffInSeconds($election->start_time);
                                                    $percentage = min(100, max(0, ($elapsedDuration / $totalDuration) * 100));
                                                @endphp
                                                
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small>Election Progress</small>
                                                        <small>{{ round($percentage) }}%</small>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto">
                                                    @if($userHasVoted($election->id))
                                                        <div class="alert alert-success mb-3 py-2">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            You have already voted in this election
                                                        </div>
                                                        
                                                        @if(auth()->user()->hasRole('admin'))
                                                            <a href="{{ route('election.results', $election) }}" class="btn btn-info btn-sm d-block">
                                                                <i class="fas fa-chart-bar me-1"></i>
                                                                View Results
                                                            </a>
                                                        @endif
                                                    @else
                                                        <a href="{{ route('election.verify', $election) }}" class="btn btn-primary d-block">
                                                            <i class="fas fa-vote-yea me-2"></i>
                                                            Cast Your Vote
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-footer bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small>
                                                        <i class="fas fa-users me-1"></i> 
                                                        {{ $election->positions->count() }} positions
                                                    </small>
                                                    <small>
                                                        <i class="fas fa-clock me-1"></i>
                                                        @if($now->greaterThan($election->end_time))
                                                            <span class="text-danger">Ended</span>
                                                        @elseif($now->lessThan($election->start_time))
                                                            <span class="text-primary">Starts {{ $now->diffForHumans($election->start_time) }}</span>
                                                        @else
                                                            <span class="text-success">Ends {{ $now->diffForHumans($election->end_time) }}</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">No Active Elections</h5>
                                        <p class="mb-0">There are currently no active elections available for voting. Please check back later.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        @if(count($pastElections) > 0)
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-history me-1"></i>
                            Past Elections
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Election</th>
                                            <th>Period</th>
                                            <th>Your Participation</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pastElections as $election)
                                            <tr>
                                                <td>
                                                    <div class="fw-bold">{{ $election->name }}</div>
                                                    <div class="small text-muted">{{ Str::limit($election->description, 100) }}</div>
                                                </td>
                                                <td>
                                                    {{ $election->start_time->format('M d, Y') }} - {{ $election->end_time->format('M d, Y') }}
                                                </td>
                                                <td>
                                                    @if($userHasVoted($election->id))
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i> Voted
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-times-circle me-1"></i> Did not vote
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(auth()->user()->hasRole('admin'))
                                                        <a href="{{ route('election.results', $election) }}" class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-chart-bar me-1"></i>
                                                            View Results
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        @if(auth()->user()->hasRole('admin'))
            <div class="row mt-2 mb-4">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-cog fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Election Administration</h5>
                                    <p class="mb-2">As an administrator, you can manage all aspects of the election system.</p>
                                    <a href="{{ route('elections') }}" class="btn btn-primary">
                                        <i class="fas fa-users-cog me-1"></i>
                                        Election Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-default-layout>