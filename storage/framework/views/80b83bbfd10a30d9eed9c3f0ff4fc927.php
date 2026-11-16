<?php
    $planRows = collect($plan);
    $readyCount = $planRows->whereNull('reason')->count();
    $skippedCount = $planRows->whereNotNull('reason')->count();
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Preview Hostel Allocation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Preview Hostel Allocation']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'Review proposed allocations','subtitle' => 'Assignments are provisional. Availability and payment eligibility are checked again when you confirm.','icon' => 'fa-list-check','backUrl' => route('hostels.bulk.index'),'backLabel' => 'Bulk Allocation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Review proposed allocations','subtitle' => 'Assignments are provisional. Availability and payment eligibility are checked again when you confirm.','icon' => 'fa-list-check','back-url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('hostels.bulk.index')),'back-label' => 'Bulk Allocation']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f)): ?>
<?php $attributes = $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f; ?>
<?php unset($__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4321498ab9bdbd94c6d83d40c932a39f)): ?>
<?php $component = $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f; ?>
<?php unset($__componentOriginal4321498ab9bdbd94c6d83d40c932a39f); ?>
<?php endif; ?>

        <?php if (isset($component)) { $__componentOriginalddfadaee5d3f158b4bfc36074640793d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalddfadaee5d3f158b4bfc36074640793d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.alerts','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.alerts'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalddfadaee5d3f158b4bfc36074640793d)): ?>
<?php $attributes = $__attributesOriginalddfadaee5d3f158b4bfc36074640793d; ?>
<?php unset($__attributesOriginalddfadaee5d3f158b4bfc36074640793d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalddfadaee5d3f158b4bfc36074640793d)): ?>
<?php $component = $__componentOriginalddfadaee5d3f158b4bfc36074640793d; ?>
<?php unset($__componentOriginalddfadaee5d3f158b4bfc36074640793d); ?>
<?php endif; ?>

        <div class="row g-4 mb-6">
            <div class="col-6 col-lg-4"><?php if (isset($component)) { $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Ready to allocate','value' => $readyCount,'icon' => 'fa-circle-check','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Ready to allocate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($readyCount),'icon' => 'fa-circle-check','color' => 'success']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65)): ?>
<?php $attributes = $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65; ?>
<?php unset($__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65)): ?>
<?php $component = $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65; ?>
<?php unset($__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65); ?>
<?php endif; ?></div>
            <div class="col-6 col-lg-4"><?php if (isset($component)) { $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Will be skipped','value' => $skippedCount,'icon' => 'fa-forward','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Will be skipped','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($skippedCount),'icon' => 'fa-forward','color' => 'warning']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65)): ?>
<?php $attributes = $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65; ?>
<?php unset($__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65)): ?>
<?php $component = $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65; ?>
<?php unset($__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65); ?>
<?php endif; ?></div>
            <div class="col-12 col-lg-4"><?php if (isset($component)) { $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Students selected','value' => $planRows->count(),'icon' => 'fa-users','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Students selected','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($planRows->count()),'icon' => 'fa-users','color' => 'primary']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65)): ?>
<?php $attributes = $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65; ?>
<?php unset($__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65)): ?>
<?php $component = $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65; ?>
<?php unset($__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65); ?>
<?php endif; ?></div>
        </div>

        <div class="card">
            <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                <h3 class="fw-bold mb-0">Proposed assignments</h3>
                <span class="text-muted fs-7 fw-normal">Nothing is saved until you press Confirm.</span>
            </div></div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Student</th><th>Proposed room / bed</th><th>Outcome</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $planRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800"><?php echo e($row['student_name']); ?></div>
                                        <div class="text-muted fs-7"><?php echo e($row['student_number']); ?></div>
                                    </td>
                                    <td><?php echo e($row['bed_label'] ?? '—'); ?></td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($row['reason'])): ?>
                                            <span class="badge badge-light-success"><i class="fas fa-check me-1"></i>Ready to allocate</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-warning"><i class="fas fa-triangle-exclamation me-1"></i>Skipped</span>
                                            <div class="text-muted fs-7 mt-1"><?php echo e($row['reason']); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
                <span class="text-muted fs-7">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($readyCount > 0): ?>
                        <?php echo e($readyCount); ?> <?php echo e(\Illuminate\Support\Str::plural('student', $readyCount)); ?> will be allocated<?php echo e($skippedCount ? ' and '.$skippedCount.' skipped' : ''); ?>.
                    <?php else: ?>
                        No students can be allocated with the current selection. Go back and adjust it.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
                <form method="POST" action="<?php echo e(route('hostels.bulk.confirm')); ?>" class="d-flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="token" value="<?php echo e($token); ?>">
                    <a class="btn btn-light" href="<?php echo e(route('hostels.bulk.index')); ?>">Cancel</a>
                    <button class="btn btn-primary" <?php if($readyCount === 0): echo 'disabled'; endif; ?>><i class="fas fa-check me-1"></i> Confirm allocations</button>
                </form>
            </div>
        </div>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/bulk-preview.blade.php ENDPATH**/ ?>