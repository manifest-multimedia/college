<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Canteen qualification lists']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Canteen qualification lists']); ?>
    <div class="container-fluid py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-6">
            <div><h1 class="mb-2">Canteen qualification lists</h1><p class="text-muted mb-0">Upload reviewed class lists and manage their clearance status.</p></div>
            <span class="badge badge-light-primary p-3"><?php echo e($prefix); ?></span>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('canteen_status')): ?><div class="alert alert-success" role="status"><?php echo e(session('canteen_status')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><div class="alert alert-danger" role="alert"><strong>The import needs attention.</strong><ul class="mb-0 mt-2"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></ul></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="alert alert-primary mb-6">Active clearance period: <strong><?php echo e($period['academic_year'] ?? 'Not configured'); ?> · Semester <?php echo e($period['semester'] ?? 'not configured'); ?></strong>. These lists can be used without Finance. They record qualification, not payments.</div>
        <div class="card mb-8"><div class="card-header"><h2 class="card-title">Upload and preview</h2></div><div class="card-body">
            <form method="POST" action="<?php echo e(route('canteen.lists.upload')); ?>" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="row g-5 mb-5">
                    <div class="col-md-6"><label for="academic_year" class="form-label">Clearance academic year</label><input class="form-control" id="academic_year" name="academic_year" value="<?php echo e(old('academic_year', $period['academic_year'] ?? '')); ?>" placeholder="2026/2027" maxlength="32" required><div class="form-text">Use the confirmed clearance period, not the intake year printed in student indexes.</div></div>
                    <div class="col-md-3"><label for="semester" class="form-label">Semester</label><input class="form-control" type="number" id="semester" name="semester" min="1" max="99" value="<?php echo e(old('semester', $period['semester'] ?? 1)); ?>" required></div>
                    <div class="col-12"><label for="files" class="form-label">Class workbooks</label><input class="form-control" type="file" id="files" name="files[]" accept=".xlsx,.xlsm" multiple required aria-describedby="file-help"><div id="file-help" class="form-text">Up to 8 Excel files, 1 MB each and 5 MB combined. One complete list per class. Macros are not run.</div></div>
                    <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" name="activate" value="1" <?php if(old('activate')): echo 'checked'; endif; ?>><span class="form-check-label">Make this the active canteen period when I confirm the import</span></label><p class="form-text mt-2">Leave this unchecked to prepare lists for a future period. The preview will show any proposed period change.</p></div>
                </div>
                <details class="mb-5"><summary class="fw-semibold">Required workbook layout and replacement rules</summary><p class="mt-3">Class label in C1; student index followed by name in column C from row 2; QUALIFIED or NOT QUALIFIED in column D. Keep the same class label for corrections.</p><p class="mb-0">A corrected file replaces that class's complete list for the selected period. Omitted students no longer retain the old list's approval. Unchanged files create no duplicates. Current valid financial records, when present, take priority.</p></details>
                <button type="submit" class="btn btn-primary">Validate and preview</button>
            </form>
        </div></div>
        <div class="card"><div class="card-header"><h2 class="card-title">Import history</h2></div><div class="card-body">
            <p class="text-muted">History is retained when lists are replaced or withdrawn. Source downloads are available for portal uploads.</p>
            <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr class="fw-bold text-muted"><th>ID / class</th><th>Period</th><th>Students</th><th>Source and operator</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $batches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $batch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr><td><strong>#<?php echo e($batch->id); ?> · <?php echo e($batch->class_label); ?></strong></td><td><?php echo e($batch->academic_year); ?><br>Semester <?php echo e($batch->semester); ?></td><td><?php echo e($batch->qualifications_count); ?> total<br><span class="text-success"><?php echo e($batch->qualified_count); ?> qualified</span><br><?php echo e($batch->qualifications_count - $batch->qualified_count); ?> not qualified</td><td><?php echo e($batch->file_name); ?><br><small><?php echo e($batch->importedBy?->name ?? 'Terminal import'); ?> · <?php echo e($batch->imported_at->format('d M Y H:i')); ?> UTC</small><details><summary class="small">Source checksum</summary><code class="text-break"><?php echo e($batch->file_sha256); ?></code></details></td><td><span class="badge <?php echo e($batch->revoked_at ? 'badge-light-secondary' : 'badge-light-success'); ?>"><?php echo e($batch->revoked_at ? 'Withdrawn / replaced' : 'Active list'); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($batch->revoked_at): ?><div class="small mt-2"><?php echo e($batch->revocation_reason); ?><br><?php echo e($batch->revokedBy?->name ?? 'Terminal / earlier import'); ?> · <?php echo e($batch->revoked_at->format('d M Y H:i')); ?> UTC</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td><td><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$batch->revoked_at): ?><a class="btn btn-sm btn-light-danger mb-2" href="<?php echo e(route('canteen.lists.withdrawal', $batch->id)); ?>">Withdraw list</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\Illuminate\Support\Facades\Storage::disk('local')->exists($imports->archivePath($batch->institution_prefix, $batch->file_sha256))): ?><a class="btn btn-sm btn-light-primary mb-2" href="<?php echo e(route('canteen.lists.source', $batch->id)); ?>">Download source</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6">No lists imported for this institution yet.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody></table></div><?php echo e($batches->links()); ?>

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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/canteen/qualification-lists.blade.php ENDPATH**/ ?>