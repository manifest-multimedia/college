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
        'login_url' => env('AUTHCENTRAL_LOGIN_URL', 'https://auth.pnmtc.edu.gh/login'),
        'api_url' => env('AUTHCENTRAL_API_URL', 'https://auth.pnmtc.edu.gh/api/user'),
        'signup_url' => env('AUTHCENTRAL_SIGNUP_URL', 'https://auth.pnmtc.edu.gh/sign-up'),
        'student_registration_url' => env('AUTHCENTRAL_STUDENT_REGISTRATION_URL', 'https://auth.pnmtc.edu.gh/student/register'),
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

];