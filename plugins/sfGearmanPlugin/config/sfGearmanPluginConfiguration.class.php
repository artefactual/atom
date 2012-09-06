<?php

/**
 * sfGearmanPlugin configuration.
 *
 * @package     sfGearmanPlugin
 * @subpackage  config
 * @author      Benjamin VIELLARD <bicou@bicou.com>
 * @license     The MIT License
 * @version     SVN: $Id: sfGearmanPluginConfiguration.class.php 29482 2010-05-16 17:11:45Z bicou $
 */
class sfGearmanPluginConfiguration extends sfPluginConfiguration
{
  /**
   * plugin version
   *
   * @var string
   */
  const VERSION = '1.0.0-DEV';

  /**
   * path to config
   *
   * @var string
   */
  const CONFIG_PATH = 'config/gearman.yml';

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    if (!extension_loaded('gearman'))
    {
      throw new sfInitializationException('sfGearmanPlugin needs pecl/gearman module.');
    }

    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      // use config cache in application context
      $configCache = $this->configuration->getConfigCache();
      $configCache->registerConfigHandler(self::CONFIG_PATH, 'sfGearmanConfigHandler');
      sfGearman::$config = include $configCache->checkConfig(self::CONFIG_PATH);
    }
    else
    {
      // live parsing (task context)
      $configPaths = $this->configuration->getConfigPaths(self::CONFIG_PATH);
      sfGearman::$config = sfGearmanConfigHandler::getConfiguration($configPaths);
    }
  }
}

