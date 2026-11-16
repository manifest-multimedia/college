<?php
    $statLabels = [
        'hostels' => ['Hostels', 'fa-building', 'primary'],
        'rooms' => ['Rooms', 'fa-door-open', 'info'],
        'beds' => ['Beds', 'fa-bed', 'success'],
        'active_allocations' => ['Active allocations', 'fa-user-check', 'warning'],
        'checked_in' => ['Checked in', 'fa-right-to-bracket', 'danger'],
    ];
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Hostel Operations & Reporting']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Hostel Operations & Reporting']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'Operations & Reporting','subtitle' => 'Monitor occupancy, export allocation reports and manage hostel wardens.','icon' => 'fa-chart-column']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Operations & Reporting','subtitle' => 'Monitor occupancy, export allocation reports and manage hostel wardens.','icon' => 'fa-chart-column']); ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $totals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php [$label, $icon, $color] = $statLabels[$key] ?? [ucwords(str_replace('_', ' ', $key)), 'fa-circle', 'primary']; ?>
                <div class="col-6 col-lg"><?php if (isset($component)) { $__componentOriginal8fe4a06fe3ac293b1e4f5f7743624a65 = $component; } ?>
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

        <div class="row g-6 mb-6">
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Occupancy by hostel</h3>
                        <span class="text-muted fs-7 fw-normal">Beds with an active allocation (allocated or checked in).</span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Hostel</th><th class="text-center">Total beds</th><th class="text-center">Allocated</th><th class="text-center">Free</th><th style="min-width:170px;">Occupancy</th></tr></thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $occupancy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php $percent = $row->beds > 0 ? round($row->allocated / $row->beds * 100) : 0; ?>
                                        <tr>
                                            <td class="fw-bold text-gray-800"><?php echo e($row->name); ?></td>
                                            <td class="text-center"><?php echo e($row->beds); ?></td>
                                            <td class="text-center"><?php echo e($row->allocated); ?></td>
                                            <td class="text-center"><?php echo e($row->beds - $row->allocated); ?></td>
                                            <td>
                                                <div class="d-flex justify-content-between fs-8 text-muted mb-1"><span><?php echo e($row->allocated); ?> / <?php echo e($row->beds); ?></span><span><?php echo e($percent); ?>%</span></div>
                                                <div class="progress" style="height:6px;"><div class="progress-bar bg-<?php echo e($percent >= 90 ? 'danger' : ($percent >= 70 ? 'warning' : 'success')); ?>" style="width: <?php echo e($percent); ?>%"></div></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="5" class="text-center text-muted py-10"><i class="fas fa-building fs-2 d-block mb-3"></i>No hostels yet.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="fw-bold mb-1"><i class="fas fa-file-csv me-2 text-primary"></i>Export allocations</h3>
                        <p class="text-muted fs-7 mb-4">Download a CSV of allocations. Leave the filters empty to export everything.</p>
                        <form method="GET" action="<?php echo e(route('hostels.reports.export')); ?>">
                            <label class="form-label fs-7 fw-bold" for="export-period">Period</label>
                            <select name="period_id" id="export-period" class="form-select mb-3">
                                <option value="">All periods</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($period->id); ?>"><?php echo e($period->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <label class="form-label fs-7 fw-bold" for="export-status">Status</label>
                            <select name="status" id="export-status" class="form-select mb-4">
                                <option value="">All statuses</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['allocated', 'checked_in', 'checked_out', 'released', 'transferred']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($status); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $status))); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <button class="btn btn-primary w-100"><i class="fas fa-download me-1"></i> Export CSV</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-6">
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="fw-bold mb-1"><i class="fas fa-user-shield me-2 text-primary"></i>Assign hostel warden</h3>
                        <p class="text-muted fs-7 mb-4">Wardens only see and update allocations in the hostels assigned to them.</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hostels->isEmpty()): ?>
                            <div class="text-muted fs-7 border border-dashed rounded p-4">Create a hostel first, then you can assign a warden to it.</div>
                        <?php elseif($staff->isEmpty()): ?>
                            <div class="text-muted fs-7 border border-dashed rounded p-4">No eligible staff accounts were found. Give a user a role such as Hostel Warden or Hostel Officer first.</div>
                        <?php else: ?>
                            <form method="POST" action="<?php echo e(route('hostels.wardens.assign')); ?>">
                                <?php echo csrf_field(); ?>
                                <label class="form-label fs-7 fw-bold" for="warden-hostel">Hostel</label>
                                <select name="hostel_id" id="warden-hostel" class="form-select mb-3" required>
                                    <option value="">Select hostel</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $hostels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hostel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($hostel->id); ?>" <?php if(old('hostel_id') == $hostel->id): echo 'selected'; endif; ?>><?php echo e($hostel->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <label class="form-label fs-7 fw-bold" for="warden-user">Staff member</label>
                                <select name="user_id" id="warden-user" class="form-select mb-4" required>
                                    <option value="">Select staff member</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($member->id); ?>" <?php if(old('user_id') == $member->id): echo 'selected'; endif; ?>><?php echo e($member->name); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($member->email): ?> (<?php echo e($member->email); ?>)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <button class="btn btn-primary w-100">Assign warden</button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header py-5"><div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Current wardens</h3>
                        <span class="text-muted fs-7 fw-normal"><?php echo e($wardens->count()); ?> active <?php echo e(\Illuminate\Support\Str::plural('assignment', $wardens->count())); ?></span>
                    </div></div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4 mb-0">
                                <thead><tr class="text-muted fw-bold fs-7 text-uppercase"><th>Hostel</th><th>Warden</th><th>Assigned</th><th class="text-end">Action</th></tr></thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $wardens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warden): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr>
                                            <td class="fw-bold text-gray-800"><?php echo e($warden->hostel?->name ?? 'Hostel unavailable'); ?></td>
                                            <td>
                                                <div class="text-gray-800"><?php echo e($warden->user?->name ?? 'User #'.$warden->user_id); ?></div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($warden->user?->email): ?><div class="text-muted fs-7"><?php echo e($warden->user->email); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td><?php echo e($warden->created_at?->format('d M Y') ?? '—'); ?></td>
                                            <td class="text-end">
                                                <form method="POST" action="<?php echo e(route('hostels.wardens.revoke', $warden)); ?>" onsubmit="return confirm('Revoke warden access for <?php echo e(addslashes($warden->user?->name ?? 'this user')); ?>?')">
                                                    <?php echo csrf_field(); ?>
                                                    <button class="btn btn-sm btn-light-danger"><i class="fas fa-user-slash me-1"></i> Revoke</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr><td colspan="4" class="text-center text-muted py-10"><i class="fas fa-user-shield fs-2 d-block mb-3"></i>No wardens assigned yet.</td></tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/operations.blade.php ENDPATH**/ ?>