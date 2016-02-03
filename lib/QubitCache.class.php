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

class QubitCache
{
  protected static $instance = null;

  public static function getInstance(array $options = array())
  {
    if (!isset(self::$instance))
    {
      if (null === $cacheClass = sfConfig::get('app_cache_engine'))
      {
        $cacheClass = sfConfig::get('app_cache_engine_class', 'sfAPCCache');
      }

      $options = array();

      if (null !== $setting = sfConfig::get('app_cache_engine_param_prefix'))
      {
        $options['prefix'] = $setting;
      }

      if (null !== $setting = sfConfig::get('app_cache_engine_param_lifetime'))
      {
        $options['lifetime'] = (float)$setting;
      }

      if ($cacheClass === 'sfMemcacheCache')
      {
        if (null !== $setting = sfConfig::get('app_cache_engine_param_servers'))
        {
          $servers = array();
          foreach ($setting as $item)
          {
            $servers[] = array(
              'host' => $item[0]['host'],
              'port' => $item[1]['port']
            );
          }

          $options['servers'] = $servers;
        }
        else if ((null !== $host = sfConfig::get('app_cache_engine_param_host')) && (null !== $port = sfConfig::get('app_cache_engine_param_port')))
        {
          $options['host'] = $host;
          $options['port'] = $port;
        }

        if (null !== $setting = sfConfig::get('app_cache_engine_param_storeCacheInfo'))
        {
          $options['storeCacheInfo'] = (bool)$setting;
        }

        if (null !== $setting = sfConfig::get('app_cache_engine_param_persistent'))
        {
          $options['persistent'] = (bool)$setting;
        }
      }

      self::$instance = new $cacheClass($options);
    }

    return self::$instance;
  }

  public static function getLabel($id, $className, $ttl = 3600)
  {
    // I should make this a property of the class instead
    $cache = self::getInstance();

    // Cache key
    $cacheKey = sprintf('label:%s:%s',
      $id,
      sfContext::getInstance()->user->getCulture());

    if ($cache->has($cacheKey))
    {
      $label = $cache->get($cacheKey);
    }
    else
    {
      // Avoid caching non-existing records
      if (null === $object = $className::getById($id))
      {
        return;
      }

      $label = $className::getById($id)->__toString();

      $cache->set($cacheKey, $label, $ttl);
    }

    return $label;
  }
}
