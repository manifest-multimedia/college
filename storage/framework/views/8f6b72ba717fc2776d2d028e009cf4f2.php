<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Withdraw qualification list']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Withdraw qualification list']); ?>
    <div class="container-fluid py-5"><a class="btn btn-sm btn-light mb-5" href="<?php echo e(route('canteen.lists.index')); ?>">Back to qualification lists</a>
        <div class="card"><div class="card-body"><h1>Withdraw qualification list #<?php echo e($batch->id); ?></h1><p><?php echo e($batch->class_label); ?> · <?php echo e($batch->academic_year); ?> · Semester <?php echo e($batch->semester); ?><br><?php echo e($batch->file_name); ?></p>
        <div class="alert alert-warning">This list will stop supplying clearance. Its audit history remains. Older approvals will not be reactivated. Valid financial evidence, if available, remains independent.</div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><div class="alert alert-danger"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($batch->revoked_at): ?><p>This list has already been withdrawn or replaced.</p><?php else: ?>
        <form method="POST" action="<?php echo e(route('canteen.lists.revoke', $batch->id)); ?>"><?php echo csrf_field(); ?>
            <label class="form-label" for="reason">Reason for withdrawal</label><textarea class="form-control mb-5" id="reason" name="reason" minlength="5" maxlength="500" required><?php echo e(old('reason')); ?></textarea>
            <label class="form-check mb-5"><input class="form-check-input" type="checkbox" name="acknowledge" value="1" required><span class="form-check-label">I confirm withdrawal of this specific class list and period.</span></label>
            <button class="btn btn-danger" type="submit">Withdraw this list</button>
        </form><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div></div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/canteen/qualification-withdrawal.blade.php ENDPATH**/ ?>