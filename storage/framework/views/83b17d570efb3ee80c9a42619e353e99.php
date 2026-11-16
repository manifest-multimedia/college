<?php
    $authService = app(\App\Services\AuthenticationService::class);
?>

<?php if (isset($component)) { $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.auth','data' => ['title' => 'Login','description' => 'Choose your preferred login method']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Login','description' => 'Choose your preferred login method']); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
        <div class="alert alert-success mb-4" role="alert">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger mb-4" role="alert">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="px-4 mx-auto mw-sm">
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.theme_settings.show_auth_central_button', true)): ?>
            <div class="mb-4">
                <a href="<?php echo e($authService->getAuthCentralLoginUrl()); ?>"
                    class="btn btn-lg btn-primary fs-11 w-100 text-primary-light d-flex align-items-center justify-content-center"
                    style="background-color: var(--brand-primary, #007bff); border-color: var(--brand-primary, #007bff);">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In with AuthCentral
                </a>
                <p class="text-center text-muted fs-13 mt-2 mb-0">Single Sign-On (SSO) - Quick & Secure</p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.theme_settings.show_regular_login', true)): ?>
                
                <div class="text-center my-4">
                    <span class="text-muted fs-13 px-3" style="background: white; position: relative; z-index: 1;">
                        OR
                    </span>
                    <hr style="margin-top: -12px; border-color: #dee2e6;">
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.theme_settings.show_regular_login', true)): ?>
            
            <form method="POST" action="<?php echo e(route('regular.login')); ?>">
            <?php echo csrf_field(); ?>
            
            <div class="mb-4">
                <label for="email" class="form-label fs-13 fw-medium text-light-dark">Email Address</label>
                <input type="email" 
                       class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                       id="email" 
                       name="email" 
                       value="<?php echo e(old('email')); ?>" 
                       required 
                       autofocus 
                       autocomplete="username">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fs-13 fw-medium text-light-dark">Password</label>
                <div style="position: relative;">
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
                           autocomplete="current-password"
                           style="padding-right: 3rem;">
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword()"
                            style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1rem; transition: color 0.3s ease;"
                            onmouseover="this.style.color='var(--brand-primary, #007bff)'" 
                            onmouseout="this.style.color='#9ca3af'">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <div class="invalid-feedback">
                        <?php echo e($message); ?>

                    </div>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label fs-13 text-light-dark" for="remember">
                        Remember me
                    </label>
                </div>
                <a class="text-primary link-primary fs-13" href="<?php echo e(route('password.request')); ?>" 
                   style="color: var(--brand-primary, #007bff) !important;">
                    Forgot password?
                </a>
            </div>

            <div class="mb-6">
                <button type="submit" class="btn btn-lg btn-outline-primary fs-11 w-100"
                        style="border-color: var(--brand-primary, #007bff); color: var(--brand-primary, #007bff);">
                    <i class="fas fa-envelope me-2"></i>
                    Sign In with Email
                </button>
            </div>
        </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="text-center">
            <p class="mb-2 fs-13 fw-medium text-light-dark">
                <span>Don't have an account?</span>
            </p>
            <div class="d-flex justify-content-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($authService->getStaffSignupUrl()): ?>
                    <a class="text-primary link-primary fs-13" href="<?php echo e($authService->getStaffSignupUrl()); ?>"
                       style="color: var(--brand-primary, #007bff) !important;">Staff Registration</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($authService->getStudentSignupUrl()): ?>
                    <a class="text-primary link-primary fs-13" href="<?php echo e($authService->getStudentSignupUrl()); ?>"
                       style="color: var(--brand-primary, #007bff) !important;">Student Registration</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.website_url') || config('branding.institution.support_email')): ?>
            <div class="mt-5 pt-4 border-top text-center">
                <div class="d-flex justify-content-center gap-3 text-muted fs-13">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.website_url')): ?>
                        <a href="<?php echo e(config('branding.institution.website_url')); ?>" 
                           target="_blank" 
                           class="text-muted text-decoration-none">
                            <i class="bi bi-globe me-1"></i>Website
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.support_email')): ?>
                        <a href="mailto:<?php echo e(config('branding.institution.support_email')); ?>" 
                           class="text-muted text-decoration-none">
                            <i class="bi bi-envelope me-1"></i>Support
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3)): ?>
<?php $attributes = $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3; ?>
<?php unset($__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3)): ?>
<?php $component = $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3; ?>
<?php unset($__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3); ?>
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/custom-auth/themes/default/login.blade.php ENDPATH**/ ?>