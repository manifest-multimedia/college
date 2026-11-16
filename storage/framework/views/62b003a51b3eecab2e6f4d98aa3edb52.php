<?php $__env->startSection('title', 'Exam Sessions'); ?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <h3 class="card-title">
                <i class="bi bi-calendar-check me-2"></i>Exam Sessions
            </h3>
        </div>
    </div>
    
    <div class="card-body">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('exam-sessions', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2804681241-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/exams/sessions.blade.php ENDPATH**/ ?>