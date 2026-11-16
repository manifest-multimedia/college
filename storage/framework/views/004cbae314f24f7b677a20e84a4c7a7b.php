<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <figure class="mb-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($media['type'] === 'image'): ?>
            <img src="<?php echo e($media['url']); ?>" alt="<?php echo e($media['filename'] ?? 'Question image'); ?>"
                 class="img-fluid rounded" style="max-height: 400px; object-fit: contain;" loading="lazy">
        <?php elseif($media['type'] === 'video'): ?>
            <video controls preload="none" playsinline class="w-100 rounded" style="max-height: 400px;"
                   aria-label="<?php echo e($media['filename'] ?? 'Question video'); ?>">
                <source src="<?php echo e($media['url']); ?>" type="<?php echo e($media['mime_type']); ?>">
                Your browser cannot play this video.
            </video>
        <?php elseif($media['type'] === 'audio'): ?>
            <audio controls preload="none" class="w-100" aria-label="<?php echo e($media['filename'] ?? 'Question audio'); ?>">
                <source src="<?php echo e($media['url']); ?>" type="<?php echo e($media['mime_type']); ?>">
                Your browser cannot play this audio.
            </audio>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <figcaption class="small text-muted"><?php echo e($media['filename'] ?? ''); ?></figcaption>
    </figure>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/question-media.blade.php ENDPATH**/ ?>