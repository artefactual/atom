<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->setPlugins(array('sfGearmanPlugin', 'sfDoctrinePlugin'));
    $this->setPluginPath('sfGearmanPlugin', dirname(__FILE__).'/../../../..');
  }

  public function setupPlugins()
  {
    $this->pluginConfigurations['sfGearmanPlugin']->connectTests();
  }
}
