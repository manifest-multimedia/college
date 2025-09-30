@php
    $theme = config('branding.auth_theme', 'default');
    $themeSettings = config('branding.theme_settings', []);
    $cardStyle = $themeSettings['card_style'] ?? 'elevated';
    
    $cardClasses = match($cardStyle) {
        'flat' => 'bg-white',
        'bordered' => 'bg-white border border-gray-200',
        default => 'bg-white shadow-md'
    };
@endphp

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 auth-background-{{ $theme }}">
    <!-- Institution Name -->
    @if(config('branding.theme_settings.show_institution_name', true))
        <div class="mb-4 text-center">
            <h1 class="text-2xl font-bold text-gray-800 institution-name">
                {{ config('branding.institution.name', config('app.name')) }}
            </h1>
            @if(config('branding.institution.short_name'))
                <p class="text-sm text-gray-600 mt-1">
                    {{ config('branding.institution.short_name') }}
                </p>
            @endif
        </div>
    @endif

    <!-- Logo -->
    <div class="logo-container">
        {{ $logo }}
    </div>

    <!-- Auth Card -->
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 {{ $cardClasses }} overflow-hidden sm:rounded-lg auth-card-{{ $theme }}">
        {{ $slot }}
    </div>

    <!-- Footer -->
    @if(config('branding.institution.website_url') || config('branding.institution.support_email'))
        <div class="mt-8 text-center">
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                @if(config('branding.institution.website_url'))
                    <a href="{{ config('branding.institution.website_url') }}" 
                       target="_blank" 
                       class="hover:text-gray-900 transition-colors">
                        Website
                    </a>
                @endif
                @if(config('branding.institution.support_email'))
                    <a href="mailto:{{ config('branding.institution.support_email') }}" 
                       class="hover:text-gray-900 transition-colors">
                        Support
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
