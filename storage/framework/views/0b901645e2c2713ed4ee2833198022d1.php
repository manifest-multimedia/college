<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo e(config('app.name', 'Laravel')); ?> - <?php echo e($title ? $title:"Undefined"); ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap">
    <link rel="stylesheet" href="<?php echo e(asset('backend/css/bootstrap/bootstrap.min.css')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('images/college-logo.png')); ?>">
</head>

<body>
    <style>
        footer {
            position: relative;
            margin-bottom: -50px;
            /* adjust this value to match the footer height */
        }
    </style>
    <?php if (isset($component)) { $__componentOriginal21b90aee62eb729a70bc5150038e88d2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal21b90aee62eb729a70bc5150038e88d2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.partials.header','data' => ['pageTitle' => 'Dashboard','pageDescription' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('partials.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => 'Dashboard','pageDescription' => 'Dashboard']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal21b90aee62eb729a70bc5150038e88d2)): ?>
<?php $attributes = $__attributesOriginal21b90aee62eb729a70bc5150038e88d2; ?>
<?php unset($__attributesOriginal21b90aee62eb729a70bc5150038e88d2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal21b90aee62eb729a70bc5150038e88d2)): ?>
<?php $component = $__componentOriginal21b90aee62eb729a70bc5150038e88d2; ?>
<?php unset($__componentOriginal21b90aee62eb729a70bc5150038e88d2); ?>
<?php endif; ?>
    <?php echo e($slot); ?>

    <?php if (isset($component)) { $__componentOriginal3bcc21be352e90c327647659b100c2a9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3bcc21be352e90c327647659b100c2a9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.partials.footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('partials.footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3bcc21be352e90c327647659b100c2a9)): ?>
<?php $attributes = $__attributesOriginal3bcc21be352e90c327647659b100c2a9; ?>
<?php unset($__attributesOriginal3bcc21be352e90c327647659b100c2a9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3bcc21be352e90c327647659b100c2a9)): ?>
<?php $component = $__componentOriginal3bcc21be352e90c327647659b100c2a9; ?>
<?php unset($__componentOriginal3bcc21be352e90c327647659b100c2a9); ?>
<?php endif; ?>

    <script src="<?php echo e(asset('backend/js/bootstrap/bootstrap.bundle.min.js')); ?>"></script>
    <script src="<?php echo e(asset('backend/js/main.js')); ?>"></script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="<?php echo e(asset('backend/js/charts-demo.js')); ?>"></script>
</body>

</html>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/backend/layout.blade.php ENDPATH**/ ?>