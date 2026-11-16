<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('branding.institution.name', config('app.name'))); ?></title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="<?php echo e(asset(config('branding.logo.favicon', '/favicon.ico'))); ?>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Brand Colors as CSS Variables -->
        <style>
            :root {
                <?php $__currentLoopData = config('branding.colors', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $name => $color): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                --brand-<?php echo e($name); ?>: <?php echo e($color); ?>;
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            }
        </style>

        <!-- Theme-specific CSS -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(file_exists(public_path("css/themes/" . config('branding.auth_theme', 'default') . "/auth.css"))): ?>
            <link rel="stylesheet" href="<?php echo e(asset('css/themes/' . config('branding.auth_theme', 'default') . '/auth.css')); ?>">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <!-- Styles -->
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="theme-<?php echo e(config('branding.auth_theme', 'default')); ?>">
        <div class="font-sans text-gray-900 antialiased">
            <?php echo e($slot); ?>

        </div>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/layouts/guest.blade.php ENDPATH**/ ?>