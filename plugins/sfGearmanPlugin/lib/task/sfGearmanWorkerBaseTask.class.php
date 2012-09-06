<?php

/**
 * Base task for gearman tasks
 *
 * @package     sfGearmanPlugin
 * @subpackage  task
 * @author      Benjamin VIELLARD <bicou@bicou.com>
 * @license     The MIT License
 * @version     SVN: $Id: sfGearmanWorkerBaseTask.class.php 29482 2010-05-16 17:11:45Z bicou $
 */
abstract class sfGearmanWorkerBaseTask extends sfBaseTask
{
  /**
   * Command options
   *
   * @var array
   * @access protected
   */
  protected $command_options = array();

  /**
   * Connect event dispatcher to gearman events
   *
   * @access protected
   * @return void
   */
  protected function connectGearmanEvents()
  {
    $this->dispatcher->connect('gearman.add_function', array($this, 'workerAddFunction'));
    $this->dispatcher->connect('gearman.start', array($this, 'workerStart'));
    $this->dispatcher->connect('gearman.stop', array($this, 'workerStop'));
    $this->dispatcher->connect('gearman.timeout', array($this, 'workerTimeout'));
    $this->dispatcher->connect('gearman.job', array($this, 'workerJob'));
  }

  /**
   * log adding functions
   *
   * @param sfEvent $event Event
   * @access public
   * @return void
   */
  public function workerAddFunction(sfEvent $event)
  {
    if ($this->command_options['verbose'])
    {
      $this->logSection('register', $event['function']);
    }
  }

  /**
   * log starting worker
   *
   * @param sfEvent $event Event
   * @access public
   * @return void
   */
  public function workerStart(sfEvent $event)
  {
    if ($this->command_options['verbose'])
    {
      $this->logSection('worker', 'start');
    }
  }

  /**
   * log stopping worker
   *
   * @param sfEvent $event Event
   * @access public
   * @return void
   */
  public function workerStop(sfEvent $event)
  {
    if ($this->command_options['verbose'])
    {
      $this->logSection('worker', 'stop');
    }
  }

  /**
   * log worker timeout
   *
   * @param sfEvent $event Event
   * @access public
   * @return void
   */
  public function workerTimeout(sfEvent $event)
  {
    if ($this->command_options['verbose'])
    {
      $this->logSection('worker', 'timeout');
    }
  }

  /**
   * log worker job
   *
   * @param sfEvent $event Event
   * @access public
   * @return void
   */
  public function workerJob(sfEvent $event)
  {
    $job = $event['job'];

    $this->logSection('job', $job->handle());
    $this->logSection('function', $job->functionName());

    if ($this->command_options['verbose'])
    {
      $this->logSection('unique', $job->unique());
      $this->log($job->workload());
    }
  }
}

