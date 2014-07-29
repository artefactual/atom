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
class gearmanWorkerTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));

    $this->namespace        = 'tools';
    $this->name             = 'gearman-worker';
    $this->briefDescription = 'Gearman worker daemon';
    $this->detailedDescription = <<<EOF
The [tools:gearman-worker|INFO] start a gearman worker.
Call it with:

  [php symfony tools:gearman-worker|INFO]
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
      // Connect this worker to gearmand
      $worker = new Net_Gearman_Worker(array('localhost:4730'));

      // Register abilities (jobs)
      foreach (array('qtSwordPluginWorker', 'arUpdateArtworkWorker') as $item)
      {
        $worker->addAbility($item);

        $this->logSection('gearman-worker', 'New ability: '.$item);
      }

      $this->logSection('gearman-worker', 'Running worker...');
      $this->logSection('gearman-worker', 'PID '.getmypid());

      $counter = 0;
      $storedLastJob = null;

      // The worker loop!
      $worker->beginWork(
        // Pass a callback that pings the database every ~30 seconds
        // in order to keep the connection alive. AtoM connects to MySQL in a
        // persistent way that timeouts when running the worker for a long time.
        // Another option would be to catch the ProperException from the worker
        // and restablish the connection when needed. Also, the persistent mode
        // could be disabled for this worker. See issue #4182.
        // If a new job has been executed: clear cache of all classes and save ES
        // documents in the batch queue (we need to call the magic method explictly
        // because the object isn't destroyed in a worker)
        function($idle, $lastJob) use (&$counter, &$storedLastJob)
        {
          if ($storedLastJob != $lastJob)
          {
            $storedLastJob = $lastJob;

            QubitSearch::getInstance()->__destruct();

            foreach (array(
              'QubitAccessLog',
              'QubitActorI18n',
              'QubitContactInformation',
              'QubitContactInformationI18n',
              'QubitEventI18n',
              'QubitFunctionI18n',
              'QubitInformationObjectI18n',
              'QubitKeymap',
              'QubitMenu',
              'QubitMenuI18n',
              'QubitNote',
              'QubitNoteI18n',
              'QubitOaiHarvest',
              'QubitOaiRepository',
              'QubitObject',
              'QubitOtherName',
              'QubitOtherNameI18n',
              'QubitPhysicalObjectI18n',
              'QubitProperty',
              'QubitPropertyI18n',
              'QubitRelationI18n',
              'QubitRepositoryI18n',
              'QubitRightsI18n',
              'QubitSetting',
              'QubitSettingI18n',
              'QubitSlug',
              'QubitStaticPageI18n',
              'QubitStatus',
              'QubitTaxonomyI18n',
              'QubitTermI18n') as $className)
            {
              $className::clearCache();
            }
          }

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

  public function gearmanWorkerLogger(sfEvent $event)
  {
    $this->logSection('gearman-worker', $event['message']);
  }
}
