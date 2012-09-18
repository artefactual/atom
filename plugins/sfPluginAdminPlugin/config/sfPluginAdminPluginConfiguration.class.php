<?php

/**
 * sfPluginAdminPlugin configuration.
 *
 * @package     sfPluginAdminPlugin
 * @subpackage  config
 * @author      Your name here
 */
class sfPluginAdminPluginConfiguration extends sfPluginConfiguration
{
  public static
    $pluginNames;

  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    // Project classes, e.g. QubitSetting, not loaded unless
    // sfApplicationConfiguration,
    // http://qubit-toolkit.org/wiki/index.php?title=Autoload#Plugins
    if (!$this->configuration instanceof sfApplicationConfiguration)
    {
      return;
    }

    $enabledModules = sfConfig::get('sf_enabled_modules');
    $enabledModules[] = 'sfPluginAdminPlugin';
    sfConfig::set('sf_enabled_modules', $enabledModules);

    // Stash plugin names enabled in ProjectConfiguration.class.php for
    // sfPluginAdminPluginIndexAction. Where is the best place to stash it?
    // This is probably not the best place : P
    sfPluginAdminPluginConfiguration::$pluginNames = $this->configuration->getPlugins();

    new sfDatabaseManager($this->configuration);

    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'plugins');

    try
    {
      if (1 == count($query = QubitSetting::get($criteria)))
      {
        // http://qubit-toolkit.org/wiki/index.php?title=Autoload
        $this->dispatcher->disconnect('autoload.filter_config', array($this->configuration, 'filterAutoloadConfig'));

        $pluginNames = unserialize($query[0]->__get('value', array('sourceCulture' => true)));
        $this->configuration->enablePlugins($pluginNames);

        $pluginPaths = $this->configuration->getAllPluginPaths();
        foreach ($pluginNames as $name)
        {
          if (!isset($pluginPaths[$name]))
          {
            throw new InvalidArgumentException('The plugin "'.$name.'" does not exist.');
          }

          // Copied from sfProjectConfiguration::loadPlugins()
          $className = $name.'Configuration';
          if (!is_readable($path = $pluginPaths[$name].'/config/'.$className.'.class.php'))
          {
            $configuration = new sfPluginConfigurationGeneric($this->configuration, $pluginPaths[$name], $name);
          }
          else
          {
            require_once $path;
            $configuration = new $className($this->configuration, $pluginPaths[$name], $name);
          }

          // Is this cached?
          $configuration->initializeAutoload();
          $configuration->initialize();

          $this->configuration->pluginConfigurations[$name] = $configuration;
        }

        $this->dispatcher->connect('autoload.filter_config', array($this->configuration, 'filterAutoloadConfig'));
      }
    }
    catch (PropelException $e)
    {
      // Silently swallow PropelException because we can't tell at this point
      // if we are in install, and install plugin can't listen for an exception
      // thrown at this point, is this the best solution?
    }
  }
}
