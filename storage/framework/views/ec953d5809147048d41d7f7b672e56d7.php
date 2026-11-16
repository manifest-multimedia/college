<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Preview qualification import']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Preview qualification import']); ?>
    <div class="container-fluid py-5">
        <a href="<?php echo e(route('canteen.lists.index')); ?>" class="btn btn-sm btn-light mb-5">Back to qualification lists</a>
        <h1 class="mb-3">Review qualification import</h1>
        <p>No qualification changes have been saved. This preview expires in 30 minutes.</p>
        <div class="alert alert-primary"><strong><?php echo e($preview['prefix']); ?> · <?php echo e($preview['year']); ?> · Semester <?php echo e($preview['semester']); ?></strong><br><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($preview['activate']): ?>Confirming will also make this the active canteen period.@elseThe active canteen period will remain unchanged.<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><div class="alert alert-danger" role="alert"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="card mb-6"><div class="card-body"><div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Class / workbook</th><th>Students</th><th>Qualified</th><th>Not qualified</th><th>Effect</th></tr></thead><tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $preview['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr><td><strong><?php echo e($file['class']); ?></strong><br><?php echo e($file['file']); ?></td><td><?php echo e($file['rows']); ?></td><td><?php echo e($file['qualified']); ?></td><td><?php echo e($file['not_qualified']); ?></td><td><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file['revoked_replay']): ?><strong class="text-warning">Previously withdrawn file: will remain withdrawn.</strong><?php elseif($file['replayed']): ?>Unchanged file: no duplicate statuses.<?php elseif($file['replaces']): ?><strong class="text-warning">Replaces batches <?php echo e(implode(', ', $file['replaces'])); ?>.</strong><br><?php echo e($file['omitted']); ?> students omitted from the earlier lists.@elseNew qualification list.<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody><tfoot><tr class="fw-bold"><td>Total</td><td><?php echo e(array_sum(array_column($preview['summary'], 'rows'))); ?></td><td><?php echo e(array_sum(array_column($preview['summary'], 'qualified'))); ?></td><td><?php echo e(array_sum(array_column($preview['summary'], 'not_qualified'))); ?></td><td></td></tr></tfoot></table></div></div></div>
        <div class="alert alert-warning">Confirm only reviewed, complete class lists. Students omitted from a replacement lose the earlier list's approval. Existing valid financial evidence can still determine clearance independently. Qualification statuses do not create payments.</div>
        <form method="POST" action="<?php echo e(route('canteen.lists.confirm', $preview['token'])); ?>"><?php echo csrf_field(); ?>
            <label class="form-check mb-5"><input class="form-check-input" type="checkbox" name="acknowledge" value="1" required><span class="form-check-label">I confirm the institution, period, totals and any replacements shown above.</span></label>
            <button class="btn btn-primary" type="submit">Confirm import</button>
            <a class="btn btn-light ms-2" href="<?php echo e(route('canteen.lists.index')); ?>">Cancel</a>
        </form>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/canteen/qualification-preview.blade.php ENDPATH**/ ?>