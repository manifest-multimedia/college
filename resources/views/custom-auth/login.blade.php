@php
    $theme = config('branding.auth_theme', 'default');
    $themeView = "custom-auth.themes.{$theme}.login";
    
    // Check if theme-specific login view exists, fallback to default
    if (!view()->exists($themeView)) {
        $themeView = 'custom-auth.themes.default.login';
    }
@endphp

@include($themeView)