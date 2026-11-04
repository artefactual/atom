<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');

// configuration
require_once dirname(__FILE__).'/../../config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::hasActive() ? ProjectConfiguration::getActive() : new ProjectConfiguration(realpath($_test_dir.'/..'));

sfContext::createInstance($configuration->getApplicationConfiguration(
    'qubit',
    'test',
    true
));

// Configure the QubitCache singleton to use a file-based cache so tests do
// not depend on APC or memcached being available
$cacheDir = sys_get_temp_dir().'/atom_phpunit_cache_'.getmypid();
sfConfig::set('app_cache_engine', 'sfFileCache');
sfConfig::set('app_cache_engine_param_cache_dir', $cacheDir);

// Instantiate the singleton now: some tests create a new application
// configuration, which reloads config/app.yml and resets app_cache_engine
QubitCache::getInstance();
