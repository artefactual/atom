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

class QubitSettingsFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $cache = QubitCache::getInstance();
    $cacheKey = 'settings:i18n:'.sfContext::getInstance()->getUser()->getCulture();

    // Get settings (from cache if exists)
    if ($cache->has($cacheKey))
    {
      $settings = unserialize($cache->get($cacheKey));
    }
    else
    {
      $settings = QubitSetting::getSettingsArray();

      $cache->set($cacheKey, serialize($settings));
    }

    // Check environment vairables and overwrite/populate settings
    $envHashmap  = array('ATOM_READ_ONLY' => 'boolean');
    foreach ($envHashmap as $env => $type)
    {
      if (false === $value = getenv($env))
      {
        continue;
      }

      switch ($type)
      {
        case 'boolean':
          $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

          break;
      }

      $key = strtolower(str_replace('ATOM', 'app', $env));
      $settings[$key] = $value;
    }

    // Overwrite/populate settings into sfConfig object
    sfConfig::add($settings);

    // Execute next filter
    $filterChain->execute();
  }
}
