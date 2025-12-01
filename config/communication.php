<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Provider
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS provider that is used to send SMS
    | messages. You may change this to any of the providers defined in the
    | "providers" array below.
    |
    | Available options: 'twilio', 'manifest-digital', 'nalo'
    |
    */
    'default_sms_provider' => env('SMS_PROVIDER', 'twilio'),

    /*
    |--------------------------------------------------------------------------
    | SMS Delivery Status Check Frequency
    |--------------------------------------------------------------------------
    |
    | How often (in minutes) should the system check for SMS delivery status
    | updates. This applies to providers that support status updates through
    | callbacks or API requests.
    |
    */
    'status_check_frequency' => env('SMS_STATUS_CHECK_FREQUENCY', 30),

    /*
    |--------------------------------------------------------------------------
    | SMS Log Retention Period
    |--------------------------------------------------------------------------
    |
    | How long (in days) should SMS logs be kept in the database. Set to 0
    | to keep logs indefinitely.
    |
    */
    'log_retention_days' => env('SMS_LOG_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | SMS Providers Display Names
    |--------------------------------------------------------------------------
    |
    | This maps the internal provider keys to user-friendly display names
    | shown in the UI.
    |
    */
    'provider_display_names' => [
        'twilio' => 'Twilio',
        'nalo' => 'Manifest Digital',
        'manifest-digital' => 'Manifest Digital',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Assistant (OpenAI) Rate Limiting & Batch Processing
    |--------------------------------------------------------------------------
    |
    | Configuration for OpenAI API rate limiting prevention and bulk operations
    | to optimize AI Sensei question import performance.
    |
    */
    'openai' => [
        // Delay between sequential API calls (milliseconds) to prevent rate limiting
        'api_call_delay_ms' => env('OPENAI_API_CALL_DELAY_MS', 500),

        // Maximum retries for rate-limited requests
        'rate_limit_max_retries' => env('OPENAI_RATE_LIMIT_MAX_RETRIES', 3),

        // Exponential backoff base delay (seconds)
        'rate_limit_backoff_base' => env('OPENAI_RATE_LIMIT_BACKOFF_BASE', 2),

        // Maximum batch size for very large imports (questions per transaction)
        // Set to 0 for unlimited (single transaction for all questions)
        'max_questions_per_batch' => env('OPENAI_MAX_QUESTIONS_PER_BATCH', 0),

        // Minimum batch size warning threshold
        // Batches smaller than this (when more questions exist) will trigger warnings
        'min_batch_warning_threshold' => env('OPENAI_MIN_BATCH_WARNING_THRESHOLD', 10),

        // Enable smart batch detection to reject incremental imports
        'enable_batch_detection' => env('OPENAI_ENABLE_BATCH_DETECTION', true),
    ],
];
