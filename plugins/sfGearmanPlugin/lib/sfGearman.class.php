<?php

/**
 * sfGearman util class
 *
 * @abstract
 * @package   sfGearmanPlugin
 * @author    Benjamin VIELLARD <bicou@bicou.com>
 * @license   The MIT License
 * @version   SVN: $Id: sfGearman.class.php 32721 2011-07-04 09:42:04Z bicou $
 */
abstract class sfGearman
{
  /**
   * asynchronous tasks
   *
   * @var integer
   */
  const BACKGROUND = 1;

  /**
   * low priority tasks
   *
   * @var integer
   */
  const LOW = 2;

  /**
   * high priority tasks
   *
   * @var integer
   */
  const HIGH = 4;

  /**
   * configuration array read from gearman.yml
   *
   * @var mixed  Defaults to null.
   */
  public static $config = null;


  /**
   * worker configuration array by key name
   *
   * @param string $name gearman.yml key
   *
   * @return array
   */
  public static function getWorker($name)
  {
    if (isset(self::$config['worker'][$name]))
    {
      return self::$config['worker'][$name];
    }

    throw new sfConfigurationException(sprintf('sfGearmanPlugin worker config "%s" not found', $name));
  }

  /**
   * doctrine worker configuration array by key name
   *
   * @param string $name gearman.yml key
   *
   * @return array
   */
  public static function getDoctrine($name)
  {
    if (isset(self::$config['doctrine'][$name]))
    {
      return self::$config['doctrine'][$name];
    }

    throw new sfConfigurationException(sprintf('sfGearmanPlugin doctrine worker config "%s" not found', $name));
  }

  /**
   * Configure a gearman connection with parameters from gearman.yml
   *
   * @param sfGearmanWorker|sfGearmanClient $connection
   * @param string              $server Optional, defaults to null.
   *
   * @return void
   */
  public static function setupConnection($connection, $server = null)
  {
    self::addServer($connection, self::getServer($server));
  }

  /**
   * server configuration array by key name
   *
   * @param string $name gearman.yml key, optional, defaults to null
   *
   * @return array
   */
  public static function getServer($name = null)
  {
    // transform key to 'default' for array
    $key = $name !== null ? $name : 'default';

    if (isset(self::$config['server'][$key]))
    {
      return self::$config['server'][$key];
    }

    // not found but still null, return null (module defaults)
    if ($name === null) return null;

    throw new sfConfigurationException(sprintf('sfGearmanPlugin server config "%s" not found', $name));
  }

  /**
   * Add servers to GearmanClient or GearmanWorker
   *
   * @param sfGearmanWorker|sfGearmanClient $connection
   * @param mixed               $params
   *
   * @return void
   */
  public static function addServer($connection, $params)
  {
    if ($params === null)
    {
      // module defaults
      $connection->addServer();
    }
    elseif (is_string($params))
    {
      // "host:port"
      $connection->addServers($params);
    }
    elseif (is_array($params))
    {
      if (isset($params['host']))
      {
        if (isset($params['port']))
        {
          // {host: ..., port: ...}
          $connection->addServer($params['host'], $params['port']);
        }
        else
        {
          // {host: ...}
          $connection->addServer($params['host']);
        }
      }
      else
      {
        // array of servers => iterate
        foreach ($params as $param)
        {
          self::addServer($connection, $param);
        }
      }
    }
  }

  /**
   * serialize if needed
   *
   * @param mixed $data
   * @static
   * @access public
   * @return void
   */
  public static function serialize($data)
  {
    return is_scalar($data) ? $data : serialize($data);
  }

  /**
   * unserialize if needed
   *
   * @param mixed $data
   * @static
   * @access public
   * @return void
   */
  public static function unserialize($data)
  {
    $r = @unserialize($data);
    if ($r !== false or $data === 'b:0;')
    {
      return $r;
    }
    return $data;
  }
}

