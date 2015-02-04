<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Cache class that stores cached content in APC.
 *
 * @package    symfony
 * @subpackage cache
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfAPCCache.class.php 21990 2009-09-13 21:09:18Z FabianLange $
 */
class sfAPCCache extends sfCache
{
  /**
   * Initializes this sfCache instance.
   *
   * Available options:
   *
   * * see sfCache for options available for all drivers
   *
   * @see sfCache
   */
  public function initialize($options = array())
  {
    parent::initialize($options);

    if (!function_exists('apc_store') || !ini_get('apc.enabled'))
    {
      throw new sfInitializationException('You must have APC installed and enabled to use sfAPCCache class.');
    }

    // The implementation of removePattern() and getCacheInfo() differs between
    // sfAPCCache and sfAPCuCache, see https://github.com/krakjoe/apcu/issues/41
    // See also ticket #7850.
    $this->usingAPCu = extension_loaded('apcu');
  }

 /**
  * @see sfCache
  */
  public function get($key, $default = null)
  {
    $value = $this->fetch($this->getOption('prefix').$key, $has);
    return $has ? $value : $default;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    $this->fetch($this->getOption('prefix').$key, $has);
    return $has;
  }

  private function fetch($key, &$success)
  {
    $has = null;
    $value = apc_fetch($key, $has);
    // the second argument was added in APC 3.0.17. If it is still null we fall back to the value returned
    if (null !== $has)
    {
      $success = $has;
    }
    else
    {
      $success = $value !== false;
    }
    return $value;
  }
  
  
  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    return apc_store($this->getOption('prefix').$key, $data, $this->getLifetime($lifetime));
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return apc_delete($this->getOption('prefix').$key);
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      return apc_clear_cache('user');
    }
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['creation_time'] + $info['ttl'] > time() ? $info['mtime'] : 0;
    }

    return 0;
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info['creation_time'] + $info['ttl'] > time() ? $info['creation_time'] + $info['ttl'] : 0;
    }

    return 0;
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    $infoKey = $this->usingAPCu ? 'key' : 'info';
    $infos = apc_cache_info('user');

    if (!is_array($infos['cache_list']))
    {
      return;
    }

    $regexp = self::patternToRegexp($this->getOption('prefix').$pattern);

    foreach ($infos['cache_list'] as $info)
    {
      if (preg_match($regexp, $info[$infoKey]))
      {
        apc_delete($info[$infoKey]);
      }
    }
  }

  protected function getCacheInfo($key)
  {
    $infoKey = $this->usingAPCu ? 'key' : 'info';
    $infos = apc_cache_info('user');

    if (is_array($infos['cache_list']))
    {
      foreach ($infos['cache_list'] as $info)
      {
        if ($this->getOption('prefix').$key == $info[$infoKey])
        {
          return $info;
        }
      }
    }

    return null;
  }
}
