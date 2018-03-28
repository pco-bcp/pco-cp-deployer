<?php
namespace Deployer;

use Dotenv\Dotenv;

require 'recipe/drupal8.php';

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

// Project name
set('application', getenv('APPLICATION'));

// Project repository
set('repository', 'https://github.com/drupalwxt/site-pco-cities.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', [

]);

add('shared_dirs', [
  'html/sites',
]);

// Writable dirs by web server
add('writable_dirs', [
  'html/sites/{{drupal_site}}/files',
]);

task('drush:cr', '
  cd {{release_path}}/html
  ../vendor/bin/drush cr
');

task('drush:updb', '
  cd {{release_path}}/html;
  ../vendor/bin/drush updb
');

task('drush:set_maintenance_mode', '
  cd {{release_path}}/html;
  ../vendor/bin/drush sset system.maintenance_mode 1
');

task('drush:unset_maintenance_mode', '
  cd {{release_path}}/html;
  ../vendor/bin/drush sset system.maintenance_mode 0
');

task('deploy', [
  'deploy:info',
  'deploy:prepare',
  'deploy:lock',
  'deploy:release',
  'deploy:update_code',
  'deploy:shared',
  'deploy:vendors',
  'drush:set_maintenance_mode',
  'drush:updb',
  'drush:cr',
  'deploy:symlink',
  'drush:unset_maintenance_mode',
  'drush:cr',
  'deploy:unlock',
  'cleanup'
]);

// Hosts
host(getenv('HOST_IP'))
  ->set('deploy_path', '~/{{application}}')
  ->set('branch', '8.x')
  ->user(getenv('DEPLOY_USER'))
  ->identityFile('~/.ssh/id_rsa');

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
