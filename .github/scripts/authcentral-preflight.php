<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

// Run only on the known PNMTC production host through its existing deployment account.
$base = '/var/www/auth.pnmtc.edu.gh';
$college = '/var/www/college.pnmtc.edu.gh/current';
umask(0077);
foreach ([$base, $base.'/shared', $base.'/releases'] as $directory) {
    if (! is_dir($directory) || ! is_writable($directory)) {
        throw new RuntimeException('AuthCentral deployment directories are not writable by this deployment account.');
    }
}
if (file_exists($base.'/.dep/deploy.lock')) {
    throw new RuntimeException('AuthCentral has another deployment lock. Stop and review it first.');
}
$stamp = gmdate('Ymd-His').'-'.bin2hex(random_bytes(3));
foreach ([$base.'/current/.env', $college.'/.env'] as $env) {
    $path = realpath($env);
    if (! $path || ! is_readable($path) || ! is_writable($path) || fileowner($path) !== posix_geteuid()) {
        throw new RuntimeException('The deployment account must own and be able to update both application environments.');
    }
    $backupDirectory = dirname($path).'/canteen-env-backups';
    if (! is_dir($backupDirectory)) {
        mkdir($backupDirectory, 0700, true);
    }
    if (! copy($path, $backupDirectory.'/.env-'.$stamp)) {
        throw new RuntimeException('Cannot save a private environment backup.');
    }
    chmod($backupDirectory.'/.env-'.$stamp, 0600);
}
chdir($base.'/current');
require $base.'/current/vendor/autoload.php';
$app = require $base.'/current/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$db = DB::connection()->getConfig();
if (($db['driver'] ?? '') !== 'mysql') {
    throw new RuntimeException('This backup routine requires the existing MySQL deployment.');
}
$quote = fn ($value) => '"'.strtr((string) $value, ['\\' => '\\\\', '"' => '\\"', "\n" => '\\n', "\r" => '\\r']).'"';
$config = "[client]\nuser=".$quote($db['username'])."\npassword=".$quote($db['password'])."\n";
if (! empty($db['unix_socket'])) {
    $config .= 'socket='.$quote($db['unix_socket'])."\n";
} else {
    $config .= 'host='.$quote($db['host'])."\nport=".(int) ($db['port'] ?? 3306)."\n";
}
$temp = tempnam(sys_get_temp_dir(), 'authcentral-backup-');
chmod($temp, 0600);
file_put_contents($temp, $config);
$directory = $base.'/shared/canteen-database-backups';
if (! is_dir($directory)) {
    mkdir($directory, 0700, true);
}
$backup = $directory.'/before-deploy-'.$stamp.'.sql';
$output = fopen($backup, 'x');
chmod($backup, 0600);
try {
    $process = proc_open(['mysqldump', '--defaults-extra-file='.$temp, '--single-transaction', '--no-tablespaces', '--hex-blob', $db['database']], [0 => ['file', '/dev/null', 'r'], 1 => $output, 2 => ['pipe', 'w']], $pipes);
    if (! is_resource($process)) {
        throw new RuntimeException('Cannot start the private database backup.');
    }
    stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $status = proc_close($process);
    fclose($output);
    if ($status !== 0 || filesize($backup) < 100) {
        throw new RuntimeException('AuthCentral database backup failed; deployment must not continue.');
    }
} finally {
    if (is_resource($output)) {
        fclose($output);
    }unlink($temp);
    unset($config,$db);
}
echo "AuthCentral preflight passed; private database and environment backups saved.\n";
