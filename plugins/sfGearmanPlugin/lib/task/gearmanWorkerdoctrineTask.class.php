<?php

/**
 * Gearman worker for Doctrine functions
 *
 * @package     sfGearmanPlugin
 * @subpackage  task
 * @author      Benjamin VIELLARD <bicou@bicou.com>
 * @license     The MIT License
 * @version     SVN: $Id: gearmanWorkerdoctrineTask.class.php 32981 2011-09-02 08:10:19Z bicou $
 */
class gearmanWorkerdoctrineTask extends sfGearmanWorkerBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('debug', null, sfCommandOption::PARAMETER_NONE, 'Debug environment flag'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),

      new sfCommandOption('server', null, sfCommandOption::PARAMETER_OPTIONAL, 'Gearman job server config key'),
      new sfCommandOption('count', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of jobs for worker to run before exiting', 100),
      new sfCommandOption('timeout', null, sfCommandOption::PARAMETER_REQUIRED, 'Timeout in seconds', 20),
      new sfCommandOption('verbose', null, sfCommandOption::PARAMETER_NONE, 'Log workloads and additional events'),

      new sfCommandOption('model', null, sfCommandOption::PARAMETER_OPTIONAL, 'Doctrine model name'),
      new sfCommandOption('methods', null, sfCommandOption::PARAMETER_OPTIONAL, 'Doctrine work methods name'),
      new sfCommandOption('config', null, sfCommandOption::PARAMETER_OPTIONAL, 'gearman.yml doctrine config key'),
    ));

    $this->namespace        = 'gearman';
    $this->name             = 'worker-doctrine';
    $this->briefDescription = 'Gearman worker daemon for doctrine jobs';
    $this->detailedDescription = <<<EOF
The [gearman:worker-doctrine|INFO] start a gearman worker for doctrine jobs.
Call it with:

  [php symfony gearman:worker-doctrine|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->command_options = $options;

    // create context
    sfContext::createInstance($this->configuration);

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    // connect to gearman events
    $this->connectGearmanEvents();

    // s => ms
    $options['timeout'] *= 1000;

    // create and work a worker
    $worker = new sfGearmanWorkerDoctrine($options, $this->dispatcher);

    try
    {
      $worker->loop();
    }
    catch(sfGearmanTimeoutException $e)
    {
    }
  }
}

