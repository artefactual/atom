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

class qubitConfiguration extends sfApplicationConfiguration
{
    // Required format: x.y.z
    public const VERSION = '2.9.0';

    public function listenToChangeCultureEvent(sfEvent $event)
    {
        setcookie('atom_culture', $event['culture'], ['path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'strict']);
    }

    /**
     * @see sfApplicationConfiguration
     */
    public function configure()
    {
        $this->dispatcher->connect('access_log.view', ['QubitAccessLogObserver', 'view']);

        $this->dispatcher->connect('user.change_culture', [$this, 'listenToChangeCultureEvent']);
    }

    /**
     * @see sfApplicationConfiguration
     */
    public function initialize()
    {
        if (false !== $readOnly = getenv('ATOM_READ_ONLY')) {
            sfConfig::set('app_read_only', filter_var($readOnly, FILTER_VALIDATE_BOOLEAN));
        }

        // Force escaping
        sfConfig::set('sf_escaping_strategy', true);
    }

    /**
     * @see sfApplicationConfiguration
     *
     * @param mixed $moduleName
     */
    public function getControllerDirs($moduleName)
    {
        if (!isset($this->cache['getControllerDirs'][$moduleName])) {
            $this->cache['getControllerDirs'][$moduleName] = [];

            // HACK Currently plugins only override application templates, not the
            // other way around
            foreach ($this->getPluginSubPaths('/modules/'.$moduleName.'/actions') as $dir) {
                $this->cache['getControllerDirs'][$moduleName][$dir] = false; // plugins
            }

            $this->cache['getControllerDirs'][$moduleName][sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/actions'] = false; // application
        }

        return $this->cache['getControllerDirs'][$moduleName];
    }

    /**
     * @see sfApplicationConfiguration
     */
    public function getDecoratorDirs()
    {
        $dirs = sfConfig::get('sf_decorator_dirs');
        $dirs[] = sfConfig::get('sf_app_template_dir');

        return $dirs;
    }

    /**
     * @see sfApplicationConfiguration
     *
     * @param mixed $moduleName
     */
    public function getTemplateDirs($moduleName)
    {
        // HACK Currently plugins only override application templates, not the
        // other way around
        $dirs = $this->getPluginSubPaths('/modules/'.$moduleName.'/templates');
        $dirs[] = sfConfig::get('sf_app_module_dir').'/'.$moduleName.'/templates';

        return array_merge($dirs, $this->getDecoratorDirs());
    }

    /**
     * @see sfProjectConfiguration
     *
     * @param mixed $path
     */
    public function setRootDir($path)
    {
        parent::setRootDir($path);

        $this->setWebDir($path);
    }

    /**
     * Get a config variable from an application config file (YAML) for a specific
     * environment (e.g. "prod", "dev", "cli").
     *
     * N.B. to get a config variable for the current context/environment, use
     * sfConfing::get() instead!
     *
     * @param string $varname    config variable name
     * @param string $env        Environment name (e.g. 'prod', 'cli')
     * @param string $configFile config file to check (e.g. 'config/settings.yml')
     *
     * @return null|string config value or null if variable is not set
     */
    public static function getConfigForEnvironment($varname, $env, $configFile)
    {
        // Parse the YAML data to an array
        $config = sfSimpleYamlConfigHandler::getConfiguration(
            sfContext::getInstance()
                ->getConfiguration()
                ->getConfigPaths($configFile)
        );

        // The settings.yml file requires an intermediate '.settings' key for
        // some reason :-/
        if (
            'config/settings.yml' == $configFile
            && isset($config[$env]['.settings'][$varname])
        ) {
            return $config[$env]['.settings'][$varname];
        }

        // Get a value from non "settings.yml" config files
        if (isset($config[$env][$varname])) {
            return $config[$env][$varname];
        }

        // Return null if the variable is not set in the specified config file
        return null;
    }
}
