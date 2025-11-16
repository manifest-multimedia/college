<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'https://github.com/manifest-multimedia/college.git');

add('shared_dirs', ['storage', 'public/images/logos']);
add('writable_dirs', ['storage', 'bootstrap/cache', 'public/images/logos']);

// Enable SSH multiplexing for better performance and connection handling
set('ssh_multiplexing', true);
set('git_tty', false); // Disable TTY for git commands

// Hosts
host('mhtia')
    ->setHostname('83.147.39.47')
    ->set('remote_user', 'administrator')
    ->set('deploy_path', '/var/www/mhtia.siteshowcase.top')
    ->set('labels', ['stage' => 'staging']);

host('mhtia-prod')
    ->setHostname('62.77.158.206')
    ->set('remote_user', 'pnmtc')
    ->set('deploy_path', '/var/www/mhtia.edu.gh')
    ->set('http_user', 'www-data')
    ->set('writable_mode', 'chmod')
    ->set('writable_chmod_mode', '0775')
    ->set('labels', ['stage' => 'production']);

host('pnmtc')
    ->setHostname('62.77.158.206')
    ->set('remote_user', 'pnmtc')
    ->set('deploy_path', '/var/www/college.pnmtc.edu.gh')
    ->set('http_user', 'www-data')
    ->set('writable_mode', 'chmod')
    ->set('writable_chmod_mode', '0775')
    ->set('labels', ['stage' => 'production']);

// Tasks
task('build:assets', function () {
    // Ensure node_modules is properly installed
    run('cd {{release_path}} && npm install --no-audit --no-fund');
    
    // Clear any cached assets
    run('cd {{release_path}} && rm -rf public/build');
    
    // Build with production flag to ensure proper optimization
    run('cd {{release_path}} && npm run prod || npm run build');
    
    // Verify the build was successful
    run('cd {{release_path}} && [ -d public/build ] || echo "Warning: Build directory not found!"');
});

// Debug task to check permissions before deployment
task('debug:check_permissions', function () {
    writeln('Checking permissions for deploy path...');
    run('ls -la {{deploy_path}}');
    writeln('Checking user and group memberships...');
    run('id');
    writeln('Checking if www-data group exists and pnmtc is a member...');
    run('getent group www-data || echo "Group www-data does not exist"');
    run('groups {{remote_user}} || echo "Could not get groups for {{remote_user}}"');
});

// Prepare proper directory structure and permissions
task('deploy:prepare_structure', function () {
    run('if [ ! -d {{deploy_path}} ]; then mkdir -p {{deploy_path}}; fi');
    run('if [ ! -d {{deploy_path}}/shared ]; then mkdir -p {{deploy_path}}/shared; fi');
    run('if [ ! -d {{deploy_path}}/releases ]; then mkdir -p {{deploy_path}}/releases; fi');
    
    // Create necessary shared directories with proper permissions
    foreach (get('shared_dirs') as $dir) {
        run("if [ ! -d {{deploy_path}}/shared/$dir ]; then mkdir -p {{deploy_path}}/shared/$dir; fi");
        run("chmod 775 {{deploy_path}}/shared/$dir");
        // Ensure www-data group ownership
        run("chgrp www-data {{deploy_path}}/shared/$dir || echo 'Could not change group ownership'");
    }
    
    // Ensure critical Laravel directories exist with proper permissions
    $criticalDirs = [
        'storage/framework/cache',
        'storage/framework/sessions',
        'storage/framework/views',
        'storage/logs',
        'storage/app',
        'storage/app/public',
        'storage/app/private',
        'storage/app/private/livewire-tmp'
    ];
    
    foreach ($criticalDirs as $dir) {
        run("if [ ! -d {{deploy_path}}/shared/$dir ]; then mkdir -p {{deploy_path}}/shared/$dir; fi");
        run("chmod 775 {{deploy_path}}/shared/$dir");
        run("chgrp www-data {{deploy_path}}/shared/$dir || echo 'Could not change group ownership for $dir'");
        run("chmod g+s {{deploy_path}}/shared/$dir || echo 'Could not set sticky bit for $dir'");
    }
});

// Setup upload directories with proper permissions
task('deploy:setup_uploads', function () {
    writeln('Setting up upload directories...');
    
    // Ensure logos directory exists in shared folder
    run('mkdir -p {{deploy_path}}/shared/public/images/logos');
    
    // Set proper permissions first (this should work even if ownership fails)
    run('chmod -R 775 {{deploy_path}}/shared/public/images/ || echo "Warning: Could not set permissions"');
    
    // Set sticky bit to ensure new files inherit group ownership
    run('chmod g+s {{deploy_path}}/shared/public/images/logos || echo "Warning: Could not set sticky bit"');
    
    // Try to set ownership, but don't fail if it doesn't work
    // This might fail if files are owned by www-data and we're running as pnmtc
    run('chown -R {{remote_user}}:www-data {{deploy_path}}/shared/public/images/ || echo "Warning: Could not change ownership - files may be owned by web server"');
    
    // Ensure the directory itself has proper group ownership at minimum
    run('chgrp -R www-data {{deploy_path}}/shared/public/images/ || echo "Warning: Could not change group ownership"');
    
    // Set ACL permissions as backup if available
    if (test('which setfacl')) {
        run('setfacl -R -m g:www-data:rwx {{deploy_path}}/shared/public/images/ || echo "Warning: Could not set ACL permissions"');
        run('setfacl -R -d -m g:www-data:rwx {{deploy_path}}/shared/public/images/ || echo "Warning: Could not set default ACL permissions"');
    }
    
    // Verify final permissions
    run('ls -la {{deploy_path}}/shared/public/images/ || echo "Could not list directory"');
    
    writeln('✅ Upload directories configured (some permission changes may have failed - this is usually okay)');
});

