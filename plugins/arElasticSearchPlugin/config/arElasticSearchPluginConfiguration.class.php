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

class arElasticSearchPluginConfiguration extends sfPluginConfiguration
{
  public static
    $summary = 'Search index plugin. Uses an ElasticSearch instance to provide advanced search features such as aggregations, fuzzy search, etc.',
    $version = '1.0.0',

    $configPath = 'config/search.yml',
    $config = null,

    $mappingPath = 'config/mapping.yml',
    $mapping = null;

  public function initialize()
  {
    if (!extension_loaded('curl'))
    {
      throw new sfInitializationException('arElasticSearchPlugin needs cURL PHP extension');
    }

    if ($this->configuration instanceof sfApplicationConfiguration)
    {
      // Use config cache in application context
      $configCache = $this->configuration->getConfigCache();
      $configCache->registerConfigHandler(self::$configPath, 'arElasticSearchConfigHandler');

      self::$config = include $configCache->checkConfig(self::$configPath);
    }
    else
    {
      // Live parsing (task context)
      $configPaths = $this->configuration->getConfigPaths(self::$configPath);

      self::$config = arElasticSearchConfigHandler::getConfiguration($configPaths);
    }
  }
}
