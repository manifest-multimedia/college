<?php if (isset($component)) { $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.backend.auth','data' => ['title' => 'Confirm Password','description' => 'This is a secure area. Please confirm your password.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('backend.auth'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Confirm Password','description' => 'This is a secure area. Please confirm your password.']); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger mb-4" role="alert">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><?php echo e($error); ?></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="mb-4 text-center text-light-dark fs-13">
        <?php echo e(__('This is a secure area of the application. Please confirm your password before continuing.')); ?>

    </div>

    <form method="POST" action="<?php echo e(route('password.confirm')); ?>" class="px-4 mx-auto mw-sm">
        <?php echo csrf_field(); ?>

        <div class="mb-4">
            <label for="password" class="form-label fs-13 fw-medium text-light-dark">Password</label>
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
                   autofocus 
                   autocomplete="current-password">
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

        <div class="mb-6 row">
            <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                Confirm Password
            </button>
        </div>
    </form>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3)): ?>
<?php $attributes = $__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3; ?>
<?php unset($__attributesOriginal76e9a41796bb2f15b5ba4cee8ec9beb3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3)): ?>
<?php $component = $__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3; ?>
<?php unset($__componentOriginal76e9a41796bb2f15b5ba4cee8ec9beb3); ?>
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/custom-auth/confirm-password.blade.php ENDPATH**/ ?>