<?php

define('_ATOM_DIR', '/atom/src');
define('_ETC_DIR', '/usr/local/etc');


function getenv_default($name, $default)
{
  $value = getenv($name);

  if (false === $value)
  {
    return $default;
  }

  return $value;
}


function getenv_or_fail($name)
{
  $value = getenv($name);

  if (false === $value)
  {
    echo "Environment variable ${name} is not defined!";
    exit(1);
  }

  return $value;
}

function get_host_and_port($value, $default_port)
{
  $parts = explode(':', $value);

  if (count($parts) == 1)
  {
    $parts[1] = $default_port;
  }

  return array('host' => $parts[0], 'port' => $parts[1]);
}


$CONFIG = array(
  'atom.development_mode'   => filter_var(getenv_default('ATOM_DEVELOPMENT_MODE', false), FILTER_VALIDATE_BOOLEAN),
  'atom.elasticsearch_host' => getenv_or_fail('ATOM_ELASTICSEARCH_HOST'),
  'atom.memcached_host'     => getenv_or_fail('ATOM_MEMCACHED_HOST'),
  'atom.gearmand_host'      => getenv_or_fail('ATOM_GEARMAND_HOST'),
  'atom.mysql_dsn'          => getenv_or_fail('ATOM_MYSQL_DSN'),
  'atom.mysql_username'     => getenv_or_fail('ATOM_MYSQL_USERNAME'),
  'atom.mysql_password'     => getenv_or_fail('ATOM_MYSQL_PASSWORD'),
  'php.max_execution_time'  => getenv_default('ATOM_PHP_MAX_EXECUTION_TIME', '120'),
  'php.max_input_time'      => getenv_default('ATOM_PHP_MAX_INPUT_TIME', '120'),
  'php.memory_limit'        => getenv_default('ATOM_PHP_MEMORY_LIMIT', '512M'),
  'php.post_max_size'       => getenv_default('ATOM_PHP_POST_MAX_SIZE', '72M'),
  'php.upload_max_filesize' => getenv_default('ATOM_PHP_UPLOAD_MAX_FILESIZE', '64M'),
  'php.max_file_uploads'    => getenv_default('ATOM_PHP_MAX_FILE_UPLOADS', '20'),
  'php.date.timezone'       => getenv_default('ATOM_PHP_DATE_TIMEZONE', 'America/Vancouver')
);


#
# /apps/qubit/config/settings.yml
#

copy(_ATOM_DIR.'/apps/qubit/config/settings.yml.tmpl', _ATOM_DIR.'/apps/qubit/config/settings.yml');


#
# /config/propel.ini
#

@unlink(_ATOM_DIR.'/config/propel.ini');
touch(_ATOM_DIR.'/config/propel.ini');


#
# /apps/qubit/config/gearman.yml
#

$gearman_yml = <<<EOT
all:
  servers:
    default: ${CONFIG['atom.gearmand_host']}
EOT;

@unlink(_ATOM_DIR.'/apps/qubit/config/gearman.yml');
file_put_contents(_ATOM_DIR.'/apps/qubit/config/gearman.yml', $gearman_yml);


#
# /apps/qubit/config/app.yml
#

$parts = get_host_and_port($CONFIG['atom.memcached_host'], 11211);
$app_yml = <<<EOT
all:
  upload_limit: -1
  download_timeout: 10
  cache_engine: sfMemcacheCache
  cache_engine_param:
    host: ${parts['host']}
    port: ${parts['port']}
    prefix: atom
    storeCacheInfo: true
    persistent: true
  read_only: false
  htmlpurifier_enabled: false
EOT;

@unlink(_ATOM_DIR.'/apps/qubit/config/app.yml');
file_put_contents(_ATOM_DIR.'/apps/qubit/config/app.yml', $app_yml);


#
# /apps/qubit/config/factories.yml
#

$parts = get_host_and_port($CONFIG['atom.memcached_host'], 11211);
$factories_yml = <<<EOT
prod:
  storage:
    class: QubitCacheSessionStorage
    param:
      session_name: symfony
      session_cookie_httponly: true
      session_cookie_secure: true
      cache:
        class: sfMemcacheCache
        param:
          host: memcached
          port: 11211
          prefix: atom
          storeCacheInfo: true
          persistent: true

