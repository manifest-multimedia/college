<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h1 class="h3 mb-3">Student Voter Verification</h1>
                        <h2 class="h4 text-primary mb-4">{{ $election->name }}</h2>
                    </div>
                    
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div>
                                <p class="mb-1"><strong>Important Information</strong></p>
                                <p class="mb-0">Enter your Student ID to begin the voting process. Once verified, you'll have {{ $election->voting_session_duration }} minutes to complete your voting.</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($errorMessage)
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ $errorMessage }}
                        </div>
                    @endif
                    
                    @if($successMessage)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ $successMessage }}
                        </div>
                    @endif
                    
                    <form wire:submit.prevent="verify">
                        <div class="mb-4">
                            <label for="student_id" class="form-label">Student ID</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-id-card"></i>
                                </span>
                                <input 
                                    wire:model="student_id" 
                                    type="text" 
                                    id="student_id" 
                                    class="form-control form-control-lg @error('student_id') is-invalid @enderror" 
                                    placeholder="Enter your Student ID"
                                    autocomplete="off" 
                                    autofocus
                                >
                                @error('student_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Verify & Begin Voting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow mt-4">
                <div class="card-body p-4">
                    <h3 class="h5 mb-3">Election Details</h3>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <p><strong>Start Time:</strong> {{ $election->start_time->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>End Time:</strong> {{ $election->end_time->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-0">
                        <p class="mb-0"><strong>Status:</strong> 
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
            
            <div class="text-center mt-4">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> 
                    Return to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('redirect', (event) => {
                window.location.href = event.url;
            });

            @this.on('redirectToUrl', (event) => {
                setTimeout(() => {
                    window.location.href = event.url;
                }, 100);
            });
        });
    </script>
</div>