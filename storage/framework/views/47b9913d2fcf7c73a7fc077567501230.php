<?php
    $hasFilters = collect(['search', 'program_id', 'cohort_id', 'academic_year_id', 'gender'])->contains(fn ($key) => filled(request($key)))
        || in_array(request('allocation_status'), ['allocated', 'unallocated'], true);
    $bulkResults = session('bulk_results');
?>

<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Bulk Hostel Allocation']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bulk Hostel Allocation']); ?>
    <div class="container-fluid py-6">

        <?php if (isset($component)) { $__componentOriginal4321498ab9bdbd94c6d83d40c932a39f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4321498ab9bdbd94c6d83d40c932a39f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.hostels.page-header','data' => ['title' => 'Bulk Hostel Allocation','subtitle' => 'Filter students and select across pages before previewing bed assignments.','icon' => 'fa-users']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('hostels.page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Bulk Hostel Allocation','subtitle' => 'Filter students and select across pages before previewing bed assignments.','icon' => 'fa-users']); ?>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bulkResults): ?>
            <?php $skipped = $bulkResults['skipped'] ?? []; ?>
            <div class="alert alert-<?php echo e(count($skipped) ? 'warning' : 'success'); ?> alert-dismissible fade show" role="alert">
                <div class="fw-bold"><i class="fas fa-circle-check me-2"></i><?php echo e($bulkResults['allocated'] ?? 0); ?> <?php echo e(\Illuminate\Support\Str::plural('student', $bulkResults['allocated'] ?? 0)); ?> allocated<?php echo e(count($skipped) ? ', '.count($skipped).' skipped' : ''); ?>.</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($skipped)): ?>
                    <ul class="mb-0 mt-2 ps-4"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $skipped; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($reason); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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

        <form method="GET" action="<?php echo e(route('hostels.bulk.index')); ?>" class="card mb-6">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <label class="form-label fs-7 fw-bold" for="filter-search">Search</label>
                        <input class="form-control" id="filter-search" name="search" value="<?php echo e(request('search')); ?>" placeholder="Name, student ID, email or phone">
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-program">Program</label>
                        <select class="form-select" id="filter-program" name="program_id">
                            <option value="">All programs</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($program->id); ?>" <?php if(request('program_id') == $program->id): echo 'selected'; endif; ?>><?php echo e($program->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-cohort">Cohort</label>
                        <select class="form-select" id="filter-cohort" name="cohort_id">
                            <option value="">All cohorts</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cohorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cohort): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cohort->id); ?>" <?php if(request('cohort_id') == $cohort->id): echo 'selected'; endif; ?>><?php echo e($cohort->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-year">Academic year</label>
                        <select class="form-select" id="filter-year" name="academic_year_id">
                            <option value="">All years</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($year->id); ?>" <?php if(request('academic_year_id') == $year->id): echo 'selected'; endif; ?>><?php echo e($year->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label class="form-label fs-7 fw-bold" for="filter-gender">Gender</label>
                        <select class="form-select" id="filter-gender" name="gender">
                            <option value="">All</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = ['Male', 'Female']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gender): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($gender); ?>" <?php if(request('gender') === $gender): echo 'selected'; endif; ?>><?php echo e($gender); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <label class="fs-7 fw-bold mb-0" for="filter-allocation">Allocation status</label>
                    <select class="form-select form-select-sm w-auto" id="filter-allocation" name="allocation_status">
                        <option value="all">All</option>
                        <option value="unallocated" <?php if(request('allocation_status') === 'unallocated'): echo 'selected'; endif; ?>>Unallocated</option>
                        <option value="allocated" <?php if(request('allocation_status') === 'allocated'): echo 'selected'; endif; ?>>Allocated</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasFilters): ?><a href="<?php echo e(route('hostels.bulk.index')); ?>" class="btn btn-light">Clear filters</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Apply filters</button>
                </div>
            </div>
        </form>

        <form method="POST" action="<?php echo e(route('hostels.bulk.preview')); ?>" id="bulk-form">
            <?php echo csrf_field(); ?>
            <div class="card mb-6">
                <div class="card-body">
                    <h3 class="fw-bold mb-1">Where should they go?</h3>
                    <p class="text-muted fs-7 mb-4">Beds in the chosen hostel are assigned automatically. You will see the exact assignments before anything is saved.</p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($periods->isEmpty()): ?>
                        <div class="alert alert-warning mb-0">There is no open allocation period. Open one in Hostel Management before allocating students.</div>
                    <?php else: ?>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold" for="bulk-hostel">Destination hostel <span class="text-danger">*</span></label>
                                <select name="hostel_id" id="bulk-hostel" class="form-select" required>
                                    <option value="">Select hostel</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $hostels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hostel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($hostel->id); ?>"><?php echo e($hostel->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fs-7 fw-bold" for="bulk-period">Allocation period <span class="text-danger">*</span></label>
                                <select name="period_id" id="bulk-period" class="form-select" required>
                                    <option value="">Select open period</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($period->id); ?>"><?php echo e($period->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header py-5 flex-wrap gap-3">
                    <div class="card-title flex-column align-items-start">
                        <h3 class="fw-bold mb-0">Students</h3>
                        <span class="text-muted fs-7 fw-normal"><?php echo e($students->total()); ?> <?php echo e(\Illuminate\Support\Str::plural('student', $students->total())); ?> match<?php echo e($students->total() === 1 ? 'es' : ''); ?>. Selections are retained across pages and filters (up to 200 students).</span>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-bordered align-middle gy-4 mb-0">
                            <thead>
                                <tr class="text-muted fw-bold fs-7 text-uppercase">
                                    <th style="width:40px;"><div class="form-check form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" id="hostel-select-all" aria-label="Select all students on this page"></div></th>
                                    <th>Student</th><th>Program</th><th>Cohort</th><th>Gender</th><th>Accommodation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <?php $studentName = trim($student->first_name.' '.$student->other_name.' '.$student->last_name); ?>
                                    <tr>
                                        <td><div class="form-check form-check-custom form-check-solid"><input class="form-check-input hostel-student-check" type="checkbox" value="<?php echo e($student->id); ?>" <?php if(in_array($student->id, $selectedIds)): echo 'checked'; endif; ?> aria-label="Select <?php echo e($studentName); ?>"></div></td>
                                        <td>
                                            <div class="fw-bold text-gray-800"><?php echo e($studentName); ?></div>
                                            <div class="text-muted fs-7"><?php echo e($student->student_id); ?></div>
                                        </td>
                                        <td><?php echo e($student->collegeClass?->name ?? '—'); ?></td>
                                        <td><?php echo e($student->cohort?->name ?? '—'); ?></td>
                                        <td><?php echo e($student->gender ?: '—'); ?></td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->has_active_allocation): ?>
                                                <span class="badge badge-light-success">Has a bed</span>
                                            <?php else: ?>
                                                <span class="badge badge-light-secondary">Unallocated</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="6" class="text-center text-muted py-10"><i class="fas fa-user-slash fs-2 d-block mb-3"></i>No students match your filters.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-3 position-sticky bottom-0" style="z-index:5; background: var(--bs-body-bg, #fff);">
                    <span class="fw-bold text-gray-800"><i class="fas fa-square-check me-2 text-primary"></i><span id="hostel-selected-count"><?php echo e(count($selectedIds)); ?> selected</span></span><button class="btn btn-sm btn-light-danger" type="button" id="hostel-clear-selection">Clear selection</button><span id="hostel-selection-error" class="text-danger fs-7" role="alert"></span>
                    <button class="btn btn-primary" type="submit" id="bulk-preview-button" disabled>Preview assignments <i class="fas fa-arrow-right ms-1"></i></button>
                </div>
            </div>
        </form>

        <div class="mt-4"><?php echo e($students->links()); ?></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const all = document.getElementById('hostel-select-all');
            const boxes = Array.from(document.querySelectorAll('.hostel-student-check'));
            const count = document.getElementById('hostel-selected-count');
            const button = document.getElementById('bulk-preview-button');
            const hostel = document.getElementById('bulk-hostel');
            const period = document.getElementById('bulk-period');
            const error = document.getElementById('hostel-selection-error');
            const clear = document.getElementById('hostel-clear-selection');
            const form = document.getElementById('bulk-form');
            const selected = new Set(<?php echo json_encode(array_map('intval', $selectedIds), 512) ?>);
            let busy = false;
            const update = () => {
                boxes.forEach(box => box.checked = selected.has(Number(box.value)));
                const visible = boxes.filter(box => box.checked).length;
                all.checked = boxes.length > 0 && visible === boxes.length;
                all.indeterminate = visible > 0 && visible < boxes.length;
                count.textContent = selected.size + ' selected across pages';
                button.disabled = busy || !selected.size || !hostel?.value || !period?.value;
                clear.disabled = busy || !selected.size;
            };
            const change = async (action, ids) => {
                if (busy) return;
                busy = true; error.textContent = ''; update();
                try {
                    const response = await fetch(<?php echo json_encode(route('hostels.bulk.selection'), 15, 512) ?>, {
                        method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': <?php echo json_encode(csrf_token(), 15, 512) ?>},
                        credentials: 'same-origin', body: JSON.stringify({action, student_ids: ids})
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(Object.values(payload.errors || {}).flat().join(' ') || payload.message || 'Could not save selection.');
                    selected.clear(); payload.selected_ids.forEach(id => selected.add(Number(id)));
                } catch (e) { error.textContent = e.message || 'Selection could not be saved.'; }
                finally { busy = false; update(); }
            };
            all.addEventListener('change', () => change(all.checked ? 'add' : 'remove', boxes.map(box => Number(box.value))));
            boxes.forEach(box => box.addEventListener('change', () => change(box.checked ? 'add' : 'remove', [Number(box.value)])));
            clear.addEventListener('click', () => change('clear', []));
            [hostel, period].forEach(field => field?.addEventListener('change', update));
            form.addEventListener('submit', event => { if (busy || !selected.size) event.preventDefault(); });
            update();
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/hostels/bulk-index.blade.php ENDPATH**/ ?>