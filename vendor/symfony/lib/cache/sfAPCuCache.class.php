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
 * Cache class that stores cached content in APCu. The PHP API should be the
 * comparing it with APC but there is actually an issue in apcu_cache_info. The
 * error seems to be solved now in the official repo but it has not been
 * packaged yet.
 *
 * @package    symfony
 * @subpackage cache
 */
class sfAPCuCache extends sfAPCCache
{
  public function initialize($options = array())
  {
    parent::initialize($options);

    if (!extension_loaded('apcu') || !ini_get('apc.enabled'))
    {
      throw new sfInitializationException('You must have APCu installed and enabled to use sfAPCuCache class.');
    }
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $infos = apcu_cache_info();
    if (!is_array($infos['cache_list']))
    {
      return;
    }

    $regexp = self::patternToRegexp($this->getOption('prefix').$pattern);

    foreach ($infos['cache_list'] as $info)
    {
      if (preg_match($regexp, $info['key']))
      {
        apc_delete($info['key']);
      }
    }
  }

  public function getCacheInfo($key)
  {
    $infos = apcu_cache_info();

    if (is_array($infos['cache_list']))
    {
      foreach ($infos['cache_list'] as $info)
      {
        if ($this->getOption('prefix').$key == $info['key'])
        {
          return $info;
        }
      }
    }

    return null;
  }
}