// Modified permissions fix task
task('deploy:fix_permissions', function () {
    writeln('Fixing file permissions...');
    
    // Use ACL to manage permissions without requiring sudo
    run('setfacl -R -m u:www-data:rwX,u:{{remote_user}}:rwX,d:u:www-data:rwX,d:u:{{remote_user}}:rwX {{deploy_path}}/current || echo "ACL not available, falling back to chmod"');
    
    // Fallback to chmod if ACL is not available
    run('find {{deploy_path}}/current -type d -exec chmod 755 {} \; || true');
    run('find {{deploy_path}}/current -type f -exec chmod 644 {} \; || true');
    
    // Make specific files executable
    run('chmod +x {{deploy_path}}/current/artisan || echo "Artisan not found"');
    
    // Fix storage directory permissions - critical for Laravel cache/views/sessions
    writeln('Fixing storage directory permissions...');
    run('find {{deploy_path}}/shared/storage -type d -exec chmod 775 {} \; || true');
    run('find {{deploy_path}}/shared/storage -type d -exec chgrp www-data {} \; || true');
    run('find {{deploy_path}}/shared/storage -type d -exec chmod g+s {} \; || true');
    run('find {{deploy_path}}/shared/storage -type f -exec chmod 664 {} \; || true');
    run('find {{deploy_path}}/shared/storage -type f -exec chgrp www-data {} \; || true');
    
    // Set ACL permissions for critical directories (especially Livewire temp directory)
    writeln('Setting ACL permissions for storage directories...');
    run('setfacl -R -m u:{{remote_user}}:rwx,u:www-data:rwx,g:www-data:rwx {{deploy_path}}/shared/storage/app/private || echo "ACL not available for storage/app/private"');
    run('setfacl -R -d -m u:{{remote_user}}:rwx,u:www-data:rwx,g:www-data:rwx {{deploy_path}}/shared/storage/app/private || echo "Default ACL not available for storage/app/private"');
    run('setfacl -R -m u:{{remote_user}}:rwx,u:www-data:rwx,g:www-data:rwx {{deploy_path}}/shared/storage/framework || echo "ACL not available for storage/framework"');
    run('setfacl -R -d -m u:{{remote_user}}:rwx,u:www-data:rwx,g:www-data:rwx {{deploy_path}}/shared/storage/framework || echo "Default ACL not available for storage/framework"');
    
    // Ensure bootstrap/cache is writable
    run('chmod -R 775 {{deploy_path}}/current/bootstrap/cache || true');
    run('chgrp -R www-data {{deploy_path}}/current/bootstrap/cache || true');
    
    // Ensure upload directories remain writable
    run('chmod -R 775 {{deploy_path}}/shared/public/images/ || true');
    run('chgrp -R www-data {{deploy_path}}/shared/public/images/ || true');
});

// Ensure .env file has proper permissions
task('deploy:fix_env_permissions', function () {
    writeln('Fixing .env file permissions...');
    
    // Ensure shared directory has proper permissions
    run('chmod 775 {{deploy_path}}/shared || true');
    run('chgrp www-data {{deploy_path}}/shared || true');
    
    // Ensure .env file has proper permissions for web server to write
    run('chmod 664 {{deploy_path}}/shared/.env || true');
    run('chgrp www-data {{deploy_path}}/shared/.env || true');
    
    writeln('✅ Environment file permissions fixed');
});

// Ensure Laravel application key is set
task('deploy:ensure_app_key', function () {
    writeln('Checking Laravel application key...');
    
    // Check if APP_KEY is set in .env
    $result = run('cd {{deploy_path}}/current && grep -E "^APP_KEY=" .env | grep -v "APP_KEY=$" || echo "MISSING"');
    
    if (trim($result) === 'MISSING') {
        writeln('APP_KEY not found or empty, generating new key...');
        run('cd {{deploy_path}}/current && php artisan key:generate --force');
        writeln('✅ Application key generated successfully');
    } else {
        writeln('✅ Application key is already set');
    }
});

// Debug upload directory structure and permissions
desc('Debug upload directory structure and permissions');
task('debug:upload_structure', function () {
    $paths = [
        '{{deploy_path}}/shared/public/images',
        '{{deploy_path}}/shared/public/images/logos',
        '{{deploy_path}}/current/public/images'
    ];
    
    foreach ($paths as $path) {
        if (test("[ -e $path ]")) {
            writeln("📁 Path: $path");
            run("ls -la $path");
            
            if (test("[ -L $path ]")) {
                $target = run("readlink $path");
                writeln("🔗 Symlink target: $target");
            }
            
            // Check ACL if available
            if (test('which getfacl')) {
                run("getfacl $path");
            }
            
            writeln("---");
        } else {
            writeln("❌ Path not found: $path");
        }
    }
});

task('deploy', [
    'deploy:prepare',
    'deploy:prepare_structure',
    'deploy:update_code',
    'deploy:vendors',
    'build:assets',
    'deploy:shared',
    'deploy:fix_env_permissions',
    'deploy:setup_uploads',
    'deploy:ensure_app_key',
    'artisan:storage:link',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:migrate',
    'deploy:publish',
    'deploy:fix_permissions',
]);

// Hooks
after('deploy:failed', 'deploy:unlock');
