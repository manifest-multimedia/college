<?php if (isset($component)) { $__componentOriginal71f4274eee9e9d5807c36ae91aaedce5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.frontend.exams','data' => ['title' => 'Online Examination']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('frontend.exams'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Online Examination']); ?>
   
   <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('exam-login');

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1528837298-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5)): ?>
<?php $attributes = $__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5; ?>
<?php unset($__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71f4274eee9e9d5807c36ae91aaedce5)): ?>
<?php $component = $__componentOriginal71f4274eee9e9d5807c36ae91aaedce5; ?>
<?php unset($__componentOriginal71f4274eee9e9d5807c36ae91aaedce5); ?>
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/frontend/take-exam.blade.php ENDPATH**/ ?>