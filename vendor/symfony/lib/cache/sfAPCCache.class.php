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

    if (!function_exists('apcu_store') || !ini_get('apc.enabled'))
    {
      throw new sfInitializationException('You must have APCu installed and enabled to use sfAPCCache class.');
    }

    // APCu compatibility issues (solved in v4.0.7).
    // Related links:
    // - https://github.com/krakjoe/apcu/issues/41
    // - https://github.com/krakjoe/apcu/commit/66e5883074348b69957aaf82564fc8136fd3561a.
    // - https://projects.artefactual.com/issues/7850
    // - https://projects.artefactual.com/issues/10064
    $this->infoKey = 'info';
    $this->createTimeKey = 'creation_time';
    $this->modTimeKey = 'modification_time';
    if (extension_loaded('apcu') && version_compare(phpversion('apc'), '4.0.7') === -1)
    {
      $this->infoKey = 'key';
      $this->createTimeKey = 'ctime';
      $this->modTimeKey = 'mtime';
    }
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
    $value = apcu_fetch($key, $has);
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
    return apcu_store($this->getOption('prefix').$key, $data, $this->getLifetime($lifetime));
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    return apcu_delete($this->getOption('prefix').$key);
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    if (sfCache::ALL === $mode)
    {
      return apcu_clear_cache();
    }
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if ($info = $this->getCacheInfo($key))
    {
      return $info[$this->createTimeKey] + $info['ttl'] > time() ? $info[$this->modTimeKey] : 0;
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
      return $info[$this->createTimeKey] + $info['ttl'] > time() ? $info[$this->createTimeKey] + $info['ttl'] : 0;
    }

    return 0;
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
      if (preg_match($regexp, $info[$this->infoKey]))
      {
        apcu_delete($info[$this->infoKey]);
      }
    }
  }

  protected function getCacheInfo($key)
  {
    $infos = apcu_cache_info();

    if (is_array($infos['cache_list']))
    {
      foreach ($infos['cache_list'] as $info)
      {
        if ($this->getOption('prefix').$key == $info[$this->infoKey])
        {
          return $info;
        }
      }
    }

    return null;
  }
}
