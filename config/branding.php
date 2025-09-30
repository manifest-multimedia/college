<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Theme
    |--------------------------------------------------------------------------
    |
    | This value determines which authentication theme to use for the login
    | and registration pages. Supported themes: "default", "mhtia", "modern"
    | You can add custom themes by creating corresponding theme directories.
    |
    */

    'auth_theme' => env('AUTH_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | College Logo Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the college logo used throughout the application.
    | You can specify different logos for different contexts.
    |
    */

    'logo' => [
        'primary' => env('COLLEGE_LOGO_PRIMARY', '/images/logos/default-logo.svg'),
        'white' => env('COLLEGE_LOGO_WHITE', '/images/logos/default-logo-white.svg'),
        'favicon' => env('COLLEGE_FAVICON', '/images/logos/favicon.ico'),
        'auth' => env('COLLEGE_LOGO_AUTH', null), // Falls back to primary if null
    ],

    /*
    |--------------------------------------------------------------------------
    | Primary Color Scheme
    |--------------------------------------------------------------------------
    |
    | Configure the primary colors used throughout the dashboard and application.
    | These colors will be injected as CSS variables.
    |
    */

    'colors' => [
        'primary' => env('PRIMARY_COLOR', '#3B82F6'), // Blue
        'secondary' => env('SECONDARY_COLOR', '#64748B'), // Gray
        'accent' => env('ACCENT_COLOR', '#10B981'), // Green
        'success' => env('SUCCESS_COLOR', '#10B981'), // Green
        'warning' => env('WARNING_COLOR', '#F59E0B'), // Yellow
        'danger' => env('DANGER_COLOR', '#EF4444'), // Red
    ],

    /*
    |--------------------------------------------------------------------------
    | Institution Configuration
    |--------------------------------------------------------------------------
    |
    | Configure institution-specific settings like staff mail URL,
    | contact information, and other customizable elements.
    |
    */

    'institution' => [
        'name' => env('INSTITUTION_NAME', config('app.name')),
        'short_name' => env('INSTITUTION_SHORT_NAME', 'College'),
        'staff_mail_url' => env('STAFF_MAIL_URL', 'https://mail.google.com'),
        'student_portal_url' => env('STUDENT_PORTAL_URL', null),
        'website_url' => env('INSTITUTION_WEBSITE_URL', null),
        'support_email' => env('SUPPORT_EMAIL', 'support@college.edu'),
        'phone' => env('INSTITUTION_PHONE', null),
        'address' => env('INSTITUTION_ADDRESS', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | List of available authentication themes. Each theme should have
    | corresponding view files in the auth/themes directory.
    |
    */

    'available_themes' => [
        'default' => [
            'name' => 'Default Theme',
            'description' => 'Clean and simple authentication design',
            'preview' => '/images/themes/default-preview.png',
        ],
        'mhtia' => [
            'name' => 'MHTIA Theme',
            'description' => 'Professional institutional design with enhanced branding',
            'preview' => '/images/themes/mhtia-preview.png',
        ],
        'mhtia-gradient' => [
            'name' => 'MHTIA Original',
            'description' => 'Authentic MHTIA design with gradient background and card layout',
            'preview' => '/images/themes/mhtia-gradient-preview.png',
        ],
        'modern' => [
            'name' => 'Modern Theme',
            'description' => 'Contemporary design with gradient backgrounds',
            'preview' => '/images/themes/modern-preview.png',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    |
    | Additional theme-specific settings that can be customized per theme.
    |
    */

    'theme_settings' => [
        'show_institution_name' => env('SHOW_INSTITUTION_NAME', true),
        'show_background_pattern' => env('SHOW_BACKGROUND_PATTERN', false),
        'enable_animations' => env('ENABLE_AUTH_ANIMATIONS', true),
        'card_style' => env('AUTH_CARD_STYLE', 'elevated'), // elevated, flat, bordered
    ],

];