<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../bootstrap/functional.php';
require 'config/sfProjectConfiguration.class.php';
require dirname(__FILE__) . '/fixtures/config/ProjectConfiguration.class.php';
require 'config/sfApplicationConfiguration.class.php';
require 'util/xfSearchConfiguration.class.php';

$t = new lime_test(2, new lime_output_color);

$configuration = ProjectConfiguration::getApplicationConfiguration('xfSearchWrapper', 'test', true);
sfContext::createInstance($configuration);

// remove all cache
sf_functional_test_shutdown();
register_shutdown_function('sf_functional_test_shutdown');

$t->ok($configuration instanceof xfSearchConfiguration, 'configuration is an instance of xfSearchConfiguration');
$t->ok(sfConfig::get('sf_test'), 'configuration set "sf_test" to true');
