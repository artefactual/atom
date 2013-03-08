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
    $context->getEventDispatcher()->connect('gearman.worker.log', function(sfEvent $event)
      {
        $this->logSection('gearman-worker', $event['message']);
      });

    // Unset default net_gearman prefix for jobs
    define('NET_GEARMAN_JOB_CLASS_PREFIX', '');

    try
    {
      // Connect this worker to gearmand
      $worker = new Net_Gearman_Worker(array('localhost:4730'));

      // Register abilities (jobs)
      foreach (array('qtSwordPluginWorker') as $item)
      {
        $worker->addAbility($item);

        $this->logSection('gearman-worker', 'New ability: '.$item);
      }

      $this->logSection('gearman-worker', 'Running worker...');

      // Loop!
      $worker->beginWork();
    }
    catch (Net_Gearman_Exception $e)
    {
      throw $e;
    }
  }
}
