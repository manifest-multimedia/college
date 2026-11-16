<?php if (isset($component)) { $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.auth','data' => ['title' => 'Verify Email','description' => 'Please verify your email address']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Verify Email','description' => 'Please verify your email address']); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status') == 'verification-link-sent'): ?>
        <div class="alert alert-success mb-4" role="alert">
            <?php echo e(__('A new verification link has been sent to the email address you provided in your profile settings.')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-4 text-center text-light-dark fs-13">
        <?php echo e(__('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.')); ?>

    </div>

    <div class="px-4 mx-auto mw-sm">
        <form method="POST" action="<?php echo e(route('verification.send')); ?>" class="mb-4">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                Resend Verification Email
            </button>
        </form>

        <div class="text-center">
            <a href="<?php echo e(route('profile.show')); ?>" class="text-primary link-primary fs-13 me-3">
                Edit Profile
            </a>
            
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-link text-primary link-primary fs-13 p-0">
                    Log Out
                </button>
            </form>
        </div>
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
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/custom-auth/verify-email.blade.php ENDPATH**/ ?>