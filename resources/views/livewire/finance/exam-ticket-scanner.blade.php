<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-qrcode"></i> Exam Entry Ticket Scanner
                </h1>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div id="scanner-container" class="mb-4" x-data="{
                        scanner: null,
                        init() {
                            // Wait for DOM to be ready
                            this.$nextTick(() => {
                                if (this.scanner) {
                                    this.scanner.stop();
                                }
                                
                                // Initialize QR scanner
                                this.scanner = new Html5QrcodeScanner('qr-reader', { 
                                    fps: 10,
                                    qrbox: 250,
                                    rememberLastUsedCamera: true,
                                    aspectRatio: 1.0
                                });
                                
                                // Handle successful QR scan
                                this.scanner.render((qrCodeMessage) => {
                                    console.log('QR Code detected:', qrCodeMessage);
                                    // Call Livewire method with the QR code value
                                    @this.call('verifyQrCode', qrCodeMessage);
                                    // Stop scanning after successful detection
                                    this.scanner.pause();
                                }, (error) => {
                                    // Handle errors if needed
                                    console.warn('QR Code scanning error:', error);
                                });
                            });
                        },
                        stopScanner() {
                            if (this.scanner) {
                                this.scanner.stop();
                                this.scanner = null;
                            }
                        },
                        restartScanner() {
                            this.stopScanner();
                            this.init();
                        }
                    }" x-init="init()" x-show="@js($scannerActive)">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-camera"></i> Scan QR Code
                                </h5>
                            </div>
                            <div class="card-body">
                                <div id="qr-reader" style="width: 100%"></div>
                                <div class="text-center mt-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Position the QR code within the scan area
                                    </div>
                                    <button class="btn btn-secondary" x-on:click="restartScanner()">
                                        <i class="fas fa-sync"></i> Reset Camera
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($verificationResult)
                        <div class="verification-result mb-4" x-data x-init="$dispatch('verification-complete')">
                            <div class="card">
                                <div class="card-header bg-{{ $verificationResult['success'] ? 'success' : 'danger' }}">
                                    <h5 class="card-title mb-0 text-white">
                                        @if($verificationResult['success'])
                                            <i class="fas fa-check-circle"></i> Verification Successful
                                        @else
                                            <i class="fas fa-times-circle"></i> Verification Failed
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-{{ $verificationResult['success'] ? 'success' : 'danger' }}">
                                        {{ $verificationResult['message'] }}
                                    </div>
                                    
                                    @if(isset($verificationResult['data']['student']))
                                        <div class="student-info mb-4">
                                            <div class="d-flex align-items-center">
                                                @if(isset($verificationResult['data']['student']['photo']))
                                                    <img src="{{ asset('storage/' . $verificationResult['data']['student']['photo']) }}" 
                                                        alt="Student Photo" class="rounded me-3" width="120" height="120">
                                                @else
                                                    <div class="bg-secondary rounded d-flex justify-content-center align-items-center text-white me-3"
                                                        style="width: 120px; height: 120px;">
                                                        <i class="fas fa-user fa-3x"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <h4 class="mb-1">{{ $verificationResult['data']['student']['full_name'] ?? 'N/A' }}</h4>
                                                    <p class="mb-1"><strong>ID:</strong> {{ $verificationResult['data']['student']['student_id'] ?? 'N/A' }}</p>
                                                    <p class="mb-0"><strong>Class:</strong> {{ $verificationResult['data']['student']['class_name'] ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(isset($verificationResult['data']['status']))
                                        <div class="mb-3">
                                            <h5>Ticket Status:</h5>
                                            <span class="badge bg-{{ 
                                                $verificationResult['data']['status'] == 'cleared' ? 'success' : 
                                                ($verificationResult['data']['status'] == 'already_used' ? 'warning' : 'danger') 
                                            }} fs-6 p-2">
                                                @if($verificationResult['data']['status'] == 'cleared')
                                                    <i class="fas fa-check-circle"></i> CLEARED FOR EXAM
                                                @elseif($verificationResult['data']['status'] == 'already_used')
                                                    <i class="fas fa-exclamation-triangle"></i> ALREADY VERIFIED
                                                @elseif($verificationResult['data']['status'] == 'inactive')
                                                    <i class="fas fa-ban"></i> TICKET INACTIVE
                                                @elseif($verificationResult['data']['status'] == 'expired')
                                                    <i class="fas fa-clock"></i> TICKET EXPIRED
                                                @else
                                                    <i class="fas fa-times-circle"></i> DISQUALIFIED FOR EXAM
                                                @endif
                                            </span>
                                            
                                            @if($verificationResult['data']['status'] == 'already_used' && isset($verificationResult['data']['verified_at']))
                                                <p class="mt-2">
                                                    <strong>Previously Verified:</strong> 
                                                    {{ \Carbon\Carbon::parse($verificationResult['data']['verified_at'])->format('d M Y, h:i A') }}
                                                </p>
                                            @endif
                                            
                                            @if($verificationResult['data']['status'] == 'expired' && isset($verificationResult['data']['expired_at']))
                                                <p class="mt-2">
                                                    <strong>Expired:</strong> 
                                                    {{ \Carbon\Carbon::parse($verificationResult['data']['expired_at'])->format('d M Y, h:i A') }}
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if(isset($verificationResult['data']['exam']))
                                        <div class="mb-3">
                                            <h5>Exam Information:</h5>
                                            <p class="mb-1"><strong>Exam Name:</strong> {{ $verificationResult['data']['exam']['title'] ?? 'N/A' }}</p>
                                            <p class="mb-0"><strong>Date:</strong> {{ isset($verificationResult['data']['exam']['exam_date']) ? \Carbon\Carbon::parse($verificationResult['data']['exam']['exam_date'])->format('d M Y') : 'N/A' }}</p>
                                        </div>
                                    @endif
                                    
                                    <div class="d-flex justify-content-center mt-4">
                                        <button wire:click="resetScanner" class="btn btn-primary btn-lg">
                                            <i class="fas fa-sync"></i> Scan Another Ticket
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Scanner Instructions
                            </h5>
                        </div>
                        <div class="card-body">
                            <ol class="mb-4">
                                <li class="mb-2">Position the student's QR code ticket in front of the camera</li>
                                <li class="mb-2">Wait for automatic scanning (1-2 seconds)</li>
                                <li class="mb-2">Verify student identity matches with displayed photo</li>
                                <li class="mb-2">Check clearance status before allowing entry</li>
                                <li>Click "Scan Another Ticket" for the next student</li>
                            </ol>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Only students with "CLEARED FOR EXAM" status should be allowed entry.
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-key"></i> Manual Verification
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>If scanning fails, enter the ticket code manually:</p>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Enter QR code" aria-label="QR Code" id="manual-qr-input">
                                <button class="btn btn-outline-primary" type="button" id="manual-verify-btn" 
                                    onclick="document.dispatchEvent(new CustomEvent('manual-qr-code', {detail: document.getElementById('manual-qr-input').value}))">
                                    Verify
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @push('styles')
    <style>
        #qr-reader {
            max-width: 100%;
            margin: 0 auto;
        }
        #qr-reader img {
            max-width: 100%;
        }
        #qr-reader video {
            max-width: 100%;
        }
        #qr-reader__scan_region {
            margin-bottom: 10px;
        }
    </style>
    @endpush
    
    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('manual-qr-code', (e) => {
            if (e.detail) {
                @this.call('verifyQrCode', e.detail);
            }
        });
        
        document.addEventListener('verification-complete', () => {
            if (window.scanner) {
                window.scanner.stop();
            }
            
            // Play sound based on verification result
            const success = @js($verificationResult && $verificationResult['success']);
            const audio = new Audio(success ? '/sounds/success.mp3' : '/sounds/error.mp3');
            audio.play();
        });
    </script>
    @endpush
</div>