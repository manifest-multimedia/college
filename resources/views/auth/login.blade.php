@php
    $theme = config('branding.auth_theme', 'default');
    $themeView = "auth.themes.{$theme}.login";
    
    // Check if theme-specific login view exists, fallback to default
    if (!view()->exists($themeView)) {
        $themeView = 'auth.themes.default.login';
    }
@endphp

@include($themeView)
