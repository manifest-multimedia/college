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
];