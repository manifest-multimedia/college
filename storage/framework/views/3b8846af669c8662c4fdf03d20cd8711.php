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
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch(app()->environment()):
        case ('staging'): ?>
            
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('online-examination', ['studentId' => $student_id,'examPassword' => $examPassword,'student_id' => $student_id]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-180687088-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            <?php break; ?>
        <?php default: ?>
            
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('online-examination-v2', ['studentId' => $student_id,'examPassword' => $examPassword,'student_id' => $student_id]);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-180687088-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5)): ?>
<?php $attributes = $__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5; ?>
<?php unset($__attributesOriginal71f4274eee9e9d5807c36ae91aaedce5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal71f4274eee9e9d5807c36ae91aaedce5)): ?>
<?php $component = $__componentOriginal71f4274eee9e9d5807c36ae91aaedce5; ?>
<?php unset($__componentOriginal71f4274eee9e9d5807c36ae91aaedce5); ?>
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/frontend/exam.blade.php ENDPATH**/ ?>