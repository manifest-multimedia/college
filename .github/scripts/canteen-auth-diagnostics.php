<?php
// Read-only deployment diagnostic. Never prints keys, passwords, user records or environment contents.
$college = '/var/www/college.pnmtc.edu.gh/current';
chdir($college);
require $college.'/vendor/autoload.php';
$app = require $college.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$auth = '/var/www/auth.pnmtc.edu.gh/current';
$result = [
    'college_auth_method' => config('authentication.method'),
    'college_identity_url' => config('authentication.authcentral.api_url'),
    'college_verification_url' => config('authentication.authcentral.credential_verify_url'),
    'college_service_key_configured' => is_string(config('authentication.authcentral.service_key')) && strlen(config('authentication.authcentral.service_key')) >= 32,
    'authcentral_expected_directory_exists' => is_dir($auth),
    'authcentral_verification_controller_present' => is_file($auth.'/app/Http/Controllers/Api/CollegeCredentialController.php'),
    'authcentral_environment_readable' => is_readable($auth.'/.env'),
];
if (is_readable($auth.'/.env')) {
    $env = file_get_contents($auth.'/.env');
    preg_match('/^COLLEGE_SERVICE_KEY=(.*)$/m', $env, $match);
    $key = trim(trim($match[1] ?? ''), "\"'");
    $result['authcentral_service_key_configured'] = strlen($key) >= 32;
    unset($env, $key, $match);
}
echo json_encode($result, JSON_PRETTY_PRINT).PHP_EOL;
