<?php

/**
 * Gearman client
 *
 * @uses      GearmanClient
 * @package   sfGearmanPlugin
 * @author    Benjamin VIELLARD <bicou@bicou.com>
 * @license   The MIT License
 * @version   SVN: $Id: sfGearmanClient.class.php 32721 2011-07-04 09:42:04Z bicou $
 */
class sfGearmanClient extends GearmanClient
{
  /**
   * Array of instances
   *
   * @var array  Defaults to array().
   */
  private static $instances = array();

  /**
   * Constructor
   *
   * @param string $server Optional, defaults to null.
   */
  public function __construct($server = null)
  {
    parent::__construct();

    sfGearman::setupConnection($this, $server);
  }

  /**
   * Get a singleton by server key config
   *
   * @param string $server Optional, defaults to null.
   *
   * @return sfGearmanClient
   */
  public static function getInstance($server = null)
  {
    $key = $server !== null ? $server : 'default';

    if (!isset(self::$instances[$key]))
    {
      self::$instances[$key] = new self($server);
    }

    return self::$instances[$key];
  }

  /**
   * Run a gearman task and return result
   *
   * @param string  $function Function name
   * @param mixed   $workload Function workload, optional, defaults to ''.
   * @param integer $options  Task options, optional, defaults to null.
   *
   * @return string Gearman result of task
   */
  public function task($function, $workload = '', $options = null)
  {
    // gearman module method
    $method = 'do';

    // priority
    switch (true)
    {
      case $options & sfGearman::HIGH: $method .= 'High'; break;
      case $options & sfGearman::LOW:  $method .= 'Low';  break;
    }

    // asynchronous
    if ($options & sfGearman::BACKGROUND)
    {
      $method .= 'Background';
    }

    // serialize workload because gearman handle strings only
    $workload = sfGearman::serialize($workload);

    do
    {
      // call gearman module method
      $result = @call_user_func(array($this, $method), $function, $workload);

      // check return code
      switch ($this->returnCode())
      {
      case GEARMAN_SUCCESS:
        return sfGearman::unserialize($result);

      case GEARMAN_WORK_DATA:
        // TODO send data somewhere
        $data = $result;
        break;

      case GEARMAN_WORK_STATUS:
        list ($numerator, $denominator) = $this->doStatus();
        trigger_error(sprintf('gearman status [%s]: %d/%d', $this->doJobHandle(), $numerator, $denominator), E_USER_NOTICE);
        break;

      case GEARMAN_WORK_WARNING:
        trigger_error(sprintf('gearman: %s', $result), E_USER_WARNING);
        break;

      case GEARMAN_WORK_FAIL:
        throw new sfGearmanException(sprintf('Worker failed "%s" (function: "%s")', $this->doJobHandle(), $function));

      default:
        // error processing
        throw new sfGearmanException(sprintf('Client error "%s" (code: %d) (ret: %d) (function: "%s")', $this->error(), $this->getErrno(), $this->returnCode(), $function));
      }
    }
    while ($this->returnCode() != GEARMAN_SUCCESS);
  }

  /**
   * Run a gearman background task and return result
   *
   * @param string  $function Function name
   * @param mixed   $workload Function workload, optional, defaults to ''.
   * @param integer $options  Task options, optional, defaults to null.
   *
   * @return string Gearman result of task
   */
  public function background($function, $workload = '', $options = null)
  {
    return $this->task($function, $workload, $options | sfGearman::BACKGROUND);
  }
}

