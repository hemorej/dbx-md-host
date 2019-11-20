<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'dropbox');

// Project repository
set('repository', '');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

set('deploy_path', '');

// Shared files/dirs between deploys 
set('writable_mode', 'chmod');
set('writable_chmod_mode', 777);
set('writable_chmod_recursive', true);
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', [
    'storage',
    'public',
    'bootstrap/cache',
]);

set('keep_releases', 10);
set('allow_anonymous_stats', false);

// Hosts

host('jerome-arfouche.com')
    ->set('deploy_path', get('deploy_path'))
	->user('')
    ->set('branch', 'master');
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');