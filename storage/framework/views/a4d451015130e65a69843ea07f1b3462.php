<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Canteen meal windows']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Canteen meal windows']); ?>
    <div class="container-fluid py-5">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-6">
            <div><h1 class="mb-2">Canteen meal windows</h1><p class="text-muted mb-0">Set when students can redeem Breakfast, Lunch and Supper.</p></div>
            <span class="badge badge-light-primary p-3"><?php echo e($prefix); ?></span>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('canteen_meal_status')): ?><div class="alert alert-success" role="status"><?php echo e(session('canteen_meal_status')); ?></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?><div class="alert alert-danger" role="alert"><strong>Settings were not saved.</strong><ul class="mb-0 mt-2"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></ul></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <p><a href="<?php echo e(route('canteen.meals.activity')); ?>" class="btn btn-light-primary">View meal attendance</a></p>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todayPrepared): ?><div class="alert alert-warning">Today's serving plan is already prepared. Changes to times or serving lanes apply to the next prepared day. Prepared offline scanners keep today's saved schedule.</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="card"><div class="card-header"><h2 class="card-title">Daily serving schedule</h2></div><div class="card-body">
            <p class="text-muted">Before enabling meal service, update all serving scanners and assign every class below. A qualified student can redeem each meal once per day. Changing the schedule does not reset meals already redeemed.</p>
            <form method="POST" action="<?php echo e(route('canteen.meals.update')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="version" value="<?php echo e(old('version', $settings['version'] ?? '')); ?>">
                <div class="row g-5 mb-6">
                    <div class="col-md-6"><label for="timezone" class="form-label">Institution timezone</label><input id="timezone" name="timezone" class="form-control" value="<?php echo e(old('timezone', $settings['timezone'])); ?>" required><div class="form-text">Use Africa/Accra for Ghana. All opening times, cutoffs and meal dates use this timezone.</div></div>
                    <div class="col-md-6 d-flex align-items-center"><input type="hidden" name="enabled" value="0"><label class="form-check"><input type="checkbox" class="form-check-input" name="enabled" value="1" <?php if(old('enabled', $settings['enabled'])): echo 'checked'; endif; ?>><span class="form-check-label">Enable meal redemption during these windows</span></label></div>
                </div>
                <div class="row g-5 mb-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $windows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $window): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-lg-4"><div class="border rounded p-5 h-100">
                        <h3 class="mb-5"><?php echo e(ucfirst($window['meal'])); ?></h3>
                        <input type="hidden" name="windows[<?php echo e($i); ?>][meal]" value="<?php echo e($window['meal']); ?>">
                        <div class="mb-4"><label for="meal-<?php echo e($i); ?>-opens" class="form-label">Opens at</label><input type="time" id="meal-<?php echo e($i); ?>-opens" class="form-control" name="windows[<?php echo e($i); ?>][opens]" value="<?php echo e(old('windows.'.$i.'.opens', $window['opens'])); ?>" required></div>
                        <div><label for="meal-<?php echo e($i); ?>-closes" class="form-label">Closes at</label><input type="time" id="meal-<?php echo e($i); ?>-closes" class="form-control" name="windows[<?php echo e($i); ?>][closes]" value="<?php echo e(old('windows.'.$i.'.closes', $window['closes'])); ?>" required></div>
                    </div></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="alert alert-primary">Opening time is included; closing time is the cutoff. For a 7:00–7:45 AM window, redemption is allowed from 7:00 AM until just before 7:45 AM. Windows cannot overlap or continue past midnight.</div>
                <button type="submit" class="btn btn-primary">Save meal windows</button>
            </form>
        </div></div>
        <div class="card mt-8"><div class="card-header"><h2 class="card-title">Serving devices and class lanes</h2></div><div class="card-body">
            <p>Sign in on each scanner and register it from the app's workspace. Match its scanner code below, name the lane, and assign every class to one device. Staff can change shifts on that device without resetting meal records.</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($devices->isEmpty()): ?><div class="alert alert-primary">No serving devices registered yet. Install the meal-redemption app and register each scanner first.</div>
            <?php else: ?>
            <form method="POST" action="<?php echo e(route('canteen.meals.assignments')); ?>"><?php echo csrf_field(); ?>
                <div class="row g-4 mb-6"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="col-md-6"><label class="form-label" for="label-<?php echo e($device->device_id); ?>">Scanner <?php echo e(strtoupper(substr($device->device_id, -6))); ?></label><input class="form-control" id="label-<?php echo e($device->device_id); ?>" name="device_labels[<?php echo e($device->device_id); ?>]" value="<?php echo e(old('device_labels.'.$device->device_id, $device->label)); ?>" maxlength="80" required></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                <div class="table-responsive"><table class="table"><thead><tr><th>Class</th><th>Students</th><th>Serving scanner</th></tr></thead><tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><tr><td><?php echo e($group['label']); ?></td><td><?php echo e($group['count']); ?></td><td><label class="visually-hidden" for="group-<?php echo e($loop->index); ?>">Scanner for <?php echo e($group['label']); ?></label><select class="form-select" id="group-<?php echo e($loop->index); ?>" name="assignments[<?php echo e($group['key']); ?>]" required><option value="">Choose scanner</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($device->device_id); ?>" <?php if(old('assignments.'.$group['key'], $assignments->get($group['key'])) === $device->device_id): echo 'selected'; endif; ?>><?php echo e($device->label); ?> · <?php echo e(strtoupper(substr($device->device_id, -6))); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></td></tr><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody></table></div><button class="btn btn-primary" type="submit">Save serving lanes</button>
            </form>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/canteen/meal-settings.blade.php ENDPATH**/ ?>