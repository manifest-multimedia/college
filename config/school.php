<?php

/**
 * School Configuration File
 *
 * This file contains configuration settings specific to the school or educational institution.
 *
 * @return array
 */

return [
    'prefix' => 'COLLEGE/DEPT',
    'name' => env('INSTITUTION_NAME', env('SCHOOL_NAME', config('app.name'))),
];
