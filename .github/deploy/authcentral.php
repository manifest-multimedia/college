<?php

namespace Deployer;

require 'recipe/laravel.php';
set('application', 'pnmtc-authcentral');
set('repository', 'https://github.com/manifest-multimedia/authcentral.git');
set('branch', 'master');
set('keep_releases', 0); // Preserve existing release history; no old releases are deleted.
set('writable_use_sudo', false);
host('authcentral')
    ->setHostname('62.77.158.206')
    ->setRemoteUser('pnmtc')
    ->setDeployPath('/var/www/auth.pnmtc.edu.gh');

task('authcentral:assets', function () {
    $assets = getenv('AUTHCENTRAL_BUILD_ASSETS_PATH');
    if (! $assets || ! is_file($assets.'/manifest.json')) {
        throw new \RuntimeException('Built AuthCentral assets are missing.');
    }
    upload($assets.'/', '{{release_path}}/public/build/');
});
after('deploy:vendors', 'authcentral:assets');
after('deploy:failed', 'deploy:unlock');
