<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Purge Qubit data.
 */
class atomPluginsTask extends sfBaseTask
{
    public function execute($arguments = [], $options = [])
    {
        $databaseManager = new sfDatabaseManager($this->configuration);
        $conn = $databaseManager->getDatabase('propel')->getConnection();

        // Retrieve QubitSetting object
        $criteria = new Criteria();
        $criteria->add(QubitSetting::NAME, 'plugins');
        if (null === $setting = QubitSetting::getOne($criteria)) {
            throw new sfException('Database entry could not be found.');
        }

        // Array of plugins
        $plugins = array_values(unserialize($setting->getValue(['sourceCulture' => true])));

        if (in_array($arguments['action'], ['add', 'delete']) && !isset($arguments['plugin'])) {
            throw new sfException('Missing plugin name.');
        }

        switch ($arguments['action']) {
            case 'add':
                $plugins[] = $arguments['plugin'];
                $this->savePlugins($setting, $plugins);

                break;

            case 'delete':
                if (false !== $this->searchPlugins($arguments['plugin'], $plugins)) {
                    unset($plugins[array_search($arguments['plugin'], $plugins)]);
                } else {
                    throw new sfException('Plugin could not be found.');
                }

                $this->savePlugins($setting, $plugins);

                break;

            case 'set-theme':
                $themePlugins = [];
                $configuration = ProjectConfiguration::getActive();
                $pluginPaths = $configuration->getAllPluginPaths();

                // Get list of theme plugins
                foreach (sfPluginAdminPluginConfiguration::$pluginNames as $name) {
                    unset($pluginPaths[$name]);
                }

                foreach ($pluginPaths as $name => $path) {
                    $className = $name.'Configuration';
                    if (sfConfig::get('sf_plugins_dir') == substr($path, 0, strlen(sfConfig::get('sf_plugins_dir'))) && is_readable($classPath = $path.'/config/'.$className.'.class.php')) {
                        $this->installPluginAssets($name, $path);

                        require_once $classPath;

                        $class = new $className($configuration);

                        // Build a list of themes
                        if (isset($class::$summary) && 1 === preg_match('/theme/i', $class::$summary)) {
                            $themePlugins[] = $name;
                        }
                    }
                }

                // Get current theme plugin
                $currentTheme = null;
                $criteria = new Criteria();
                $criteria->add(QubitSetting::NAME, 'plugins');
                if (1 == count($query = QubitSetting::get($criteria))) {
                    $setting = $query[0];

                    foreach (unserialize($setting->getValue(['sourceCulture' => true])) as $plugin) {
                        if (in_array($plugin, $themePlugins)) {
                            $currentTheme = $plugin;

                            break;
                        }
                    }
                }

                // Check if the new plugin is a theme plugin
                if (in_array($arguments['plugin'], $themePlugins)) {
                    // Delete current theme plugin
                    if (false !== $this->searchPlugins($currentTheme, $plugins)) {
                        unset($plugins[array_search($currentTheme, $plugins)]);
                    }

                    // Add new theme plugin
                    $plugins[] = $arguments['plugin'];

                    // Save new plugins array
                    $this->savePlugins($setting, $plugins);
                } else {
                    throw new sfException(sprintf('%s is not a theme plugin.', $arguments['plugin']));
                }

                break;

            case 'list':
                foreach ($plugins as $plugin) {
                    echo $plugin."\n";
                }

                break;

            default:
                throw new sfException('Missing action');
        }
    }

    // Copied from sfPluginPublishAssetsTask
    protected function installPluginAssets($name, $path)
    {
        $webDir = $path.'/web';

        if (is_dir($webDir)) {
            $filesystem = new sfFilesystem();
            $filesystem->relativeSymlink($webDir, sfConfig::get('sf_web_dir').'/'.$name, true);
        }
    }

    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('action', sfCommandArgument::REQUIRED, 'The action (add, delete, set-theme or list).'),
            new sfCommandArgument('plugin', sfCommandArgument::OPTIONAL, 'The plugin name.'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('action', null, sfCommandOption::PARAMETER_REQUIRED, 'Desired action'),
        ]);

        $this->namespace = 'tools';
        $this->name = 'atom-plugins';
        $this->briefDescription = 'Manage AtoM plugins.';

        $this->detailedDescription = <<<'EOF'
Manage AtoM plugins stored in the database. Examples:
 - symfony atom-plugins add arFoobarPlugin
 - symfony atom-plugins delete arFoobarPlugin
 - symfony atom-plugins set-theme arFoobarPlugin
 - symfony atom-plugins list
EOF;
    }

    private function searchPlugins($plugin, &$plugins)
    {
        $key = array_search($plugin, $plugins);

        return $key;
    }

    private function savePlugins($setting, &$plugins)
    {
        $setting->setValue(serialize(array_unique($plugins)), ['sourceCulture' => true]);
        $setting->save();
    }
}
