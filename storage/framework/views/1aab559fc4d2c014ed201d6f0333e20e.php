<?php
    $statusBadge = ['allocated' => 'primary', 'checked_in' => 'success', 'checked_out' => 'secondary', 'released' => 'warning', 'transferred' => 'info'];
    $awaitingCheckIn = $summary['allocated'] ?? 0;
    $checkedIn = $summary['checked_in'] ?? 0;
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Hostel Warden']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Warden']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'My Hostel Occupancy','subtitle' => 'Showing allocations for '.$hostelNames->join(', ', ' and ').'. Only hostels assigned to you are displayed.','icon' => 'fa-user-shield','back' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Hostel Occupancy','subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Showing allocations for '.$hostelNames->join(', ', ' and ').'. Only hostels assigned to you are displayed.'),'icon' => 'fa-user-shield','back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Awaiting check-in','value' => $awaitingCheckIn,'icon' => 'fa-hourglass-half','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Awaiting check-in','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($awaitingCheckIn),'icon' => 'fa-hourglass-half','color' => 'primary']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Checked in','value' => $checkedIn,'icon' => 'fa-right-to-bracket','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Checked in','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($checkedIn),'icon' => 'fa-right-to-bracket','color' => 'success']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Total allocations','value' => $summary->sum(),'icon' => 'fa-bed','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total allocations','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary->sum()),'icon' => 'fa-bed','color' => 'info']); ?>
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
            <div class="card-header flex-wrap gap-3 py-5">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-0">Allocations</h3>
                    <span class="text-muted fs-7 fw-normal"><?php echo e($allocations->total()); ?> <?php echo e(\Illuminate\Support\Str::plural('record', $allocations->total())); ?><?php echo e($statusFilter ? ' with status "'.str_replace('_', ' ', $statusFilter).'"' : ''); ?></span>
                </div>
                <form method="GET" action="<?php echo e(route('warden.hostels.index')); ?>">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Filter by status">
                        <option value="">All statuses</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['allocated', 'checked_in', 'checked_out', 'released', 'transferred']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($status); ?>" <?php if($statusFilter === $status): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </form>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Student</th><th>Hostel</th><th>Room / Bed</th><th>Status</th><th class="text-end">Action</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allocation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php $studentName = trim(($allocation->first_name ?? '').' '.($allocation->last_name ?? '')) ?: 'Unknown student'; ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800"><?php echo e($studentName); ?></div>
                                        <div class="text-muted fs-7"><?php echo e($allocation->student_number); ?></div>
                                    </td>
                                    <td><?php echo e($allocation->hostel_name); ?></td>
                                    <td>Room <?php echo e($allocation->room_number); ?> &middot; Bed <?php echo e($allocation->bed_code); ?></td>
                                    <td>
                                        <span class="badge badge-light-<?php echo e($statusBadge[$allocation->status] ?? 'secondary'); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $allocation->status))); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allocation->status === 'checked_in' && $allocation->checked_in_at): ?>
                                            <div class="text-muted fs-8 mt-1">since <?php echo e($allocation->checked_in_at->format('d M Y')); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($allocation->status, ['allocated', 'checked_in'], true)): ?>
                                            <form method="POST" action="<?php echo e(route('warden.hostels.transition', $allocation->id)); ?>" <?php if($allocation->status === 'checked_in'): ?> onsubmit="return confirm('Check <?php echo e(addslashes($studentName)); ?> out of this bed?')" <?php endif; ?>>
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="action" value="<?php echo e($allocation->status === 'allocated' ? 'check_in' : 'check_out'); ?>">
                                                <button class="btn btn-sm btn-<?php echo e($allocation->status === 'allocated' ? 'light-success' : 'light-primary'); ?>">
                                                    <i class="fas <?php echo e($allocation->status === 'allocated' ? 'fa-right-to-bracket' : 'fa-right-from-bracket'); ?> me-1"></i><?php echo e($allocation->status === 'allocated' ? 'Check in' : 'Check out'); ?>

                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="5" class="text-center text-muted py-10"><i class="fas fa-bed fs-2 d-block mb-3"></i><?php echo e($statusFilter ? 'No allocations with this status.' : 'No allocations in your hostels yet.'); ?></td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4"><?php echo e($allocations->links()); ?></div>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/warden.blade.php ENDPATH**/ ?>