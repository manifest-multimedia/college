<?php
    $theme = config('branding.auth_theme', 'default');
    $themeSettings = config('branding.theme_settings', []);
    $cardStyle = $themeSettings['card_style'] ?? 'elevated';
    
    $cardClasses = match($cardStyle) {
        'flat' => 'bg-white',
        'bordered' => 'bg-white border border-gray-200',
        default => 'bg-white shadow-md'
    };
?>

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 auth-background-<?php echo e($theme); ?>">
    <!-- Institution Name -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.theme_settings.show_institution_name', true)): ?>
        <div class="mb-4 text-center">
            <h1 class="text-2xl font-bold text-gray-800 institution-name">
                <?php echo e(config('branding.institution.name', config('app.name'))); ?>

            </h1>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.short_name')): ?>
                <p class="text-sm text-gray-600 mt-1">
                    <?php echo e(config('branding.institution.short_name')); ?>

                </p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Logo -->
    <div class="logo-container">
        <?php echo e($logo); ?>

    </div>

    <!-- Auth Card -->
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 <?php echo e($cardClasses); ?> overflow-hidden sm:rounded-lg auth-card-<?php echo e($theme); ?>">
        <?php echo e($slot); ?>

    </div>

    <!-- Footer -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.website_url') || config('branding.institution.support_email')): ?>
        <div class="mt-8 text-center">
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.website_url')): ?>
                    <a href="<?php echo e(config('branding.institution.website_url')); ?>" 
                       target="_blank" 
                       class="hover:text-gray-900 transition-colors">
                        Website
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(config('branding.institution.support_email')): ?>
                    <a href="mailto:<?php echo e(config('branding.institution.support_email')); ?>" 
                       class="hover:text-gray-900 transition-colors">
                        Support
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/authentication-card.blade.php ENDPATH**/ ?>