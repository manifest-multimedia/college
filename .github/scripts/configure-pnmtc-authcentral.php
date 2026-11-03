<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;

// Private environments are updated on the server; no service keys leave the host.
function pnmtcEnvironmentValues(string $contents, array $values): string
{
    $lines = preg_split('/\r?\n/', $contents);
    $result = [];
    foreach ($lines as $line) {
        $key = explode('=', $line, 2)[0];
        if (! array_key_exists($key, $values)) {
            $result[] = $line;
        }
    }
    while ($result && end($result) === '') {
        array_pop($result);
    }
    foreach ($values as $key => $value) {
        if (! preg_match('/^[A-Z_]+$/', $key) || preg_match('/[\r\n]/', $value)) {
            throw new RuntimeException('Invalid environment update.');
        }
        $result[] = $key.'='.$value;
    }

    return implode("\n", $result)."\n";
}
function pnmtcAtomicEnvironmentWrite(string $path, string $contents): void
{
    if (fileowner($path) !== posix_geteuid()) {
        throw new RuntimeException('Environment ownership mismatch.');
    }
    $temp = tempnam(dirname($path), '.canteen-env-');
    try {
        if (file_put_contents($temp, $contents) === false || ! chmod($temp, fileperms($path) & 0777) || ! chgrp($temp, filegroup($path)) || ! rename($temp, $path)) {
            throw new RuntimeException('Cannot safely update an application environment.');
        }
    } finally {
        if (file_exists($temp)) {
            unlink($temp);
        }
    }
}
function pnmtcCacheConfiguration(string $directory): void
{
    $process = proc_open([PHP_BINARY, 'artisan', 'config:cache'], [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $directory);
    if (! is_resource($process)) {
        throw new RuntimeException('Cannot refresh application configuration.');
    }
    stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException('Application configuration cache failed.');
    }
}
if (defined('PNMTC_AUTH_ENV_TEST')) {
    return;
}
umask(0077);
$college = '/var/www/college.pnmtc.edu.gh/current';
$auth = '/var/www/auth.pnmtc.edu.gh/current';
$paths = ['college' => realpath($college.'/.env'), 'auth' => realpath($auth.'/.env')];
$old = [];
foreach ($paths as $name => $path) {
    if (! $path || ! is_writable($path) || fileowner($path) !== posix_geteuid()) {
        throw new RuntimeException('Private application environment is not writable by its owner.');
    }
    $old[$name] = file_get_contents($path);
    $dir = dirname($path).'/canteen-env-backups';
    if (! is_dir($dir)) {
        mkdir($dir, 0700, true);
    }
    $backup = $dir.'/.env-before-config-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3));
    if (! copy($path, $backup)) {
        throw new RuntimeException('Cannot back up private environment.');
    }chmod($backup, 0600);
}
$key = bin2hex(random_bytes(32));
try {
    pnmtcAtomicEnvironmentWrite($paths['auth'], pnmtcEnvironmentValues($old['auth'], [
        'COLLEGE_SERVICE_KEY' => $key, 'SSO_ALLOWED_CALLBACKS' => 'https://college.pnmtc.edu.gh/auth/callback', 'COLLEGE_CALLBACK_URL' => 'https://college.pnmtc.edu.gh/auth/callback',
    ]));
    pnmtcAtomicEnvironmentWrite($paths['college'], pnmtcEnvironmentValues($old['college'], [
        'AUTH_METHOD' => 'authcentral', 'AUTHCENTRAL_LOGIN_URL' => 'https://auth.pnmtc.edu.gh/login', 'AUTHCENTRAL_API_URL' => 'https://auth.pnmtc.edu.gh/api/user',
        'AUTHCENTRAL_CREDENTIAL_VERIFY_URL' => 'https://auth.pnmtc.edu.gh/api/v1/college/verify-credentials', 'AUTHCENTRAL_SERVICE_KEY' => $key, 'AUTHCENTRAL_CA_FILE' => '',
    ]));
    pnmtcCacheConfiguration($auth);
    pnmtcCacheConfiguration($college);
    chdir($college);
    require $college.'/vendor/autoload.php';
    $app = require $college.'/bootstrap/app.php';
    $app->make(Kernel::class)->bootstrap();
    $response = Http::acceptJson()->timeout(15)->withOptions(['allow_redirects' => false])->withHeaders(['X-College-Service-Key' => $key])
        ->post(config('authentication.authcentral.credential_verify_url'), ['username' => 'canteen-release-check-'.bin2hex(random_bytes(4)).'@example.invalid', 'password' => bin2hex(random_bytes(24))]);
    if ($response->status() !== 401) {
        throw new RuntimeException('AuthCentral verification readiness failed with HTTP '.$response->status());
    }
    if (config('authentication.method') !== 'authcentral') {
        throw new RuntimeException('College did not load the AuthCentral configuration.');
    }
    echo "PNMTC College now uses AuthCentral. Matching private service keys configured. AuthCentral rejected a nonexistent test identity with the expected HTTP 401.\n";
} catch (Throwable $error) {
    foreach ($paths as $name => $path) {
        pnmtcAtomicEnvironmentWrite($path, $old[$name]);
    }
    pnmtcCacheConfiguration($auth);
    pnmtcCacheConfiguration($college);
    fwrite(STDERR, "AuthCentral connection setup failed; prior private configuration restored. ".$error->getMessage().PHP_EOL);
    exit(1);
} finally {
    unset($key,$old);
}
