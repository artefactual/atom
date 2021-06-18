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

class arDominionB5PluginConfiguration extends sfPluginConfiguration
{
    public static $summary = 'Theme plugin made with Bootstrap 5.';
    public static $version = '0.0.1';

    public function initialize()
    {
        $decoratorDirs = sfConfig::get('sf_decorator_dirs');
        $decoratorDirs[] = $this->rootDir.'/templates';
        sfConfig::set('sf_decorator_dirs', $decoratorDirs);

        $moduleDirs = sfConfig::get('sf_module_dirs');
        $moduleDirs[$this->rootDir.'/modules'] = false;
        sfConfig::set('sf_module_dirs', $moduleDirs);

        // Move this plugin to the top to allow overwriting
        // controllers and views from other plugin modules.
        $plugins = $this->configuration->getPlugins();
        unset($plugins[array_search($this->name, $plugins)]);
        $this->configuration->setPlugins(array_values([$this->name] + $plugins));
    }
}
