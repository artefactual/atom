<?php

/**
 * Gearman worker for MySQL triggers
 *
 * @package     sfGearmanPlugin
 * @subpackage  task
 * @author      Benjamin VIELLARD <bicou@bicou.com>
 * @license     The MIT License
 * @version     SVN: $Id: gearmanWorkertriggerTask.class.php 29482 2010-05-16 17:11:45Z bicou $
 */
class gearmanWorkertriggerTask extends sfGearmanWorkerBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
      new sfCommandOption('server', null, sfCommandOption::PARAMETER_OPTIONAL, 'Gearman job server config key'),
      new sfCommandOption('queue', null, sfCommandOption::PARAMETER_REQUIRED, 'Gearman function name used by queue', '__mysql_trigger'),
      new sfCommandOption('count', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of jobs for worker to run before exiting', 100),
      new sfCommandOption('timeout', null, sfCommandOption::PARAMETER_REQUIRED, 'Timeout in seconds', -1),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Log workloads and additional events'),
    ));

    $this->namespace        = 'gearman';
    $this->name             = 'worker-trigger';
    $this->briefDescription = 'Gearman worker daemon for database jobs';
    $this->detailedDescription = <<<EOF
The [gearman:worker-trigger|INFO] process MySQL jobs received from triggers
Call it with:

  [php symfony gearman:worker-trigger|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->command_options = $options;

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // connect to gearman events
    $this->connectGearmanEvents();

    // s => ms
    $options['timeout'] *= 1000;

    // get a worker
    $worker = new sfGearmanWorker($options, $this->dispatcher);
    $worker->addFunction($options['queue'], array($this, 'work'));

    try
    {
      $worker->loop();
    }
    catch(sfGearmanTimeoutException $e)
    {
    }
  }

  /**
   * Gearman work for mysql triggers
   *
   * @param GearmanJob      $job    Gearman job
   * @param sfGearmanWorker $worker Gearman worker
   * @return string
   */
  public function work($job, $worker)
  {
    $worker->notifyEventJob($job);

    // workload contains XML from MySQL server UDF's
    $workload = $job->workload();

    // parse workload
    libxml_use_internal_errors(true);
    $trigger = simplexml_load_string($workload);
    if ($trigger === false)
    {
      $job->sendWarning('Failed loading XML workload');
      foreach (libxml_get_errors() as $error)
      {
        $job->sendWarning(trim($error->message));
      }
      $job->sendFail();
      return;
    }

    // extract params from xml
    $event    = (string)$trigger['event'];
    $model    = (string)$trigger['model'];
    $columns  = array();
    $modified = array();
    foreach ($trigger->columns->children() as $column)
    {
      $columns[$column->getName()] = (string)$column;
      if (isset($column['modified']) and $column['modified'] == 1)
      {
        $modified[] = $column->getName();
      }
    }

    $this->logSection('event', $model.'.'.$event);

    // ensure model exists
    if (!class_exists($model))
    {
      $job->sendWarning(sprintf('Model class "%s" not found', $model));
      $job->sendFail();
      return;
    }

    // create a proxy object with data from columns
    $record = Doctrine::getTable($model)->create($columns);
    $record->state(Doctrine_Record::STATE_PROXY);

    // resend a task to gearman job server from event àla Doctrine
    $result = $record->taskBackground('trigger'.ucfirst(strtolower($event)), $modified);

    // don't need object anymore
    $record->free(true);

    return $result;
  }
}


