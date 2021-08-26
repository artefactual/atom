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

require_once sfConfig::get('sf_plugins_dir')
    .'/arDominionB5Plugin/config/arDominionB5PluginConfiguration.class.php';

class arUnogPluginConfiguration extends arDominionB5PluginConfiguration
{
    public static $summary = 'Theme plugin extending arDominionB5Plugin.';
    public static $version = '0.0.1';

    public function initialize()
    {
        parent::initialize();

        // Add this plugin templates before arDominionB5Plugin
        sfConfig::set('sf_decorator_dirs', array_merge(
            [$this->rootDir.'/templates'],
            sfConfig::get('sf_decorator_dirs')
        ));

        // Move this plugin to the top to allow overwriting
        // controllers and views from other plugin modules.
        $plugins = $this->configuration->getPlugins();
        if (false !== $key = array_search($this->name, $plugins)) {
            unset($plugins[$key]);
        }
        $this->configuration->setPlugins(array_merge([$this->name], $plugins));
    }
}
