<?php

/**
 * sfPluginAdminPlugin configuration.
 *
 * @author      Your name here
 */
class sfPluginAdminPluginConfiguration extends sfPluginConfiguration
{
    public static $pluginNames;
    public static $loadPlugins = true;

    /**
     * @see sfPluginConfiguration
     */
    public function initialize()
    {
        // Project classes, e.g. QubitSetting, not loaded unless
        // sfApplicationConfiguration,
        // http://accesstomemory.org/wiki/index.php?title=Autoload#Plugins
        if (!$this->configuration instanceof sfApplicationConfiguration) {
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

        $criteria = new Criteria();
        $criteria->add(QubitSetting::NAME, 'plugins');

        try {
            if (sfPluginAdminPluginConfiguration::$loadPlugins && 1 == count($query = QubitSetting::get($criteria))) {
                // http://accesstomemory.org/wiki/index.php?title=Autoload
                $this->dispatcher->disconnect('autoload.filter_config', [$this->configuration, 'filterAutoloadConfig']);

                $pluginNames = unserialize($query[0]->__get('value', ['sourceCulture' => true]));

                // if (isset($_GET['t']))
                // {
                //   if (false !== $key = array_search('arArchivesCanadaPlugin', $pluginNames))
                //   {
                //     unset($pluginNames[$key]);
                //     $pluginNames[] = 'arDominionPlugin';
                //   }
                // }

                // Find available plugins in the filesystem
                $pluginPaths = $this->configuration->getAllPluginPaths();

                // Ignore plugins configured in the db but not available (removed)
                foreach (array_diff($pluginNames, array_keys($pluginPaths)) as $item) {
                    if (false !== $pos = array_search($item, $pluginNames)) {
                        unset($pluginNames[$pos]);

                        $this->dispatcher->notify(new sfEvent($this, 'application.log', [sprintf('The plugin "%s" does not exist.', $item)]));
                    }
                }

                // Check request to see if a theme is requested,
                // validate it exists and it's actually a theme.
                if (
                    isset($_REQUEST['theme'])
                    && !in_array($_REQUEST['theme'], $pluginNames)
                    && isset($pluginPaths[$_REQUEST['theme']])
                ) {
                    $themeConfigClass = $_REQUEST['theme'].'Configuration';
                    $themeConfigPath = $pluginPaths[$_REQUEST['theme']]
                        .'/config/'.$themeConfigClass.'.class.php';

                    if (is_readable($themeConfigPath)) {
                        require_once $themeConfigPath;

                        if (
                            isset($themeConfigClass::$summary)
                            && 1 === preg_match(
                                '/theme/i',
                                $themeConfigClass::$summary
                            )
                        ) {
                            // Add requested theme plugin to the list
                            // and store its value for later.
                            $pluginNames[] = $_REQUEST['theme'];
                            $requestedTheme = $_REQUEST['theme'];
                        }
                    }
                }

                foreach ($pluginNames as $name) {
                    if (!isset($pluginPaths[$name])) {
                        throw new InvalidArgumentException('The plugin "'.$name.'" does not exist.');
                    }

                    // Copied from sfProjectConfiguration::loadPlugins()
                    $className = $name.'Configuration';
                    if (!is_readable($path = $pluginPaths[$name].'/config/'.$className.'.class.php')) {
                        $configuration = new sfPluginConfigurationGeneric($this->configuration, $pluginPaths[$name], $name);
                    } else {
                        require_once $path;

                        // Do not enable other themes if there
                        // is a theme plugin requested.
                        if (
                            isset($requestedTheme)
                            && $requestedTheme != $name
                            && isset($className::$summary)
                            && 1 === preg_match(
                                '/theme/i',
                                $className::$summary
                            )
                        ) {
                            continue;
                        }

                        $configuration = new $className($this->configuration, $pluginPaths[$name], $name);
                    }

                    // Is this cached?
                    $configuration->initializeAutoload();
                    $configuration->initialize();

                    $this->configuration->enablePlugins([$name]);
                    $this->configuration->pluginConfigurations[$name] = $configuration;
                }

                $this->dispatcher->connect('autoload.filter_config', [$this->configuration, 'filterAutoloadConfig']);
            }
        } catch (PropelException $e) {
            // Silently swallow PropelException because we can't tell at this point
            // if we are in install, and install plugin can't listen for an exception
            // thrown at this point, is this the best solution?
        }
    }
}
