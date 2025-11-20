<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Method
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication method for the application.
    |
    | Supported methods:
    | - "authcentral": Uses AuthCentral service for authentication
    | - "regular": Uses standard Laravel username/password authentication
    |
    */

    'method' => env('AUTH_METHOD', 'authcentral'),

    /*
    |--------------------------------------------------------------------------
    | AuthCentral Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options specific to AuthCentral authentication.
    |
    */

    'authcentral' => [
        'login_url' => env('AUTHCENTRAL_LOGIN_URL', 'https://auth.example.edu/login'),
        'api_url' => env('AUTHCENTRAL_API_URL', 'https://auth.example.edu/api/user'),
        'signup_url' => env('AUTHCENTRAL_SIGNUP_URL', 'https://auth.example.edu/sign-up'),
        'student_registration_url' => env('AUTHCENTRAL_STUDENT_REGISTRATION_URL', 'https://auth.example.edu/student/register'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Synchronization Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for syncing passwords between AuthCentral and CIS.
    |
    */

    'password_sync' => [
        'enabled' => env('PASSWORD_SYNC_ENABLED', true),
        'api_key' => env('PASSWORD_SYNC_API_KEY'),
        'webhook_url' => env('PASSWORD_SYNC_WEBHOOK_URL', '/api/auth-sync/password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Regular Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for standard Laravel authentication.
    |
    */

    'regular' => [
        'default_role' => env('AUTH_REGULAR_DEFAULT_ROLE', 'Staff'),
        'allow_registration' => env('AUTH_ALLOW_REGISTRATION', false),
        'allow_staff_registration' => env('AUTH_ALLOW_STAFF_REGISTRATION', true),
        'allow_student_registration' => env('AUTH_ALLOW_STUDENT_REGISTRATION', true),
        'student_default_status' => env('AUTH_STUDENT_DEFAULT_STATUS', 'active'),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Registration Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that apply to user registration regardless of auth method.
    |
    */

    'registration' => [
        'redirect_after_staff_registration' => env('AUTH_STAFF_REGISTRATION_REDIRECT', '/dashboard'),
        'redirect_after_student_registration' => env('AUTH_STUDENT_REGISTRATION_REDIRECT', '/student-dashboard'),
        'require_email_verification' => env('AUTH_REQUIRE_EMAIL_VERIFICATION', false),
        'auto_login_after_registration' => env('AUTH_AUTO_LOGIN_AFTER_REGISTRATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security-related settings for the authentication system.
    |
    */

    'security' => [
        'log_authentication_attempts' => env('AUTH_LOG_ATTEMPTS', true),
        'session_lifetime' => env('AUTH_SESSION_LIFETIME', 120),
        // Note: Removed password restrictions to enable flexible authentication
        // Users can now choose between SSO and direct email/password login
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Method Constants
    |--------------------------------------------------------------------------
    |
    | These constants define the available authentication methods.
    |
    */

    'methods' => [
        'authcentral' => 'authcentral',
        'regular' => 'regular',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Mapping
    |--------------------------------------------------------------------------
    |
    | Maps authentication contexts to system roles.
    |
    */

    'roles' => [
        'staff_default' => env('AUTH_STAFF_DEFAULT_ROLE', 'Staff'),
        'student_default' => env('AUTH_STUDENT_DEFAULT_ROLE', 'Student'),
        'authcentral_fallback' => env('AUTH_AUTHCENTRAL_FALLBACK_ROLE', 'Staff'),
    ],

];
