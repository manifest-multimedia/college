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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($verificationStep === 'id'): ?>
                            <!-- Step 1: Student ID Verification -->
                            <h5>Enter Student ID to Vote</h5>
                            <p>Please enter your Student ID to verify your eligibility to vote in this election.</p>
                            
                            <form wire:submit.prevent="verify" class="mb-4">
                                <div class="mb-3">
                                    <label for="student_id" class="form-label">Student ID</label>
                                    <input type="text" wire:model="student_id" class="form-control <?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="student_id" placeholder="Enter your Student ID" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['student_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
                                    <div class="alert alert-danger"><?php echo e($errorMessage); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check-circle me-1"></i> Verify ID
                                </button>
                            </form>
                        <?php elseif($verificationStep === 'security'): ?>
                            <!-- Step 2: Security Question Verification -->
                            <h5>Identity Verification</h5>
                            <p>To verify your identity, please answer the following security question:</p>
                            
                            <div class="alert alert-info">
                                <strong>Security Question:</strong> What is your <?php echo e($securityQuestion); ?>?
                            </div>
                            
                            <form wire:submit.prevent="verifySecurityQuestion" class="mb-4">
                                <div class="mb-3">
                                    <label for="securityAnswer" class="form-label">Your Answer</label>
                                    <input type="text" wire:model="securityAnswer" class="form-control <?php $__errorArgs = ['securityAnswer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="securityAnswer" placeholder="Enter your answer" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['securityAnswer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errorMessage): ?>
                                    <div class="alert alert-danger"><?php echo e($errorMessage); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <div class="d-flex">
                                    <button type="button" wire:click="resetVerification" class="btn btn-secondary me-2">
                                        <i class="fas fa-arrow-left me-1"></i> Back
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-check-circle me-1"></i> Verify
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="mt-4">
                            <h6>Election: <?php echo e($election->title); ?></h6>
                            <p>
                                <strong>Start:</strong> <?php echo e($election->start_time->format('M j, Y g:i A')); ?><br>
                                <strong>End:</strong> <?php echo e($election->end_time->format('M j, Y g:i A')); ?>

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
                <a href="<?php echo e(route('home')); ?>" class="text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i> 
                    Return to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Audio element for error alert (hidden) -->
    <audio id="error-alert" src="<?php echo e(asset('sounds/error_alert.mp3')); ?>" preload="auto"></audio>
    <script>
        document.addEventListener('livewire:initialized', () => {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('redirect', (event) => {
                window.location.href = event.url;
            });

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('redirectToUrl', (event) => {
                setTimeout(() => {
                    window.location.href = event.url;
                }, 100);
            });
            
            // Listen for verification failure events
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('verification-failed', () => {
                const audioElement = document.getElementById('error-alert');
                if (audioElement) {
                    audioElement.currentTime = 0;
                    audioElement.play();
                }
            });
            
            // Stop sound when key combinations are pressed
            document.addEventListener('keydown', (e) => {
                const audioElement = document.getElementById('error-alert');
                if (audioElement && (e.ctrlKey || e.metaKey || e.altKey)) {
                    audioElement.pause();
                    audioElement.currentTime = 0;
                }
            });
        });
    </script>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/election-voter-verification.blade.php ENDPATH**/ ?>