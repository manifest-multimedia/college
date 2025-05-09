<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-vote-yea me-2"></i>
                            Election Voter Verification
                        </h3>
                    </div>
                    <div class="card-body">
                        @if ($verificationStep === 'id')
                            <!-- Step 1: Student ID Verification -->
                            <h5>Enter Student ID to Vote</h5>
                            <p>Please enter your Student ID to verify your eligibility to vote in this election.</p>
                            
                            <form wire:submit.prevent="verify" class="mb-4">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="text" wire:model="student_id" class="form-control @error('student_id') is-invalid @enderror" id="student_id" placeholder="Enter your Student ID">
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                @if ($errorMessage)
                                    <div class="alert alert-danger">{{ $errorMessage }}</div>
                                @endif
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check-circle me-1"></i> Verify ID
                                </button>
                            </form>
                        @elseif ($verificationStep === 'security')
                            <!-- Step 2: Security Question Verification -->
                            <h5>Identity Verification</h5>
                            <p>To verify your identity, please answer the following security question:</p>
                            
                            <div class="alert alert-info">
                                <strong>Security Question:</strong> What is your {{ $securityQuestion }}?
                            </div>
                            
                            <form wire:submit.prevent="verifySecurityQuestion" class="mb-4">
                                <div class="mb-3">
                                    <label for="securityAnswer" class="form-label">Your Answer</label>
                                    <input type="text" wire:model="securityAnswer" class="form-control @error('securityAnswer') is-invalid @enderror" id="securityAnswer" placeholder="Enter your answer">
                                    @error('securityAnswer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                @if ($errorMessage)
                                    <div class="alert alert-danger">{{ $errorMessage }}</div>
                                @endif
                                
                                <div class="d-flex">
                                    <button type="button" wire:click="resetVerification" class="btn btn-secondary me-2">
                                        <i class="fas fa-arrow-left me-1"></i> Back
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check-circle me-1"></i> Verify
                                    </button>
                                </div>
                            </form>
                        @endif
                        
                        <div class="mt-4">
                            <h6>Election: {{ $election->title }}</h6>
                            <p>
                                <strong>Start:</strong> {{ $election->start_time->format('M j, Y g:i A') }}<br>
                                <strong>End:</strong> {{ $election->end_time->format('M j, Y g:i A') }}
                            </p>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            If you encounter any issues, please contact the election administrator.
                        </small>
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

    <!-- Audio element for error alert (hidden) -->
    <audio id="error-alert" src="{{ asset('sounds/error_alert.mp3') }}" preload="auto"></audio>

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
            
            // Listen for verification failure events
            @this.on('verification-failed', () => {
                const audioElement = document.getElementById('error-alert');
                if (audioElement) {
                    audioElement.currentTime = 0;
                    audioElement.play();
                }
            });
        });
    </script>
</div>