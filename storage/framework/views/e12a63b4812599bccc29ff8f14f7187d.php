<?php
    $theme = config('branding.auth_theme', 'default');
    $themeView = "custom-auth.themes.{$theme}.login";
    
    // Check if theme-specific login view exists, fallback to default
    if (!view()->exists($themeView)) {
        $themeView = 'custom-auth.themes.default.login';
    }
?>

<?php echo $__env->make($themeView, array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/custom-auth/login.blade.php ENDPATH**/ ?>