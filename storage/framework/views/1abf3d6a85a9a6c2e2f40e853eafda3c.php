<?php
    $setting = fn (string $key, $default = null) => old($key, $settings?->$key ?? $default);
    $enabled = (bool) $setting('enabled', false);
    $requirePayment = (bool) $setting('require_payment', false);

    $workflowOptions = ['staff' => 'Staff-managed', 'application' => 'Applications', 'hybrid' => 'Hybrid', 'automatic' => 'Automatic (future integration)'];
    $unitOptions = ['bed' => 'Bed', 'room' => 'Room (represented by a single allocation unit)'];
    $periodOptions = ['academic_year' => 'Academic year', 'semester' => 'Semester', 'custom' => 'Custom'];
    $ruleToggles = [
        'enabled' => ['Enable module', 'Turn hostel management on or off for the whole institution.', false],
        'enforce_gender' => ['Enforce gender restrictions', 'Only allocate students to hostels that match their gender policy.', true],
        'require_check_in' => ['Require check-in', 'Students must be checked in after they are allocated a bed.', true],
        'allow_applications' => ['Enable applications (future workflow)', 'Let students apply for accommodation from their portal.', false],
        'require_payment' => ['Require payment clearance', 'Blocks allocation until the student has paid the minimum accommodation fee.', false],
        'auto_waitlist' => ['Enable automatic waiting list', 'Place approved applicants on a waiting list when no bed is free.', true],
    ];
    $genderLabels = ['mixed' => 'Mixed', 'male' => 'Male only', 'female' => 'Female only'];
    $allocationStatusBadge = ['allocated' => 'primary', 'checked_in' => 'success', 'checked_out' => 'secondary', 'released' => 'warning', 'transferred' => 'info'];
    $periodStatusBadge = ['draft' => 'secondary', 'open' => 'success', 'closed' => 'danger'];
    $openPeriods = $periods->where('status', 'open');
    $occupancyPercent = $totals['beds'] > 0 ? round($totals['occupied'] / $totals['beds'] * 100) : 0;
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Hostel Management']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Management']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'Hostel Management','subtitle' => 'Configure accommodation, create inventory, and manage student allocations.','back' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Management','subtitle' => 'Configure accommodation, create inventory, and manage student allocations.','back' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
            <a class="btn btn-light-primary" href="<?php echo e(route('hostels.applications')); ?>"><i class="fas fa-clipboard-check me-1"></i> Application Approvals</a>
            <a class="btn btn-light-primary" href="<?php echo e(route('hostels.operations')); ?>"><i class="fas fa-chart-column me-1"></i> Operations &amp; Reports</a>
            <a class="btn btn-light-primary" href="<?php echo e(route('hostels.bulk.index')); ?>"><i class="fas fa-users me-1"></i> Bulk Allocation &amp; Student Search</a>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($settings?->enabled)): ?>
            <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-3" role="alert">
                <div><i class="fas fa-power-off me-2"></i><strong>Hostel management is switched off.</strong> Students cannot be allocated until the module is enabled in Settings.</div>
                <button type="button" class="btn btn-sm btn-warning" data-goto-tab="tab-settings">Open settings</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="row g-4 mb-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = [
                ['Hostels', $totals['hostels'], 'fa-building', 'primary'],
                ['Rooms', $totals['rooms'], 'fa-door-open', 'info'],
                ['Beds', $totals['beds'], 'fa-bed', 'success'],
                ['Open periods', $totals['openPeriods'], 'fa-calendar-check', 'warning'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $icon, $color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-6 col-lg-3"><?php if (isset($component)) { $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.stat','data' => ['label' => $label,'value' => $value,'icon' => $icon,'color' => $color]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($label),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($value),'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($icon),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($color)]); ?>
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
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="card mb-6">
            <div class="card-body py-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-gray-800">Overall occupancy</span>
                    <span class="text-muted fs-7"><?php echo e($totals['occupied']); ?> of <?php echo e($totals['beds']); ?> beds in use (<?php echo e($occupancyPercent); ?>%)</span>
                </div>
                <div class="progress" style="height:8px;" role="progressbar" aria-valuenow="<?php echo e($occupancyPercent); ?>" aria-valuemin="0" aria-valuemax="100">
                    <div class="progress-bar bg-primary" style="width: <?php echo e($occupancyPercent); ?>%"></div>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-6 fs-6" id="hostel-tabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="tab-allocations" data-bs-toggle="tab" data-bs-target="#pane-allocations" type="button" role="tab"><i class="fas fa-bed me-2"></i>Allocations</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="tab-inventory" data-bs-toggle="tab" data-bs-target="#pane-inventory" type="button" role="tab"><i class="fas fa-building me-2"></i>Inventory</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="tab-periods" data-bs-toggle="tab" data-bs-target="#pane-periods" type="button" role="tab"><i class="fas fa-calendar-days me-2"></i>Periods</button></li>
            <li class="nav-item" role="presentation"><button class="nav-link" id="tab-settings" data-bs-toggle="tab" data-bs-target="#pane-settings" type="button" role="tab"><i class="fas fa-sliders me-2"></i>Settings</button></li>
        </ul>

        <div class="tab-content">

            
            <div class="tab-pane fade show active" id="pane-allocations" role="tabpanel">
                <div class="card">
                    <div class="card-header flex-wrap gap-3 py-5">
                        <div class="card-title flex-column align-items-start">
                            <h3 class="fw-bold mb-0">Recent allocations</h3>
                            <span class="text-muted fs-7 fw-normal"><?php echo e($allocations->total()); ?> <?php echo e(\Illuminate\Support\Str::plural('allocation', $allocations->total())); ?><?php echo e($statusFilter ? ' with status "'.str_replace('_', ' ', $statusFilter).'"' : ''); ?></span>
                        </div>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <form method="GET" action="<?php echo e(route('hostels.index')); ?>">
                                <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="Filter by status">
                                    <option value="">All statuses</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['allocated', 'checked_in', 'checked_out', 'released', 'transferred']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status); ?>" <?php if($statusFilter === $status): echo 'selected'; endif; ?>><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </form>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal"><i class="fas fa-plus me-1"></i> Allocate student</button>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>Student</th><th>Accommodation</th><th>Period</th><th>Status</th><th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allocation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $studentName = trim(($allocation->student?->first_name ?? '').' '.($allocation->student?->last_name ?? '')) ?: 'Unknown student';
                                            $bed = $allocation->bed;
                                            $placement = $bed ? ($bed->room?->hostel?->name.' / Room '.$bed->room?->room_number.' / Bed '.$bed->bed_code) : 'Bed #'.$allocation->hostel_bed_id;
                                            $isActive = in_array($allocation->status, ['allocated', 'checked_in'], true);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-gray-800"><?php echo e($studentName); ?></div>
                                                <div class="text-muted fs-7"><?php echo e($allocation->student?->student_id ?? 'ID #'.$allocation->student_id); ?></div>
                                            </td>
                                            <td>
                                                <div class="text-gray-800"><?php echo e($bed?->room?->hostel?->name ?? 'Hostel unavailable'); ?></div>
                                                <div class="text-muted fs-7">Room <?php echo e($bed?->room?->room_number ?? '—'); ?> &middot; Bed <?php echo e($bed?->bed_code ?? $allocation->hostel_bed_id); ?></div>
                                            </td>
                                            <td><?php echo e($allocation->period?->name ?? '—'); ?></td>
                                            <td>
                                                <span class="badge badge-light-<?php echo e($allocationStatusBadge[$allocation->status] ?? 'secondary'); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $allocation->status))); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allocation->status === 'checked_in' && $allocation->checked_in_at): ?>
                                                    <div class="text-muted fs-8 mt-1">since <?php echo e($allocation->checked_in_at->format('d M Y')); ?></div>
                                                <?php elseif($allocation->status === 'checked_out' && $allocation->checked_out_at): ?>
                                                    <div class="text-muted fs-8 mt-1">on <?php echo e($allocation->checked_out_at->format('d M Y')); ?></div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isActive): ?>
                                                    <div class="d-inline-flex gap-2">
                                                        <form method="POST" action="<?php echo e(route('hostels.transition', $allocation)); ?>" <?php if($allocation->status === 'checked_in'): ?> onsubmit="return confirm('Check <?php echo e(addslashes($studentName)); ?> out of this bed?')" <?php endif; ?>>
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="action" value="<?php echo e($allocation->status === 'allocated' ? 'check_in' : 'check_out'); ?>">
                                                            <button type="submit" class="btn btn-sm btn-<?php echo e($allocation->status === 'allocated' ? 'light-success' : 'light-primary'); ?>">
                                                                <i class="fas <?php echo e($allocation->status === 'allocated' ? 'fa-right-to-bracket' : 'fa-right-from-bracket'); ?> me-1"></i><?php echo e($allocation->status === 'allocated' ? 'Check in' : 'Check out'); ?>

                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-light-warning" data-bs-toggle="modal" data-bs-target="#transferModal"
                                                            data-action="<?php echo e(route('hostels.allocations.transfer', $allocation)); ?>"
                                                            data-student="<?php echo e($studentName); ?>" data-current="<?php echo e($placement); ?>">
                                                            <i class="fas fa-right-left me-1"></i> Transfer
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-10">
                                            <i class="fas fa-bed fs-2 d-block mb-3"></i>
                                            <?php echo e($statusFilter ? 'No allocations with this status.' : 'No allocations yet.'); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($statusFilter)): ?>
                                                <div class="mt-3"><button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#allocateModal">Allocate the first student</button></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4"><?php echo e($allocations->links()); ?></div>
                    </div>
                </div>
            </div>

            
            <div class="tab-pane fade" id="pane-inventory" role="tabpanel">
                <div class="row g-4 mb-6">
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1"><span class="badge badge-circle badge-primary me-2">1</span>Add hostel</h3>
                            <p class="text-muted fs-7 mb-4">Start with the building itself.</p>
                            <form method="POST" action="<?php echo e(route('hostels.hostel')); ?>"><?php echo csrf_field(); ?>
                                <input class="form-control mb-3" name="name" value="<?php echo e(old('name')); ?>" placeholder="Hostel name" required>
                                <input class="form-control mb-3" name="code" value="<?php echo e(old('code')); ?>" placeholder="Unique code, e.g. BLK-A" required>
                                <input class="form-control mb-3" name="location" value="<?php echo e(old('location')); ?>" placeholder="Location (optional)">
                                <select class="form-select mb-4" name="gender_policy" aria-label="Gender policy">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['mixed' => 'Mixed', 'male' => 'Male', 'female' => 'Female']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" <?php if(old('gender_policy', 'mixed') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <button class="btn btn-primary w-100">Create hostel</button>
                            </form>
                        </div></div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1"><span class="badge badge-circle badge-primary me-2">2</span>Add room</h3>
                            <p class="text-muted fs-7 mb-4">Rooms belong to a hostel.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hostels->isEmpty()): ?>
                                <div class="text-muted fs-7 border border-dashed rounded p-4">Create a hostel first, then you can add rooms to it.</div>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route('hostels.room')); ?>"><?php echo csrf_field(); ?>
                                    <select class="form-select mb-3" name="hostel_id" required aria-label="Hostel">
                                        <option value="">Choose hostel</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $hostels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hostel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($hostel->id); ?>" <?php if(old('hostel_id') == $hostel->id): echo 'selected'; endif; ?>><?php echo e($hostel->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <input class="form-control mb-3" name="room_number" value="<?php echo e(old('room_number')); ?>" placeholder="Room number" required>
                                    <input class="form-control mb-4" name="floor" value="<?php echo e(old('floor')); ?>" placeholder="Floor (optional)">
                                    <button class="btn btn-primary w-100">Create room</button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div></div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1"><span class="badge badge-circle badge-primary me-2">3</span>Add bed</h3>
                            <p class="text-muted fs-7 mb-4">Beds are what students are allocated to.</p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rooms->isEmpty()): ?>
                                <div class="text-muted fs-7 border border-dashed rounded p-4">Create a room first, then you can add beds to it.</div>
                            <?php else: ?>
                                <form method="POST" action="<?php echo e(route('hostels.bed')); ?>"><?php echo csrf_field(); ?>
                                    <select class="form-select mb-3" name="hostel_room_id" required aria-label="Room">
                                        <option value="">Choose room</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $rooms->sortBy(fn ($room) => $room->hostel?->name.'|'.$room->room_number, SORT_NATURAL | SORT_FLAG_CASE); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $room): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($room->id); ?>" <?php if(old('hostel_room_id') == $room->id): echo 'selected'; endif; ?>><?php echo e($room->hostel?->name); ?> / <?php echo e($room->room_number); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <input class="form-control mb-4" name="bed_code" value="<?php echo e(old('bed_code')); ?>" placeholder="Bed code, e.g. A or 1" required>
                                    <button class="btn btn-primary w-100">Create bed</button>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Hostels</h3>
                        <span class="text-muted fs-7 fw-normal">Capacity and occupancy at a glance.</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Hostel</th><th>Location</th><th>Gender policy</th><th class="text-center">Rooms</th><th class="text-center">Beds</th><th style="min-width:180px;">Occupancy</th></tr></thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $hostels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hostel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php
                                            $stats = $hostelStats[$hostel->id];
                                            $hostelPercent = $stats['beds'] > 0 ? round($stats['occupied'] / $stats['beds'] * 100) : 0;
                                        ?>
                                        <tr>
                                            <td><div class="fw-bold text-gray-800"><?php echo e($hostel->name); ?></div><div class="text-muted fs-7"><?php echo e($hostel->code); ?></div></td>
                                            <td><?php echo e($hostel->location ?: '—'); ?></td>
                                            <td><span class="badge badge-light-<?php echo e(['male' => 'primary', 'female' => 'danger', 'mixed' => 'info'][$hostel->gender_policy] ?? 'secondary'); ?>"><?php echo e($genderLabels[$hostel->gender_policy] ?? ucfirst($hostel->gender_policy)); ?></span></td>
                                            <td class="text-center"><?php echo e($stats['rooms']); ?></td>
                                            <td class="text-center"><?php echo e($stats['beds']); ?></td>
                                            <td>
                                                <div class="d-flex justify-content-between fs-8 text-muted mb-1"><span><?php echo e($stats['occupied']); ?> / <?php echo e($stats['beds']); ?></span><span><?php echo e($hostelPercent); ?>%</span></div>
                                                <div class="progress" style="height:6px;"><div class="progress-bar bg-<?php echo e($hostelPercent >= 90 ? 'danger' : ($hostelPercent >= 70 ? 'warning' : 'success')); ?>" style="width: <?php echo e($hostelPercent); ?>%"></div></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="6" class="text-center text-muted py-10"><i class="fas fa-building fs-2 d-block mb-3"></i>No hostels yet. Add one above to get started.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="tab-pane fade" id="pane-periods" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="card h-100"><div class="card-body">
                            <h3 class="fw-bold mb-1">New allocation period</h3>
                            <p class="text-muted fs-7 mb-4">Students can only be allocated while a period is open.</p>
                            <form method="POST" action="<?php echo e(route('hostels.period')); ?>"><?php echo csrf_field(); ?>
                                <label class="form-label fs-7 fw-bold" for="period-name">Period name</label>
                                <input id="period-name" class="form-control mb-3" name="name" placeholder="e.g. 2026/2027 Semester 1" required>
                                <div class="row g-3 mb-3">
                                    <div class="col"><label class="form-label fs-7 fw-bold" for="period-start">Start</label><input id="period-start" type="date" class="form-control" name="starts_on" value="<?php echo e(old('starts_on')); ?>" required></div>
                                    <div class="col"><label class="form-label fs-7 fw-bold" for="period-end">End</label><input id="period-end" type="date" class="form-control" name="ends_on" value="<?php echo e(old('ends_on')); ?>" required></div>
                                </div>
                                <label class="form-label fs-7 fw-bold" for="period-status">Status</label>
                                <select id="period-status" class="form-select mb-4" name="status">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['draft' => 'Draft', 'open' => 'Open for allocation', 'closed' => 'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" <?php if(old('status', 'draft') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <button class="btn btn-primary w-100">Create period</button>
                            </form>
                        </div></div>
                    </div>
                    <div class="col-lg-8">
                        <div class="card h-100">
                            <div class="card-header py-5"><div class="card-title"><h3 class="fw-bold mb-0">Allocation periods</h3></div></div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table class="table table-row-bordered align-middle gy-4 mb-0">
                                        <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Period</th><th>Dates</th><th>Status</th></tr></thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr>
                                                    <td class="fw-bold text-gray-800"><?php echo e($period->name); ?></td>
                                                    <td><?php echo e($period->starts_on->format('d M Y')); ?> &ndash; <?php echo e($period->ends_on->format('d M Y')); ?></td>
                                                    <td><span class="badge badge-light-<?php echo e($periodStatusBadge[$period->status] ?? 'secondary'); ?>"><?php echo e(ucfirst($period->status)); ?></span></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr><td colspan="3" class="text-center text-muted py-10"><i class="fas fa-calendar-days fs-2 d-block mb-3"></i>No allocation periods yet.</td></tr>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="tab-pane fade" id="pane-settings" role="tabpanel">
                <form method="POST" action="<?php echo e(route('hostels.settings')); ?>" class="card">
                    <?php echo csrf_field(); ?>
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Institution settings</h3>
                        <span class="text-muted fs-7 fw-normal">How accommodation is offered and allocated across the institution.</span>
                    </div></div>
                    <div class="card-body">
                        <div class="fw-bold text-gray-800 mb-3">Workflow</div>
                        <div class="row g-4 mb-8">
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-bold" for="allocation_method">Allocation workflow</label>
                                <select class="form-select" id="allocation_method" name="allocation_method">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $workflowOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>" <?php if($setting('allocation_method', 'staff') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-bold" for="allocation_unit">Allocation unit</label>
                                <select class="form-select" id="allocation_unit" name="allocation_unit">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $unitOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>" <?php if($setting('allocation_unit', 'bed') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-7 fw-bold" for="period_type">Period</label>
                                <select class="form-select" id="period_type" name="period_type">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periodOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>" <?php if($setting('period_type', 'academic_year') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="fw-bold text-gray-800 mb-3">Rules</div>
                        <div class="row g-3 mb-8">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ruleToggles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => [$label, $help, $default]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6">
                                    <input type="hidden" name="<?php echo e($key); ?>" value="0">
                                    <label class="d-flex justify-content-between align-items-center border rounded p-4 h-100 cursor-pointer" for="setting-<?php echo e($key); ?>">
                                        <span class="pe-4"><span class="d-block fw-bold text-gray-800"><?php echo e($label); ?></span><span class="d-block text-muted fs-7"><?php echo e($help); ?></span></span>
                                        <span class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" role="switch" id="setting-<?php echo e($key); ?>" name="<?php echo e($key); ?>" value="1" <?php if((bool) $setting($key, $default)): echo 'checked'; endif; ?>>
                                        </span>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div id="payment-settings" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['d-none' => ! $requirePayment]); ?>">
                            <div class="fw-bold text-gray-800 mb-3">Payment clearance</div>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold" for="fee_type_id">Accommodation fee type</label>
                                    <select name="fee_type_id" id="fee_type_id" class="form-select">
                                        <option value="">Not configured</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $feeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feeType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($feeType->id); ?>" <?php if($setting('fee_type_id') == $feeType->id): echo 'selected'; endif; ?>><?php echo e($feeType->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <div class="form-text">Required while payment clearance is on.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-7 fw-bold" for="minimum_payment_percent">Minimum payment (%)</label>
                                    <input class="form-control" id="minimum_payment_percent" type="number" min="1" max="100" name="minimum_payment_percent" value="<?php echo e($setting('minimum_payment_percent', 100)); ?>">
                                    <div class="form-text">Share of the fee a student must have paid before allocation.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end"><button class="btn btn-primary"><i class="fas fa-floppy-disk me-1"></i> Save settings</button></div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="allocateModal" tabindex="-1" aria-labelledby="allocateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="<?php echo e(route('hostels.allocate')); ?>" class="modal-content">
                <?php echo csrf_field(); ?>
                <div class="modal-header"><h3 class="modal-title fw-bold" id="allocateModalLabel">Allocate student</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($openPeriods->isEmpty()): ?>
                        <div class="alert alert-warning mb-4">There is no open allocation period. Open one in the <a href="#" data-goto-tab="tab-periods" data-bs-dismiss="modal">Periods</a> tab first.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($beds->isEmpty()): ?>
                        <div class="alert alert-warning mb-4">There are no beds yet. Add some in the <a href="#" data-goto-tab="tab-inventory" data-bs-dismiss="modal">Inventory</a> tab first.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <label class="form-label fs-7 fw-bold" for="hostel-student-search">Student</label>
                    <input type="search" class="form-control mb-2" id="hostel-student-search" placeholder="Search by name or student ID" autocomplete="off" required>
                    <select class="form-select mb-1" id="hostel-student-result" name="student_id" required aria-label="Matching students">
                        <option value="">Type at least 2 characters to find students</option>
                    </select>
                    <div class="form-text mb-4" id="hostel-student-hint">Pick a student from the matches.</div>

                    <label class="form-label fs-7 fw-bold" for="allocate-bed">Bed</label>
                    <select class="form-select mb-4" id="allocate-bed" name="hostel_bed_id" required>
                        <option value="">Select bed</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $beds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($bed->id); ?>"><?php echo e($bed->room?->hostel?->name); ?> / Room <?php echo e($bed->room?->room_number); ?> / Bed <?php echo e($bed->bed_code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>

                    <label class="form-label fs-7 fw-bold" for="allocate-period">Period</label>
                    <select class="form-select" id="allocate-period" name="hostel_allocation_period_id" required>
                        <option value="">Select open period</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $openPeriods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($period->id); ?>"><?php echo e($period->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Allocate</button></div>
            </form>
        </div>
    </div>

    
    <div class="modal fade" id="transferModal" tabindex="-1" aria-labelledby="transferModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="" class="modal-content" id="transfer-form">
                <?php echo csrf_field(); ?>
                <div class="modal-header"><h3 class="modal-title fw-bold" id="transferModalLabel">Transfer student</h3><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
                <div class="modal-body">
                    <div class="bg-light rounded p-4 mb-4">
                        <div class="fw-bold text-gray-800" id="transfer-student">—</div>
                        <div class="text-muted fs-7">Currently in <span id="transfer-current">—</span></div>
                    </div>
                    <label class="form-label fs-7 fw-bold" for="transfer-bed">Move to bed</label>
                    <select class="form-select mb-4" id="transfer-bed" name="hostel_bed_id" required>
                        <option value="">Select bed</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $beds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($bed->id); ?>"><?php echo e($bed->room?->hostel?->name); ?> / Room <?php echo e($bed->room?->room_number); ?> / Bed <?php echo e($bed->bed_code); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <label class="form-label fs-7 fw-bold" for="transfer-reason">Reason for transfer <span class="text-muted fw-normal">(optional)</span></label>
                    <input class="form-control" id="transfer-reason" name="reason" maxlength="2000" placeholder="e.g. Maintenance in current room">
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-warning">Transfer</button></div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabKey = 'hostels.activeTab';
            const tabButtons = Array.from(document.querySelectorAll('#hostel-tabs [data-bs-toggle="tab"]'));
            const openTab = (id) => {
                const button = document.getElementById(id);
                if (button && !button.classList.contains('active')) { button.click(); }
            };

            let stored = null;
            try { stored = sessionStorage.getItem(tabKey); } catch (e) {}
            const initial = (location.hash || '').replace('#', '') || stored;
            if (initial && tabButtons.some((button) => button.id === initial)) { openTab(initial); }

            tabButtons.forEach((button) => button.addEventListener('shown.bs.tab', () => {
                try { sessionStorage.setItem(tabKey, button.id); } catch (e) {}
            }));

            document.querySelectorAll('[data-goto-tab]').forEach((link) => link.addEventListener('click', (event) => {
                event.preventDefault();
                openTab(link.dataset.gotoTab);
            }));

            const paymentToggle = document.getElementById('setting-require_payment');
            const paymentSettings = document.getElementById('payment-settings');
            if (paymentToggle && paymentSettings) {
                paymentToggle.addEventListener('change', () => paymentSettings.classList.toggle('d-none', !paymentToggle.checked));
            }

            const transferModal = document.getElementById('transferModal');
            if (transferModal) {
                transferModal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;
                    if (!trigger) { return; }
                    document.getElementById('transfer-form').action = trigger.dataset.action;
                    document.getElementById('transfer-student').textContent = trigger.dataset.student;
                    document.getElementById('transfer-current').textContent = trigger.dataset.current;
                    document.getElementById('transfer-bed').value = '';
                    document.getElementById('transfer-reason').value = '';
                });
            }

            const input = document.getElementById('hostel-student-search');
            const results = document.getElementById('hostel-student-result');
            const hint = document.getElementById('hostel-student-hint');
            if (!input || !results) { return; }
            let timer;
            input.addEventListener('input', () => {
                clearTimeout(timer);
                const term = input.value.trim();
                if (term.length < 2) {
                    results.replaceChildren(new Option('Type at least 2 characters to find students', ''));
                    hint.textContent = 'Pick a student from the matches.';
                    return;
                }
                results.replaceChildren(new Option('Searching...', ''));
                timer = setTimeout(async () => {
                    try {
                        const response = await fetch('<?php echo e(route('hostels.students.search')); ?>?search=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) { throw new Error('Search failed'); }
                        const students = await response.json();
                        results.replaceChildren(new Option(students.length ? 'Select student' : 'No matching students', ''));
                        students.forEach((student) => results.add(new Option(student.label, student.id)));
                        hint.textContent = students.length ? students.length + ' match' + (students.length === 1 ? '' : 'es') + ' found.' : 'Try a different name or ID.';
                    } catch (error) {
                        results.replaceChildren(new Option('Unable to load students', ''));
                        hint.textContent = 'Search failed. Please try again.';
                    }
                }, 250);
            });
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/manage.blade.php ENDPATH**/ ?>