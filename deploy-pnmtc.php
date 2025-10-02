<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'https://github.com/manifest-multimedia/college.git');
// add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache']);

// Enable SSH multiplexing for better performance and connection handling
set('ssh_multiplexing', true);
set('git_tty', false); // Disable TTY for git commands

// Enable verbose output for debugging
set('log_level', 4); // 4 = Verbose

// Hosts
host('62.77.158.206')
    ->set('remote_user', 'pnmtc')
    ->set('deploy_path', '/var/www/college.pnmtc.edu.gh')
    ->set('http_user', 'www-data')
    ->set('writable_mode', 'chmod')
    ->set('writable_chmod_mode', '0775');

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
    run('groups pnmtc || echo "Could not get groups for pnmtc"');
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
    }
});

// Modified permissions fix task
task('deploy:fix_permissions', function () {
    // Use ACL to manage permissions without requiring sudo
    run('setfacl -R -m u:www-data:rwX,u:pnmtc:rwX,d:u:www-data:rwX,d:u:pnmtc:rwX {{deploy_path}} || echo "ACL not available, falling back to chmod"');
    
    // Fallback to chmod if ACL is not available
    run('find {{deploy_path}} -type d -exec chmod 775 {} \;');
    run('find {{deploy_path}} -type f -exec chmod 664 {} \;');
    
    // Make specific files executable
    run('[ -f {{deploy_path}}/current/artisan ] && chmod +x {{deploy_path}}/current/artisan || echo "Artisan not found"');
});

task('deploy', [
    'debug:check_permissions',
    'deploy:prepare_structure',
    'deploy:prepare',
    'deploy:update_code',
    'deploy:vendors',
    'build:assets',
    'deploy:shared',
    'artisan:storage:link',
    'artisan:view:cache',
    'artisan:config:cache',
    'artisan:migrate',
    'deploy:publish',
    // 'deploy:fix_permissions',
]);

// Hooks
after('deploy:failed', 'deploy:unlock');
