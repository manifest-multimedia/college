<?php
    $applicationsOpen = $settings?->enabled && $settings->allow_applications && in_array($settings->allocation_method, ['application', 'hybrid'], true);
    $statusBadge = ['pending' => 'warning', 'waitlisted' => 'info', 'approved' => 'success', 'rejected' => 'danger'];
    $statusHelp = [
        'pending' => 'Under review',
        'waitlisted' => 'No bed is free right now; you stay in the queue',
        'approved' => 'A bed has been allocated to you',
        'rejected' => 'Not successful this time',
    ];
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Hostel Applications']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Applications']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'My Hostel Applications','subtitle' => 'Apply for accommodation and track the progress of your requests.','icon' => 'fa-house-user','back' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'My Hostel Applications','subtitle' => 'Apply for accommodation and track the progress of your requests.','icon' => 'fa-house-user','back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
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

        <div class="row g-6">
            <div class="col-xl-5">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Apply for accommodation</h3>
                        <span class="text-muted fs-7 fw-normal">Choose a period and tell us your hostel preference.</span>
                    </div></div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $applicationsOpen): ?>
                            <div class="text-center text-muted py-8">
                                <i class="fas fa-lock fs-2 d-block mb-3"></i>
                                Hostel applications are currently unavailable.
                                <div class="fs-7 mt-1">Please check back later or contact the accommodation office.</div>
                            </div>
                        <?php elseif($periods->isEmpty()): ?>
                            <div class="text-center text-muted py-8">
                                <i class="fas fa-calendar-xmark fs-2 d-block mb-3"></i>
                                No application period is open right now.
                                <div class="fs-7 mt-1">Applications will appear here once a period opens.</div>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('student.hostel.apply')); ?>">
                                <?php echo csrf_field(); ?>
                                <label class="form-label fs-7 fw-bold" for="application-period">Allocation period <span class="text-danger">*</span></label>
                                <select class="form-select mb-4" id="application-period" name="hostel_allocation_period_id" required>
                                    <option value="">Select period</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($period->id); ?>" <?php if(old('hostel_allocation_period_id') == $period->id): echo 'selected'; endif; ?>><?php echo e($period->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>

                                <label class="form-label fs-7 fw-bold" for="application-hostel">Preferred hostel <span class="text-muted fw-normal">(optional)</span></label>
                                <select class="form-select mb-4" id="application-hostel" name="preferred_hostel_id">
                                    <option value="">No preference</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $hostels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hostel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($hostel->id); ?>" <?php if(old('preferred_hostel_id') == $hostel->id): echo 'selected'; endif; ?>><?php echo e($hostel->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>

                                <label class="form-label fs-7 fw-bold" for="application-notes">Notes <span class="text-muted fw-normal">(optional)</span></label>
                                <textarea class="form-control mb-1" id="application-notes" name="notes" rows="4" maxlength="2000" placeholder="Anything the accommodation office should know"><?php echo e(old('notes')); ?></textarea>
                                <div class="text-muted fs-8 text-end mb-4"><span id="notes-count">0</span> / 2000</div>

                                <button class="btn btn-primary w-100"><i class="fas fa-paper-plane me-1"></i> Submit application</button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Application history</h3>
                        <span class="text-muted fs-7 fw-normal"><?php echo e($applications->count()); ?> <?php echo e(\Illuminate\Support\Str::plural('application', $applications->count())); ?> submitted</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Period</th><th>Preferred hostel</th><th>Status</th><th>Submitted</th></tr></thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="fw-bold text-gray-800"><?php echo e($application->period?->name ?? '—'); ?></td>
                                            <td><?php echo e($application->preferredHostel?->name ?? 'No preference'); ?></td>
                                            <td>
                                                <span class="badge badge-light-<?php echo e($statusBadge[$application->status] ?? 'secondary'); ?>"><?php echo e(ucfirst($application->status)); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($statusHelp[$application->status])): ?><div class="text-muted fs-8 mt-1"><?php echo e($statusHelp[$application->status]); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <div><?php echo e($application->created_at->format('d M Y')); ?></div>
                                                <div class="text-muted fs-8"><?php echo e($application->created_at->format('H:i')); ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-10"><i class="fas fa-file-circle-question fs-2 d-block mb-3"></i>You have not applied for accommodation yet.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notes = document.getElementById('application-notes');
            const counter = document.getElementById('notes-count');
            if (notes && counter) {
                const update = () => { counter.textContent = notes.value.length; };
                notes.addEventListener('input', update);
                update();
            }
        });
    </script>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/student-applications.blade.php ENDPATH**/ ?>