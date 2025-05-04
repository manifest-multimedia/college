<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <div class="d-inline-block rounded-circle bg-success d-flex align-items-center justify-content-center text-white mb-4" style="width: 100px; height: 100px;">
                            <i class="fas fa-check-circle fa-4x"></i>
                        </div>
                    </div>
                    
                    <h1 class="h3 mb-3">Thank You for Voting!</h1>
                    <h2 class="h5 text-muted mb-4">Your vote has been securely recorded.</h2>
                    
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-info-circle fa-2x"></i>
                            </div>
                            <div class="text-start">
                                <p class="mb-1"><strong>Voting Details</strong></p>
                                <p class="mb-0">Election: {{ $election->name }}</p>
                                <p class="mb-0">Date: {{ now()->format('F j, Y h:i A') }}</p>
                                <p class="mb-0">Reference #: {{ $sessionId }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <p class="mb-4">Your participation in this election helps shape our institution's future. The final results will be published after the election concludes.</p>
                    
                    <div class="d-grid gap-2 col-md-6 mx-auto">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p>Have feedback about the voting experience?</p>
                <a href="{{ route('contact') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-comment me-1"></i>
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</div>