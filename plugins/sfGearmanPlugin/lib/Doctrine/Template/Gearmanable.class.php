<?php

/**
 * Doctrine_Template_Gearmanable
 *
 * @uses      Doctrine_Template
 * @package   sfGearmanPlugin
 * @author    Benjamin VIELLARD <bicou@bicou.com>
 * @license   The MIT License
 * @version   SVN: $Id: Gearmanable.class.php 29482 2010-05-16 17:11:45Z bicou $
 */
class Doctrine_Template_Gearmanable extends Doctrine_Template
{
  /**
   * GearmanJob in worker context
   *
   * @var GearmanJob  Defaults to null.
   */
  private $_job = null;

  /**
   * options
   *
   * @var array
   * @access protected
   */
  protected $_options = array('trigger' => 'event', 'events' => array());

  /**
   * Set gearman worker job
   *
   * @param GearmanJob $job
   *
   * @return void
   */
  public function setGearmanJob(GearmanJob $job)
  {
    $this->_job = $job;
  }

  /**
   * set gearman worker job
   *
   * @param GearmanJob $job
   *
   * @return void
   */
  public function setGearmanJobTableProxy(GearmanJob $job)
  {
    $this->_job = $job;
  }

  /**
   * retrieve gearman worker job
   *
   * @return GearmanJob
   */
  public function getGearmanJob()
  {
    return $this->_job;
  }

  /**
   * retrieve gearman worker job
   *
   * @return GearmanJob
   */
  public function getGearmanJobTableProxy()
  {
    return $this->_job;
  }

  /**
   * the gearman client
   *
   * @return GearmanClient
   */
  public function getGearmanClient()
  {
    return sfGearmanClient::getInstance($this->getOption('server'));
  }

  /**
   * setTableDefinition
   *
   * @return void
   */
  public function setTableDefinition()
  {
    if ($this->getOption('trigger') == 'event')
    {
      $this->addListener(new Doctrine_Record_Listener_Gearmanable($this));
    }
  }

  /**
   * Send a task to gearman job server
   *
   * @return string
   */
  public function task()
  {
    $arguments = func_get_args();

    return $this->gearman(array_shift($arguments), $arguments);
  }

  /**
   * Send a table task to gearman job server
   *
   * @return string
   */
  public function taskTableProxy()
  {
    $arguments = func_get_args();

    return $this->gearmanTableProxy(array_shift($arguments), $arguments);
  }

  /**
   * Send a background task to gearman job server
   *
   * @return string
   */
  public function taskBackground()
  {
    $arguments = func_get_args();

    return $this->gearman(array_shift($arguments), $arguments, sfGearman::BACKGROUND);
  }

  /**
   * Send a background table task to gearman job server
   *
   * @return string
   */
  public function taskBackgroundTableProxy()
  {
    $arguments = func_get_args();

    return $this->gearmanTableProxy(array_shift($arguments), $arguments, sfGearman::BACKGROUND);
  }

  /**
   * Send a record task to gearman job server
   *
   * @param string  $method    Object method to call in worker
   * @param array   $arguments Method arguments, optional, defaults to array()
   * @param integer $options   Optional, defaults to null.
   *
   * @return string Gearman task result
   */
  public function gearman($method, $arguments = array(), $options = null)
  {
    $workload = array('arguments' => $arguments, 'record' => $this->getInvoker());

    return $this->gearmanTask($method, $workload, $options);
  }

  /**
   * Send a table task to gearman job server
   *
   * @param string  $method    Object method to call in worker
   * @param array   $arguments Method arguments, optional, defaults to array()
   * @param integer $options   Optional, defaults to null.
   *
   * @return string Gearman task result
   */
  public function gearmanTableProxy($method, $arguments = array(), $options = null)
  {
    $workload = array('arguments' => $arguments);

    return $this->gearmanTask($method, $workload, $options);
  }

  /**
   * Send a task to gearman job server
   *
   * @param string  $method   Object method to call in worker
   * @param mixed   $workload Gearman workload
   * @param integer $options  sfGearman options
   *
   * @return string Gearman task result
   */
  private function gearmanTask($method, $workload, $options)
  {
    $function = $this->getInvoker()->getTable()->getComponentName().'.'.$method;

    return $this->getGearmanClient()->task($function, $workload, $options);
  }
}

