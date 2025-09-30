@php
    $theme = config('branding.auth_theme', 'default');
    $themeView = "custom-auth.themes.{$theme}.login";
    
    // Check if theme-specific view exists, fallback to component
    if (!view()->exists($themeView)) {
        $useComponent = true;
    } else {
        $useComponent = false;
    }
@endphp

@if($useComponent)
    @include('custom-auth.login')
@else
    @include($themeView)
@endif
