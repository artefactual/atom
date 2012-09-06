<?php

/**
 * Gearman worker
 *
 * @uses GearmanWorker
 * @package   sfGearmanPlugin
 * @author    Benjamin VIELLARD <bicou@bicou.com>
 * @license   The MIT License
 * @version   SVN: $Id: sfGearmanWorker.class.php 32980 2011-09-02 08:07:40Z bicou $
 */
class sfGearmanWorker extends GearmanWorker
{
  /**
   * Parameters
   *
   * @var array
   * @access protected
   */
  protected $parameters = array();

  /**
   * Event dispatcher
   *
   * @var sfEventDispatcher
   * @access protected
   */
  protected $dispatcher = null;

  /**
   * __construct
   *
   * @param array             $parameters Parameters
   * @param sfEventDispatcher $dispatcher Event dispatcher
   */
  public function __construct($parameters = array(), sfEventDispatcher $dispatcher = null)
  {
    parent::__construct();

    $this->parameters = $parameters;
    $this->dispatcher = $dispatcher;

    $this->addOptions(GEARMAN_WORKER_GRAB_UNIQ);

    sfGearman::setupConnection($this, $this->getParameter('server'));

    $this->configure();
  }

  /**
   * Configure worker
   *
   * @param array $parameters Parameters
   * @access public
   * @return void
   */
  public function configure()
  {
    if (($config = $this->getParameter('config')) !== null)
    {
      // use config from gearman.yml worker
      foreach (sfGearman::getWorker($config) as $function => $callback)
      {
        $this->addFunction($function, $callback);
      }
    }
  }

  /**
   * Get a parameter
   *
   * @param mixed $name    Name
   * @param mixed $default Default value
   * @access public
   * @return mixed Parameter value
   */
  public function getParameter($name, $default = null)
  {
    return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
  }

  /**
   * Register and add callback function
   *
   * @param mixed $function Name of a gearman function
   * @param mixed $callback Callback called when a job is submitted
   * @param mixed $context  Reference to data
   * @param int $timeout    Interval of time in seconds
   * @access public
   * @return boolean
   */
  public function addFunction($function, $callback, $context = null, $timeout = 0)
  {
    // worker add function event
    if ($this->dispatcher !== null)
    {
      $this->dispatcher->notify(new sfEvent($this, 'gearman.add_function', array('function' => $function)));
    }

    // if no reference set, attach the worker object
    if ($context === null)
    {
      $context = $this;
    }

    if (!is_callable($callback))
    {
      throw new sfGearmanException(sprintf('Not found: %s (disabled plugin?)', implode($callback, '::')));
    }

    return parent::addFunction($function, $callback, $context, $timeout);
  }

  /**
   * Work a worker
   *
   * @param integer       $count   Number of jobs for worker to run before breaking, optional, defaults to 0.
   * @param integer       $timeout Socket I/O activity timeout (in ms), optional, defaults to -1.
   *
   * @return void
   */
  public function loop($count = null, $timeout = null)
  {
    // if no count specified, loop for ever
    if ($count === null)
    {
      $count = $this->getParameter('count', 0);
    }
    $iteration = 0;

    // if no timeout specified, wait for ever
    if ($timeout === null)
    {
      $timeout = $this->getParameter('timeout', -1);
    }
    $this->setTimeout($timeout);

    // worker start event
    if ($this->dispatcher !== null)
    {
      $this->dispatcher->notify(new sfEvent($this, 'gearman.start'));
    }

    do
    {
      $iteration++;

      @$this->work();

      $code = $this->returnCode();

      // exception if timeout is set
      if ($code == GEARMAN_TIMEOUT and $timeout >= 0)
      {
        if ($this->dispatcher !== null)
        {
          $this->dispatcher->notify(new sfEvent($this, 'gearman.timeout'));
        }

        throw new sfGearmanTimeoutException(sprintf('Worker timeout "%s" (code: %d)', $this->error(), $code));
      }

      // exception if not successfull
      if ($code != GEARMAN_SUCCESS)
      {
        throw new sfGearmanException(sprintf('Worker error "%s" (code: %d)', $this->error(), $code));
      }
    }
    // break after n workloads (because of php memory)
    while(!$count or $iteration < $count);

    // worker stop event
    if ($this->dispatcher !== null)
    {
      $this->dispatcher->notify(new sfEvent($this, 'gearman.stop'));
    }
  }

  /**
   * Notify job event
   *
   * @param GearmanJob $job Gearman job
   * @access public
   * @return void
   */
  public function notifyEventJob(GearmanJob $job)
  {
    if ($this->dispatcher !== null)
    {
      $this->dispatcher->notify(new sfEvent($this, 'gearman.job', array('job' => $job)));
    }
  }
}