dev:
  storage:
    class: QubitCacheSessionStorage
    param:
      session_name: symfony
      session_cookie_httponly: true
      session_cookie_secure: true
      cache:
        class: sfMemcacheCache
        param:
          host: memcached
          port: 11211
          prefix: atom
          storeCacheInfo: true
          persistent: true

EOT;

@unlink(_ATOM_DIR.'/apps/qubit/config/factories.yml');
file_put_contents(_ATOM_DIR.'/apps/qubit/config/factories.yml', $factories_yml);


#
# /config/search.yml
#

$parts = get_host_and_port($CONFIG['atom.elasticsearch_host'], 9200);
$search_yml = <<<EOT
all:
  server:
    host: ${parts['host']}
    post: ${parts['port']}

EOT;

@unlink(_ATOM_DIR.'/config/search.yml');
file_put_contents(_ATOM_DIR.'/config/search.yml', $search_yml);


#
# /config/config.php
#

$mysql_config = array(
  'all' => array(
    'propel' => array(
      'class' => 'sfPropelDatabase',
      'param' => array(
        'encoding' => 'utf8',
        'persistent' => true,
        'pooling' => true,
        'dsn' => $CONFIG['atom.mysql_dsn'],
        'username' => $CONFIG['atom.mysql_username'],
        'password' => $CONFIG['atom.mysql_password'],
      ),
    ),
  ),
  'dev' => array(
    'propel' => array(
      'param' => array(
        'classname' => 'DebugPDO',
        'debug' => array(
          'realmemoryusage' => true,
          'details' => array(
            'time' => array('enabled' => true,),
            'slow' => array('enabled' => true, 'threshold' => 0.1,),
            'mem' => array('enabled' => true,),
            'mempeak' => array ('enabled' => true,),
            'memdelta' => array ('enabled' => true,),
          ),
        ),
      ),
    ),
  ),
  'test' => array(
    'propel' => array(
      'param' => array(
        'classname' => 'DebugPDO',
      ),
    ),
  ),
);

$config_php = "<?php\n\nreturn ".var_export($mysql_config, 1).";\n\n?>\n";

@unlink(_ATOM_DIR.'/config/config.php');
file_put_contents(_ATOM_DIR.'/config/config.php', $config_php);


#
# php ini
#

$php_ini = <<<EOT
[PHP]
output_buffering = 4096
expose_php = off
log_errors = on
error_reporting = E_ALL
display_errors = stderr
display_startup_errors = on
max_execution_time = ${CONFIG['php.max_execution_time']}
max_input_time = ${CONFIG['php.max_input_time']}
memory_limit = ${CONFIG['php.memory_limit']}
log_errors = on
post_max_size = ${CONFIG['php.post_max_size']}
default_charset = UTF-8
cgi.fix_pathinfo = off
upload_max_filesize = ${CONFIG['php.upload_max_filesize']}
max_file_uploads = ${CONFIG['php.max_file_uploads']}
date.timezone = ${CONFIG['php.date.timezone']}
session.use_only_cookies = off
opcache.fast_shutdown = on
opcache.max_accelerated_files = 10000
opcache.validate_timestamps = off

EOT;

if ($CONFIG['atom.development_mode'])
{
  $php_ini .= <<<EOT
\n
# Development-specific configuration
expose_php = on
opcache.validate_timestamps = on

EOT;
}

@unlink(_ETC_DIR.'/php/php.ini');
file_put_contents(_ETC_DIR.'/php/php.ini', $php_ini);


#
# fpm ini
#

$fpm_ini = <<<EOT
[global]
error_log = /proc/self/fd/2
daemonize = no

[atom]
access.log = /proc/self/fd/2
clear_env = no
catch_workers_output = yes
user = root
group = root
listen = [::]:9000
pm = dynamic
pm.max_children = 5
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 3

EOT;

@unlink(_ETC_DIR.'/php-fpm.d/atom.conf');
file_put_contents(_ETC_DIR.'/php-fpm.d/atom.conf', $fpm_ini);
