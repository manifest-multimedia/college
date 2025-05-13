<x-dashboard.default title="Access Denied - Device Conflict">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <div class="card-title">
                        <h4><i class="fas fa-exclamation-triangle"></i> Access Denied - Device Conflict</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-laptop text-danger" style="font-size: 4rem;"></i>
                        <i class="fas fa-times text-danger mx-3" style="font-size: 2rem;"></i>
                        <i class="fas fa-mobile-alt text-danger" style="font-size: 4rem;"></i>
                    </div>
                    
                    <div class="alert alert-danger">
                        <h5><strong>This exam is already in progress on another device.</strong></h5>
                        <p>For security reasons, you can only access an exam from one device at a time.</p>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h5>Why am I seeing this?</h5>
                            <p>Our system has detected that this exam is currently active on a different device or browser. This restriction helps maintain the integrity of the examination process.</p>
                            
                            <h5>What should I do?</h5>
                            <ul>
                                <li>Return to the device where you originally started this exam</li>
                                <li>Wait approximately 2 minutes for the session to time out on the other device</li>
                                <li>If you believe this is an error, contact your exam administrator for assistance</li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('take-exam') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Return to Exam Portal
                        </a>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>If you continue experiencing issues, please contact the IT support team with the time this occurred.</small>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>