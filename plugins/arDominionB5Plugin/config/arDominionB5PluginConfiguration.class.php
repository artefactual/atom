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
        // Avoid $this->name and $this->rootDir to use this class
        // values when this method is called from child classes.
        $decoratorDirs = sfConfig::get('sf_decorator_dirs');
        $decoratorDirs[] = sfConfig::get('sf_plugins_dir')
            .'/arDominionB5Plugin/templates';
        sfConfig::set('sf_decorator_dirs', $decoratorDirs);

        // Move this plugin to the top to allow overwriting
        // controllers and views from other plugin modules.
        $plugins = $this->configuration->getPlugins();
        if (false !== $key = array_search('arDominionB5Plugin', $plugins)) {
            unset($plugins[$key]);
        }
        $this->configuration->setPlugins(
            array_merge(['arDominionB5Plugin'], $plugins)
        );

        // Indicate this is a Bootstrap 5 theme in sfConfig,
        // used to render with different classes, etc.
        sfConfig::set('app_b5_theme', true);
    }
}
