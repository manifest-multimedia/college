<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Communication Module Services
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM_NUMBER'),
    ],

    'nalo' => [
        'api_key' => env('NALO_API_KEY'),
        'username' => env('NALO_USERNAME'),
        'password' => env('NALO_PASSWORD'),
        'sender_id' => env('NALO_SENDER_ID', 'PNMTC'),
        'reseller_prefix' => env('NALO_RESELLER_PREFIX', 'Resl_Nalo'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'url' => env('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'files_url' => env('OPENAI_FILES_API_URL', 'https://api.openai.com/v1/files'),
        'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
        'assistant_id' => env('OPENAI_ASSISTANT_ID','asst_NazjcT8qxaTUYexZuvcjiDss'),
    ],

];
