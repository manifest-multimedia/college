<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => 'Counter',
    'value' => '0',
    'icon' => 'fas fa-chart-bar',
    'color' => 'primary' // primary, success, warning, danger, info
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
    'title' => 'Counter',
    'value' => '0',
    'icon' => 'fas fa-chart-bar',
    'color' => 'primary' // primary, success, warning, danger, info
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card shadow-sm h-100">
    <div class="card-body d-flex align-items-center" style="min-height: 100px;">
        <div class="d-flex align-items-center w-100">
            <div class="symbol symbol-50px me-5">
                <span class="symbol-label bg-light-<?php echo e($color); ?>">
                    <i class="<?php echo e($icon); ?> fs-2x text-<?php echo e($color); ?>"></i>
                </span>
            </div>
            <div class="d-flex flex-column">
                <h4 class="card-title mb-1 fs-6" style="font-weight:500"><?php echo e($title); ?></h4>
                <span class="fs-2 fw-bold text-gray-900"><?php echo e($value); ?></span>
            </div>
        </div>
    </div>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/components/dashboard/counter.blade.php ENDPATH**/ ?>