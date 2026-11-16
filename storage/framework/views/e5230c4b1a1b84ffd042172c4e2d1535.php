<?php if (isset($component)) { $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.auth','data' => ['title' => 'Change Password','description' => 'Set your personal password to continue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Change Password','description' => 'Set your personal password to continue']); ?>
    <div class="px-3 px-sm-4 mx-auto mw-sm">
        
        <div class="alert alert-warning d-flex align-items-center mb-4 p-3 rounded-3 shadow-sm border border-warning border-opacity-50" role="alert">
            <i class="bi bi-shield-lock-fill fs-3 text-warning me-3 flex-shrink-0"></i>
            <div class="fs-13">
                <span class="fw-bold d-block text-gray-900 mb-1">Temporary Credentials Detected</span>
                Your account password was recently reset by the System Administrator. For your security, you must set a new personal password before accessing the system.
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-danger mb-4 p-3 rounded-3" role="alert">
                <div class="fw-bold mb-1 fs-13"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following:</div>
                <ul class="mb-0 ps-3 fs-13">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.force-change.update')); ?>">
            <?php echo csrf_field(); ?>

            <div class="mb-3">
                <label class="form-label fs-13 fw-semibold text-gray-700">Account</label>
                <input type="text" class="form-control bg-light" value="<?php echo e(auth()->user()->email); ?>" disabled readonly>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fs-13 fw-semibold text-gray-700">New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="new-password"
                           placeholder="At least 8 characters">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="text-danger fs-12 mt-1"><?php echo e($message); ?></div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fs-13 fw-semibold text-gray-700">Confirm New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password"
                           placeholder="Re-enter your new password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save New Password & Proceed
                </button>
            </div>
        </form>

        <div class="text-center pt-2">
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-link text-muted fs-13 text-decoration-none p-0">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out Instead
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3)): ?>
<?php $attributes = $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3; ?>
<?php unset($__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3)): ?>
<?php $component = $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3; ?>
<?php unset($__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3); ?>
<?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/custom-auth/force-password-change.blade.php ENDPATH**/ ?>