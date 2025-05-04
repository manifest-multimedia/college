<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="d-inline-block bg-danger text-white rounded-circle p-3 mb-3">
                            <i class="fas fa-clock fa-3x"></i>
                        </div>
                        <h1 class="h2 mb-3">Voting Session Expired</h1>
                        <p class="text-muted">
                            Your voting session has timed out. This can happen if:
                        </p>
                        <ul class="text-start text-muted list-unstyled">
                            <li class="mb-2"><i class="fas fa-check-circle text-danger me-2"></i> You took longer than the allotted time to complete your ballot</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-danger me-2"></i> You left the voting page idle for too long</li>
                            <li><i class="fas fa-check-circle text-danger me-2"></i> Your session was closed due to inactivity</li>
                        </ul>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div>
                        <p class="mb-4">To vote in this election, you'll need to verify your student ID again.</p>
                        <a href="{{ route('election.verify', ['election' => $election->id]) }}" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-redo me-2"></i> Try Again
                        </a>
                        
                        <div class="mt-3">
                            <a href="{{ route('home') }}" class="text-decoration-none">
                                <i class="fas fa-home me-1"></i> Return to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>