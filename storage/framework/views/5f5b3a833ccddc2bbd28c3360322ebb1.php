<?php
    $queueStatusBadge = ['pending' => 'warning', 'waitlisted' => 'info'];
    $totalInQueue = $counts->sum();
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Hostel Application Approvals']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Application Approvals']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'Hostel Applications','subtitle' => 'Review pending and waitlisted accommodation applications.','icon' => 'fa-clipboard-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Applications','subtitle' => 'Review pending and waitlisted accommodation applications.','icon' => 'fa-clipboard-check']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Pending review','value' => $counts['pending'] ?? 0,'icon' => 'fa-hourglass-half','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pending review','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($counts['pending'] ?? 0),'icon' => 'fa-hourglass-half','color' => 'warning']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Waitlisted','value' => $counts['waitlisted'] ?? 0,'icon' => 'fa-list-ol','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Waitlisted','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($counts['waitlisted'] ?? 0),'icon' => 'fa-list-ol','color' => 'info']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => 'Open allocation periods','value' => $openPeriods->count(),'icon' => 'fa-calendar-check','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Open allocation periods','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($openPeriods->count()),'icon' => 'fa-calendar-check','color' => 'success']); ?>
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

        <div class="card mb-6">
            <div class="card-body">
                <div class="row g-4 align-items-center">
                    <div class="col-lg-6">
                        <h3 class="fw-bold mb-1"><i class="fas fa-wand-magic-sparkles me-2 text-primary"></i>Automatic allocation</h3>
                        <p class="text-muted fs-7 mb-0">Assigns available beds to applicants, oldest applications first. Applicants who have not cleared payment are skipped, and applicants with no free bed are waitlisted when the waiting list is enabled.</p>
                    </div>
                    <div class="col-lg-6">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($openPeriods->isEmpty()): ?>
                            <div class="text-muted fs-7 border border-dashed rounded p-4">There is no open allocation period. Open one from the Periods tab in Hostel Management to run automatic allocation.</div>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('hostels.applications.auto-allocate')); ?>" class="d-flex flex-wrap gap-3" onsubmit="return confirm('Run automatic allocation for the selected period? Beds will be assigned right away.')">
                                <?php echo csrf_field(); ?>
                                <select class="form-select flex-grow-1 w-auto" name="hostel_allocation_period_id" required aria-label="Allocation period">
                                    <option value="">Choose open period</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $openPeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($period->id); ?>"><?php echo e($period->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <button class="btn btn-primary"><i class="fas fa-play me-1"></i> Run allocation</button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-wrap gap-3 py-5">
                <div class="card-title flex-column align-items-start">
                    <h3 class="fw-bold mb-0">Applications awaiting a decision</h3>
                    <span class="text-muted fs-7 fw-normal">Oldest first. Approving an application allocates the chosen bed straight away.</span>
                </div>
                <form method="GET" action="<?php echo e(route('hostels.applications')); ?>">
                    <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Filter by status">
                        <option value="">All (<?php echo e($totalInQueue); ?>)</option>
                        <option value="pending" <?php if($statusFilter === 'pending'): echo 'selected'; endif; ?>>Pending (<?php echo e($counts['pending'] ?? 0); ?>)</option>
                        <option value="waitlisted" <?php if($statusFilter === 'waitlisted'): echo 'selected'; endif; ?>>Waitlisted (<?php echo e($counts['waitlisted'] ?? 0); ?>)</option>
                    </select>
                </form>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                        <thead>
                            <tr class="text-muted fw-bold fs-7 text-uppercase">
                                <th>Applicant</th><th>Period</th><th>Preferred hostel</th><th>Submitted</th><th>Status</th><th class="text-end">Decision</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $applications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $application): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $applicantName = trim(($application->student?->first_name ?? '').' '.($application->student?->last_name ?? '')) ?: 'Unknown student';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-gray-800"><?php echo e($applicantName); ?></div>
                                        <div class="text-muted fs-7"><?php echo e($application->student?->student_id ?? 'ID #'.$application->student_id); ?></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($application->notes)): ?>
                                            <div class="text-muted fs-8 mt-1 fst-italic text-truncate" style="max-width:260px;" title="<?php echo e($application->notes); ?>"><i class="fas fa-comment-dots me-1"></i><?php echo e($application->notes); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td><?php echo e($application->period?->name ?? '—'); ?></td>
                                    <td><?php echo e($application->preferredHostel?->name ?? 'No preference'); ?></td>
                                    <td>
                                        <div><?php echo e($application->created_at->format('d M Y')); ?></div>
                                        <div class="text-muted fs-8"><?php echo e($application->created_at->diffForHumans()); ?></div>
                                    </td>
                                    <td><span class="badge badge-light-<?php echo e($queueStatusBadge[$application->status] ?? 'secondary'); ?>"><?php echo e(ucfirst($application->status)); ?></span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-sm btn-light-success" data-bs-toggle="modal" data-bs-target="#approveModal"
                                                data-action="<?php echo e(route('hostels.applications.decide', $application)); ?>"
                                                data-applicant="<?php echo e($applicantName); ?>"
                                                data-preferred-id="<?php echo e($application->preferred_hostel_id); ?>"
                                                data-preferred-name="<?php echo e($application->preferredHostel?->name); ?>">
                                                <i class="fas fa-check me-1"></i> Approve
                                            </button>
                                            <form method="POST" action="<?php echo e(route('hostels.applications.decide', $application)); ?>" onsubmit="return confirm('Reject the application from <?php echo e(addslashes($applicantName)); ?>?')">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-light-danger"><i class="fas fa-xmark me-1"></i> Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="6" class="text-center text-muted py-10">
                                    <i class="fas fa-inbox fs-2 d-block mb-3"></i>
                                    <?php echo e($statusFilter ? 'No '.$statusFilter.' applications.' : 'No pending applications. You are all caught up.'); ?>

                                </td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4"><?php echo e($applications->links()); ?></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" class="modal-content" id="approve-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="decision" value="approved">
                <div class="modal-header"><h3 class="modal-title fw-bold" id="approveModalLabel">Approve &amp; allocate</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="bg-light rounded p-4 mb-4">
                        <div class="fw-bold text-gray-800" id="approve-applicant">—</div>
                        <div class="text-muted fs-7" id="approve-preference">—</div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($beds->isEmpty()): ?>
                        <div class="alert alert-warning mb-0">There are no active beds to allocate. Add inventory in Hostel Management first.</div>
                    <?php else: ?>
                        <label class="form-label fs-7 fw-bold" for="approve-bed">Bed <span class="text-danger">*</span></label>
                        <select class="form-select mb-2" id="approve-bed" name="hostel_bed_id" required>
                            <option value="">Select a bed</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $beds->groupBy(fn ($bed) => $bed->room?->hostel?->name); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hostelName => $hostelBeds): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <optgroup label="<?php echo e($hostelName); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $hostelBeds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($bed->id); ?>" data-hostel-id="<?php echo e($bed->room?->hostel_id); ?>">Room <?php echo e($bed->room?->room_number); ?> / Bed <?php echo e($bed->bed_code); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </optgroup>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <div class="form-check form-check-custom form-check-solid d-none" id="approve-all-wrapper">
                            <input class="form-check-input" type="checkbox" id="approve-show-all">
                            <label class="form-check-label fs-7" for="approve-show-all">Show beds from all hostels</label>
                        </div>
                        <div class="form-text">A bed is required to approve. Availability is checked again when you confirm.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" <?php if($beds->isEmpty()): echo 'disabled'; endif; ?>>Approve &amp; allocate</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('approveModal');
            if (!modal) { return; }
            const bedSelect = document.getElementById('approve-bed');
            const showAll = document.getElementById('approve-show-all');
            const allWrapper = document.getElementById('approve-all-wrapper');
            let preferredId = '';

            const applyBedFilter = () => {
                if (!bedSelect) { return; }
                const restrict = preferredId !== '' && !showAll.checked;
                bedSelect.querySelectorAll('option[data-hostel-id]').forEach((option) => {
                    const hidden = restrict && option.dataset.hostelId !== preferredId;
                    option.hidden = hidden;
                    option.disabled = hidden;
                });
                bedSelect.querySelectorAll('optgroup').forEach((group) => {
                    group.hidden = Array.from(group.querySelectorAll('option')).every((option) => option.hidden);
                });
                bedSelect.value = '';
            };

            modal.addEventListener('show.bs.modal', (event) => {
                const trigger = event.relatedTarget;
                if (!trigger) { return; }
                document.getElementById('approve-form').action = trigger.dataset.action;
                document.getElementById('approve-applicant').textContent = trigger.dataset.applicant;
                preferredId = trigger.dataset.preferredId || '';
                document.getElementById('approve-preference').textContent = preferredId
                    ? 'Prefers ' + trigger.dataset.preferredName
                    : 'No hostel preference';
                if (showAll) { showAll.checked = false; }
                if (allWrapper) { allWrapper.classList.toggle('d-none', preferredId === ''); }
                applyBedFilter();
            });
            if (showAll) { showAll.addEventListener('change', applyBedFilter); }
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/applications.blade.php ENDPATH**/ ?>