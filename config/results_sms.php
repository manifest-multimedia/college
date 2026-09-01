<?php

return [
    // Default keeps the feature compatible with existing queue workers.
    'queue' => env('RESULTS_SMS_QUEUE', 'default'),
    'max_rows' => (int) env('RESULTS_SMS_MAX_ROWS', 10000),
    'max_file_kilobytes' => (int) env('RESULTS_SMS_MAX_FILE_KILOBYTES', 10240),
    'clamav_binary' => env('RESULTS_SMS_CLAMAV_BINARY'),
    'require_malware_scan' => (bool) env('RESULTS_SMS_REQUIRE_MALWARE_SCAN', false),
    'retention_days' => (int) env('RESULTS_SMS_RETENTION_DAYS', 180),
    'rate_limit_per_minute' => (int) env('RESULTS_SMS_RATE_LIMIT_PER_MINUTE', 45),
];
