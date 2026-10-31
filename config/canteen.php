<?php

return [

    // File location only; institution identity comes from Student ID settings.
    'finance_import_path' => env('PNMTC_FINANCE_PATH', base_path('finance-import')),

    /*
    |--------------------------------------------------------------------------
    | Canteen clearance threshold
    |--------------------------------------------------------------------------
    |
    | No institution-independent payment rule is assumed. Save the approved
    | threshold through CanteenPolicy. Qualification lists contain no amounts.
    |
    */

    'payment_threshold' => null,

    // Explicit scope for the compatibility seeder; never infer it from intake IDs.
    'import_academic_year' => null,
    'import_semester' => null,

    /*
    |--------------------------------------------------------------------------
    | Roster sync page size
    |--------------------------------------------------------------------------
    |
    | Maximum number of student records returned per roster sync page. The
    | app pages through the roster with a cursor, so this only bounds the
    | work done per request as the student body grows.
    |
    */

    'roster_page_size' => (int) env('CANTEEN_ROSTER_PAGE_SIZE', 500),

    /*
    |--------------------------------------------------------------------------
    | Verification sync batch size
    |--------------------------------------------------------------------------
    |
    | Maximum number of offline verifications accepted per sync request.
    |
    */

    'verification_batch_size' => (int) env('CANTEEN_VERIFICATION_BATCH_SIZE', 500),
];
