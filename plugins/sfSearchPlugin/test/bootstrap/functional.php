<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/unit.php';

function sf_functional_test_shutdown()
{
  sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
  sfToolkit::clearDirectory(sfConfig::get('sf_log_dir'));
}

function bootstrap($app = 'frontend')
{
  require_once dirname(__FILE__) . '/../functional/fixtures/config/ProjectConfiguration.class.php';
  $configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', true);
  sfContext::createInstance($configuration);

  // remove all cache
  sf_functional_test_shutdown();

  register_shutdown_function('sf_functional_test_shutdown');

  require dirname(__FILE__) . '/../functional/xfTestBrowser.class.php';

  return $configuration;
}
