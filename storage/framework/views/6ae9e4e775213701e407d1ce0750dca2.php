<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Exam Devices']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Exam Devices']); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">Please correct the highlighted registration details.</div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="card mb-5">
        <div class="card-header"><h3 class="card-title">Register this device</h3></div>
        <div class="card-body">
            <p class="text-muted">Open this page on the physical device that will be used for examinations. Registration creates a secure, HTTP-only device credential in this browser.</p>
            <form method="POST" action="<?php echo e(route('admin.exam-devices.store')); ?>" class="row g-3"><?php echo csrf_field(); ?>
                <div class="col-md-4"><label class="form-label">Device label</label><input class="form-control <?php $__errorArgs = ['label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" name="label" value="<?php echo e(old('label')); ?>" required placeholder="e.g. ICT Lab PC 12"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div class="col-md-3"><label class="form-label">Device type</label><select class="form-select" name="device_type" required><option value="desktop">Desktop</option><option value="laptop">Laptop</option><option value="mobile">Mobile</option><option value="tablet">Tablet</option></select></div>
                <div class="col-md-3"><label class="form-label">Location (optional)</label><input class="form-control" name="location" placeholder="e.g. ICT Lab"></div>
                <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary w-100">Register device</button></div>
            </form>
        </div>
    </div>
    <div class="card"><div class="card-header"><h3 class="card-title">Registered devices</h3></div><div class="card-body table-responsive">
        <table class="table table-row-bordered"><thead><tr><th>Device</th><th>Type</th><th>Location</th><th>Status</th><th>Registered by</th><th>Last seen</th><th></th></tr></thead><tbody>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><tr><td><?php echo e($device->label); ?></td><td class="text-capitalize"><?php echo e($device->device_type); ?></td><td><?php echo e($device->location ?: '—'); ?></td><td><span class="badge badge-light-<?php echo e($device->status === 'active' ? 'success' : 'warning'); ?>"><?php echo e($device->status); ?></span></td><td><?php echo e($device->registeredBy->name); ?></td><td><?php echo e($device->last_seen_at?->diffForHumans() ?: 'Never'); ?></td><td class="text-end"><form class="d-inline" method="POST" action="<?php echo e(route($device->status === 'active' ? 'admin.exam-devices.suspend' : 'admin.exam-devices.activate', $device)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-light"><?php echo e($device->status === 'active' ? 'Suspend' : 'Activate'); ?></button></form><form class="d-inline" method="POST" action="<?php echo e(route('admin.exam-devices.revoke', $device)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-light-danger">Revoke</button></form></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="7" class="text-center">No devices registered.</td></tr><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody></table><?php echo e($devices->links()); ?>

    </div></div>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/admin/exam-devices/index.blade.php ENDPATH**/ ?>