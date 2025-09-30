@php
    $authLogo = config('branding.logo.auth') ?? config('branding.logo.primary');
    $primaryColor = config('branding.colors.primary', '#3B82F6');
@endphp

<a href="/" class="flex items-center justify-center">
    @if($authLogo && $authLogo !== '/images/logos/default-logo.svg')
        <!-- Custom Logo -->
        <img src="{{ asset($authLogo) }}" 
             alt="{{ config('branding.institution.name', config('app.name')) }} Logo" 
             class="w-16 h-16 object-contain">
    @else
        <!-- Default SVG Logo with Brand Colors -->
        <svg class="w-16 h-16" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.395 44.428C4.557 40.198 0 32.632 0 24 0 10.745 10.745 0 24 0a23.891 23.891 0 0113.997 4.502c-.2 17.907-11.097 33.245-26.602 39.926z" 
                  fill="{{ $primaryColor }}"/>
            <path d="M14.134 45.885A23.914 23.914 0 0024 48c13.255 0 24-10.745 24-24 0-3.516-.756-6.856-2.115-9.866-4.659 15.143-16.608 27.092-31.75 31.751z" 
                  fill="{{ $primaryColor }}"/>
        </svg>
    @endif
</a>
