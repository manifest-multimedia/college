<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['label', 'value', 'icon' => 'fa-circle', 'color' => 'primary']));

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

foreach (array_filter((['label', 'value', 'icon' => 'fa-circle', 'color' => 'primary']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="card h-100">
    <div class="card-body d-flex align-items-center gap-4">
        <span class="d-inline-flex align-items-center justify-content-center rounded bg-light-<?php echo e($color); ?> text-<?php echo e($color); ?>" style="width:48px;height:48px;"><i class="fas <?php echo e($icon); ?> fs-3"></i></span>
        <div>
            <div class="fs-2 fw-bold text-gray-900 lh-1"><?php echo e($value); ?></div>
            <div class="text-muted fs-7 mt-1"><?php echo e($label); ?></div>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/hostels/stat.blade.php ENDPATH**/ ?>