<?php

/**
 * sfThemePlugin configuration.
 *
 * @package     sfThemePlugin
 * @subpackage  config
 * @author      Your name here
 * @version     SVN: $Id: sfThemePluginConfiguration.class.php 2371 2009-04-16 20:42:44Z jablko $
 */
class sfThemePluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $enabledModules = sfConfig::get('sf_enabled_modules');
    $enabledModules[] = 'sfThemePlugin';
    sfConfig::set('sf_enabled_modules', $enabledModules);
  }
}
