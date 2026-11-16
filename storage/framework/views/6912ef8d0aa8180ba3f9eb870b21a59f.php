<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'examSession',
    'hours',
    'minutes',
    'seconds'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'examSession',
    'hours',
    'minutes',
    'seconds'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div <?php echo e($attributes->merge(['class' => 'exam-timer-container'])); ?>>
    <div class="card shadow-sm">
        <div class="card-header bg-primary">
            <div class="card-title">
                <h5 class="text-white mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Exam Timer
                </h5>
            </div>
        </div>
        
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-12">
                    <div class="timer-display p-3 text-center">
                        <h1 id="exam-timer" class="display-4 fw-bold mb-0">
                            <?php echo e(sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds)); ?>

                        </h1>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examSession->has_extra_time): ?>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <span class="badge bg-success py-2 px-3">
                                    <i class="ki-duotone ki-plus-square fs-5 me-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Extra time added: <?php echo e($examSession->extra_time_minutes); ?> minutes
                                </span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-6">
                            <div class="timer-info">
                                <span class="text-muted fs-7 d-block">Started:</span>
                                <span class="fs-6 fw-semibold"><?php echo e($examSession->started_at->format('h:i A')); ?></span>
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="timer-info">
                                <span class="text-muted fs-7 d-block">Ends:</span>
                                <span class="fs-6 fw-semibold"><?php echo e($examSession->adjustedCompletionTime->format('h:i A')); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/components/exam-timer.blade.php ENDPATH**/ ?>