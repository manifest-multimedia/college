<?php
    use App\Models\User;
    use App\Models\Student;
    use App\Models\CollegeClass;
    use Spatie\Permission\Models\Role;

    $totalUsers = User::count();
    
    $studentRole = Role::where('name', 'Student')->first();
    $parentRole = Role::where('name', 'Parent')->first();
    
    $excludeRoleIds = [];
    if ($studentRole) {
        $excludeRoleIds[] = $studentRole->id;
    }
    if ($parentRole) {
        $excludeRoleIds[] = $parentRole->id;
    }
    
    if (!empty($excludeRoleIds)) {
        $activeStaff = User::whereHas('roles', function ($query) use ($excludeRoleIds) {
            $query->whereNotIn('id', $excludeRoleIds);
        })->count();
    } else {
        $activeStaff = User::count();
    }
    
    $totalStudents = Student::count();
    $activeStudents = Student::active()->count();
    $activePrograms = CollegeClass::count();
?>

<div class="col-xl-3 col-md-6">
    <?php if (isset($component)) { $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.counter','data' => ['title' => 'Total Users','value' => $totalUsers,'icon' => 'fas fa-users','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Users','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalUsers),'icon' => 'fas fa-users','color' => 'primary']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $attributes = $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $component = $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
</div>

<div class="col-xl-3 col-md-6">
    <?php if (isset($component)) { $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.counter','data' => ['title' => 'Active Staff','value' => $activeStaff,'icon' => 'fas fa-user-tie','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Staff','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeStaff),'icon' => 'fas fa-user-tie','color' => 'success']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $attributes = $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $component = $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
</div>

<div class="col-xl-3 col-md-6">
    <?php if (isset($component)) { $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.counter','data' => ['title' => 'Total Students','value' => $totalStudents,'icon' => 'fas fa-user-graduate','color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Total Students','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totalStudents),'icon' => 'fas fa-user-graduate','color' => 'warning']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $attributes = $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $component = $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
</div>

<div class="col-xl-3 col-md-6">
    <?php if (isset($component)) { $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.counter','data' => ['title' => 'Active Students','value' => $activeStudents,'icon' => 'fas fa-user-check','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Students','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeStudents),'icon' => 'fas fa-user-check','color' => 'success']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $attributes = $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $component = $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
</div>

<div class="col-xl-3 col-md-6">
    <?php if (isset($component)) { $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.counter','data' => ['title' => 'Active Programs','value' => $activePrograms,'icon' => 'fas fa-graduation-cap','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.counter'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Active Programs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activePrograms),'icon' => 'fas fa-graduation-cap','color' => 'info']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $attributes = $__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__attributesOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8)): ?>
<?php $component = $__componentOriginal2eab7672ea63a6b852ec82304a45bfa8; ?>
<?php unset($__componentOriginal2eab7672ea63a6b852ec82304a45bfa8); ?>
<?php endif; ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/dashboard/counters.blade.php ENDPATH**/ ?>