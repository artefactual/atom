<?php

/**
 * sfThemePlugin configuration.
 *
 * @package     sfThemePlugin
 * @subpackage  config
 * @author      Your name here
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
