<div>
    <div 
        x-data="{
            positions: {{ Js::from($positions) }},
            currentPositionIndex: 0,
            confirmationShown: false,
            timeRemaining: {{ $timeRemaining }},
            formattedTime: '',
            formatTime() {
                const minutes = Math.floor(this.timeRemaining / 60);
                const seconds = this.timeRemaining % 60;
                this.formattedTime = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            },
            startTimer() {
                this.formatTime();
                setInterval(() => {
                    if (this.timeRemaining > 0) {
                        this.timeRemaining--;
                        this.formatTime();
                    } else {
                        // Time expired
                        Livewire.dispatch('timeExpired');
                    }
                    
                    // Add warning class when less than 2 minutes remain
                    if (this.timeRemaining < 120) {
                        document.getElementById('timer').classList.add('bg-warning');
                    }
                }, 1000);
            },
            nextPosition() {
                if (this.currentPositionIndex < this.positions.length - 1) {
                    this.currentPositionIndex++;
                    window.scrollTo(0, 0);
                }
            },
            prevPosition() {
                if (this.currentPositionIndex > 0) {
                    this.currentPositionIndex--;
                    window.scrollTo(0, 0);
                }
            },
            isLastPosition() {
                return this.currentPositionIndex === this.positions.length - 1;
            },
            isFirstPosition() {
                return this.currentPositionIndex === 0;
            },
            showConfirmation() {
                this.confirmationShown = true;
            },
            hasSingleCandidate(position) {
                return position.candidates.length === 1;
            }
        }"
        x-init="startTimer()"
        @voteSubmitted.window="setTimeout(() => window.location.href = '{{ route('election.thank-you', $election->id) }}', 1500)"
        class="voting-interface"
    >
        <div class="container-fluid py-4">
            <!-- Header with Timer -->
            <div class="row justify-content-between sticky-top bg-white py-2 shadow-sm mb-4">
                <div class="col-md-8">
                    <h1 class="h3 mb-0">{{ $election->name }}</h1>
                    <p class="text-muted mb-0">Welcome, {{ $student->name ?? 'Student' }}</p>
                </div>
                <div class="col-md-4 text-end">
                    <div id="timer" class="bg-info text-white py-2 px-3 rounded-lg d-inline-block">
                        <i class="fas fa-clock me-2"></i>
                        Time Remaining: <strong x-text="formattedTime"></strong>
                    </div>
                </div>
            </div>
            
            <!-- Progress Indicator -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="progress" style="height: 8px;">
                        <div 
                            class="progress-bar bg-primary" 
                            role="progressbar" 
                            x-bind:style="`width: ${(currentPositionIndex + 1) / positions.length * 100}%`"
                            x-bind:aria-valuenow="currentPositionIndex + 1"
                            aria-valuemin="0" 
                            x-bind:aria-valuemax="positions.length"
                        ></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small>Position <span x-text="currentPositionIndex + 1"></span> of <span x-text="positions.length"></span></small>
                        <small x-text="`${Math.round((currentPositionIndex + 1) / positions.length * 100)}% complete`"></small>
                    </div>
                </div>
            </div>
            
            <!-- Error Message -->
            @if($errorMessage)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ $errorMessage }}
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Voting Content -->
            <div class="row">
                <div class="col-12">
                    <template x-for="(position, index) in positions" :key="position.id">
                        <div x-show="currentPositionIndex === index" class="position-card">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-light py-3">
                                    <h2 class="h4 mb-0" x-text="position.name"></h2>
                                </div>
                                <div class="card-body">
                                    <p x-text="position.description" class="text-muted mb-3"></p>
                                    
                                    <!-- Multiple candidates case -->
                                    <template x-if="!hasSingleCandidate(position)">
                                        <div>
                                            <div class="alert alert-info">
                                                <small>
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Select <strong x-text="position.max_votes_allowed"></strong> candidate(s) for this position
                                                </small>
                                            </div>
                                            
                                            <!-- Candidates -->
                                            <div class="row">
                                                <template x-for="candidate in position.candidates" :key="candidate.id">
                                                    <div class="col-md-6 mb-3">
                                                        <div 
                                                            class="card h-100 candidate-card"
                                                            x-bind:class="{'selected': $wire.votes[position.id] === candidate.id}"
                                                            wire:click="selectCandidate(position.id, candidate.id)"
                                                        >
                                                            <div class="position-relative">
                                                                <template x-if="candidate.image_path">
                                                                    <img 
                                                                        x-bind:src="`/storage/${candidate.image_path}`"
                                                                        x-bind:alt="candidate.name"
                                                                        class="card-img-top"
                                                                        style="height: 180px; object-fit: cover;"
                                                                    >
                                                                </template>
                                                                <template x-if="!candidate.image_path">
                                                                    <div class="bg-secondary text-white d-flex justify-content-center align-items-center" style="height: 180px;">
                                                                        <i class="fas fa-user fa-3x"></i>
                                                                    </div>
                                                                </template>
                                                                
                                                                <div 
                                                                    class="position-absolute top-0 end-0 p-2"
                                                                    x-show="$wire.votes[position.id] === candidate.id"
                                                                >
                                                                    <div class="badge bg-success">
                                                                        <i class="fas fa-check-circle"></i> Selected
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="card-body">
                                                                <h5 class="card-title" x-text="candidate.name"></h5>
                                                                <p class="card-text small" x-text="candidate.bio"></p>
                                                            </div>
                                                            <div class="card-footer bg-transparent text-center">
                                                                <template x-if="$wire.votes[position.id] === candidate.id">
                                                                    <button class="btn btn-outline-danger btn-sm">
                                                                        <i class="fas fa-times me-1"></i> Unselect
                                                                    </button>
                                                                </template>
                                                                <template x-if="$wire.votes[position.id] !== candidate.id">
                                                                    <button class="btn btn-outline-primary btn-sm">
                                                                        <i class="fas fa-check me-1"></i> Select
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <!-- Single candidate case (YES/NO vote) -->
                                    <template x-if="hasSingleCandidate(position)">
                                        <div>
                                            <div class="alert alert-warning">
                                                <small>
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    This position has only one candidate. Please vote YES or NO to approve or reject this candidate.
                                                </small>
                                            </div>
                                            
                                            <!-- Single Candidate Card -->
                                            <div class="row mb-4">
                                                <div class="col-md-6 mx-auto">
                                                    <div class="card h-100">
                                                        <div class="position-relative">
                                                            <template x-if="position.candidates[0].image_path">
                                                                <img 
                                                                    x-bind:src="`/storage/${position.candidates[0].image_path}`"
                                                                    x-bind:alt="position.candidates[0].name"
                                                                    class="card-img-top"
                                                                    style="height: 200px; object-fit: cover;"
                                                                >
                                                            </template>
                                                            <template x-if="!position.candidates[0].image_path">
                                                                <div class="bg-secondary text-white d-flex justify-content-center align-items-center" style="height: 200px;">
                                                                    <i class="fas fa-user fa-3x"></i>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        
                                                        <div class="card-body">
                                                            <h5 class="card-title text-center" x-text="position.candidates[0].name"></h5>
                                                            <p class="card-text" x-text="position.candidates[0].bio"></p>
                                                            
                                                            <!-- YES/NO Voting Buttons -->
                                                            <div class="d-flex justify-content-center mt-3">
                                                                <div class="btn-group w-100">
                                                                    <button 
                                                                        class="btn btn-lg" 
                                                                        x-bind:class="$wire.yesNoVotes[position.id] === 'yes' ? 'btn-success' : 'btn-outline-success'"
                                                                        wire:click="selectYesNo(position.id, 'yes')"
                                                                    >
                                                                        <i class="fas fa-check-circle me-2"></i> YES
                                                                    </button>
                                                                    <button 
                                                                        class="btn btn-lg" 
                                                                        x-bind:class="$wire.yesNoVotes[position.id] === 'no' ? 'btn-danger' : 'btn-outline-danger'"
                                                                        wire:click="selectYesNo(position.id, 'no')"
                                                                    >
                                                                        <i class="fas fa-times-circle me-2"></i> NO
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <!-- Navigation buttons -->
                                    <div class="d-flex justify-content-between mt-3">
                                        <button 
                                            class="btn btn-outline-secondary" 
                                            x-show="!isFirstPosition()" 
                                            @click="prevPosition()"
                                        >
                                            <i class="fas fa-arrow-left me-2"></i> Previous Position
                                        </button>
                                        
                                        <template x-if="!isLastPosition()">
                                            <button 
                                                class="btn btn-primary ms-auto" 
                                                @click="nextPosition()"
                                            >
                                                Next Position <i class="fas fa-arrow-right ms-2"></i>
                                            </button>
                                        </template>
                                        
                                        <template x-if="isLastPosition()">
                                            <button 
                                                class="btn btn-success ms-auto" 
                                                wire:click="confirmSubmit"
                                            >
                                                <i class="fas fa-check-double me-2"></i> Review & Submit Ballot
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Vote Confirmation Modal -->
        @if($confirmingSubmission)
            <div class="modal fade show" tabindex="-1" style="display: block;">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-light">
                            <h5 class="modal-title">
                                <i class="fas fa-vote-yea me-2"></i>
                                Confirm Your Ballot
                            </h5>
                            <button wire:click="cancelSubmit" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 alert-heading">Please review your selections carefully</h6>
                                        <p class="mb-0">Once you submit your ballot, it cannot be changed and you will not be able to vote again in this election.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Position</th>
                                            <th>Your Selection</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($positions as $position)
                                            <tr>
                                                <td>{{ $position->name }}</td>
                                                <td>
                                                    @if($position->candidates->where('is_active', true)->count() === 1)
                                                        @php
                                                            $singleCandidate = $position->candidates->first();
                                                        @endphp
                                                        @if(isset($yesNoVotes[$position->id]) && $yesNoVotes[$position->id])
                                                            <div class="d-flex align-items-center">
                                                                @if($singleCandidate->image_path)
                                                                    <img src="{{ Storage::url($singleCandidate->image_path) }}" alt="{{ $singleCandidate->name }}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                                @else
                                                                    <div class="bg-secondary rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                                                        <i class="fas fa-user text-white small"></i>
                                                                    </div>
                                                                @endif
                                                                {{ $singleCandidate->name }}: 
                                                                @if($yesNoVotes[$position->id] === 'yes')
                                                                    <span class="badge bg-success ms-2">YES</span>
                                                                @else
                                                                    <span class="badge bg-danger ms-2">NO</span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-danger">No selection made</span>
                                                        @endif
                                                    @else
                                                        @if(isset($votes[$position->id]) && $votes[$position->id])
                                                            @php
                                                                $selectedCandidate = $position->candidates->firstWhere('id', $votes[$position->id]);
                                                            @endphp
                                                            @if($selectedCandidate)
                                                                <div class="d-flex align-items-center">
                                                                    @if($selectedCandidate->image_path)
                                                                        <img src="{{ Storage::url($selectedCandidate->image_path) }}" alt="{{ $selectedCandidate->name }}" class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                                                    @else
                                                                        <div class="bg-secondary rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                                                            <i class="fas fa-user text-white small"></i>
                                                                        </div>
                                                                    @endif
                                                                    {{ $selectedCandidate->name }}
                                                                </div>
                                                            @else
                                                                <span class="text-danger">Invalid selection</span>
                                                            @endif
                                                        @else
                                                            <span class="text-danger">No selection made</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="cancelSubmit" class="btn btn-outline-secondary">
                                <i class="fas fa-edit me-1"></i> Change Selections
                            </button>
                            <button type="button" wire:click="submit" class="btn btn-success" wire:loading.attr="disabled">
                                <i wire:loading class="fas fa-spinner fa-spin me-1"></i>
                                <i wire:loading.remove class="fas fa-check-circle me-1"></i>
                                Submit Ballot
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        @endif
        
        <!-- Thank You Modal (after successful vote) -->
        @if($voteSubmitted)
            <div class="modal fade show" tabindex="-1" style="display: block;">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body text-center py-5">
                            <div class="d-flex justify-content-center">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                            </div>
                            <h4>Thank You for Voting!</h4>
                            <p class="mb-0">Your ballot has been successfully submitted.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        @endif
    </div>
    
    @push('styles')
    <style>
        .candidate-card {
            cursor: pointer;
            transition: all 0.2s;
        }
        .candidate-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .candidate-card.selected {
            border: 3px solid #198754;
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
    </style>
    @endpush
</div>