<?php

require_once '/home/carl/symfony/1.1/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    sfConfig::set('sf_plugins_dir', realpath(dirname(__FILE__) . '/../../../../..'));
  }
}
