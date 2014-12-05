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
 * Gearman worker task
 *
 * @package    symfony
 * @subpackage task
 */
class jobWorkerTask extends arBaseTask
{
  private static $gearmanConf = null;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('types', null, sfCommandOption::PARAMETER_REQUIRED, 'Type of jobs to perform (check config/gearman.yml for details)', ''),
      new sfCommandOption('abilities', null, sfCommandOption::PARAMETER_REQUIRED, 'A comma separated string indicating which jobs this worker can do.', '')
    ));

    $this->addArguments(array(
    ));

    $this->namespace        = 'jobs';
    $this->name             = 'worker';
    $this->briefDescription = 'Gearman worker daemon';
    $this->detailedDescription = <<<EOF
Usage: php symfony [jobs:worker|INFO] [--abilities="myAbility1, myAbility2, ..."][--types="general, sword, ..."]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration($options['application'], $options['env'], false);
    $context = sfContext::createInstance($configuration);

    // Using the current context, get the event dispatcher and suscribe an event in it
    $context->getEventDispatcher()->connect('gearman.worker.log', array($this, 'gearmanWorkerLogger'));

    // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
    sfConfig::add(QubitSetting::getSettingsArray());

    // Unset default net_gearman prefix for jobs
    define('NET_GEARMAN_JOB_CLASS_PREFIX', '');
    try
    {
      $abilities = $this->getAbilities($options);
      $servers = $this->getServers();

      if (!count($abilities))
      {
        throw new sfException('No abilities specified.');
      }

      if (!count($servers))
      {
        $servers = array('127.0.0.1:4730');
      }

      $worker = new Net_Gearman_Worker($servers);

      // Register abilities (jobs)
      foreach ($abilities as $ability)
      {
        $ability = trim($ability);
        if (!class_exists($ability))
        {
          throw new sfException("Ability not defined: $ability, please ensure the job is in the lib/task/job directory.");
        }

        $this->logSection('gearman-worker', "New ability: $ability");
        $worker->addAbility(QubitJob::getJobPrefix() . $ability);
      }

      $this->logSection('gearman-worker', 'Running worker...');
      $this->logSection('gearman-worker', 'PID ' . getmypid());

      $counter = 0;

      // The worker loop!
      $worker->beginWork(
        // Pass a callback that pings the database every ~30 seconds
        // in order to keep the connection alive. AtoM connects to MySQL in a
        // persistent way that timeouts when running the worker for a long time.
        // Another option would be to catch the ProperException from the worker
        // and restablish the connection when needed. Also, the persistent mode
        // could be disabled for this worker. See issue #4182.
        function() use (&$counter)
        {
          if (30 == $counter++)
          {
            $counter = 0;

            QubitPdo::prepareAndExecute('SELECT 1');
          }
        });
    }
    catch (Net_Gearman_Exception $e)
    {
      throw $e;
    }
  }

  // Parse the Gearman YAML file
  private static function getGearmanConf()
  {
    if (!self::$gearmanConf)
    {
      self::$gearmanConf = sfYaml::load(sfConfig::get('sf_config_dir') . '/' . 'gearman.yml');
      if (!is_array(self::$gearmanConf) || !array_key_exists('all', self::$gearmanConf))
      {
        throw new sfException('Invalid Gearman settings file specified.');
      }

      self::$gearmanConf = self::$gearmanConf['all'];
    }

    return self::$gearmanConf;
  }

  /**
   * Get all the abilities this worker will have, a possible combination of
   * abilities specified in the CLI and or abilities specified in one or more
   * 'worker types' as outlined in config/gearman.yml. If no abilities are
   * specified as options, the worker will have all abilities defined under
   * lib/job/.
   */
  private function getAbilities($options)
  {
    $gearmanConf = self::getGearmanConf();

    $abilities = array_filter(explode(',', $options['abilities']));

    if ($options['types'])
    {
      $types = array_filter(explode(',', $options['types']));
      foreach ($types as $type)
      {
        if (!array_key_exists($type, $gearmanConf['worker_types']))
        {
          throw new sfException("Invalid type specified: $type -- does it exist in the gearman config file?");
        }

        $abilities = array_merge($abilities, $gearmanConf['worker_types'][$type]);
      }
    }

    if (!count($abilities))
    {
      $abilities = $this->getAllDefinedJobs();
    }

    return $abilities;
  }

  /**
   * Return an array with all class names for jobs under lib/job/
   */
  private function getAllDefinedJobs()
  {
    $jobs = array();
    foreach (scandir(sfConfig::get('sf_root_dir') . '/lib/job') as $file)
    {
      if ($file == '.' || $file == '..' || $file == 'arBaseJob.class.php')
      {
        continue;
      }

      $jobs[] = str_replace('.class.php', '', $file);
    }

    // Add qtSwordPluginWorker if qtSwordPlugin is enabled
    if (in_array('qtSwordPlugin', unserialize(sfConfig::get('app_plugins'))))
    {
      $jobs[] = 'qtSwordPluginWorker';
    }

    return $jobs;
  }

  // Get an array of Gearman host strings
  private function getServers()
  {
    $ret = array();

    $gearmanConf = self::getGearmanConf();
    foreach ($gearmanConf['servers'] as $serv)
    {
      $ret[] = $serv['host'] . ':' . $serv['port'];
    }

    return $ret;
  }

  public function gearmanWorkerLogger(sfEvent $event)
  {
    $this->logSection('gearman-worker', $event['message']);
  }
}
