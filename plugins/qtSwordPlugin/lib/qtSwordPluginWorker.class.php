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

/*
    How to call the worker:
    (daemon-tools, upstart or any other tool is welcomed!)

  php symfony gearman:worker \
    --config=sword \
    --application=qubit \
    --connection=propel \
    --timeout=-1 \
    --count=0 \
    --verbose
*/

class qtSwordPluginWorker
{
  public static function depositSwordPackage($job, $worker)
  {
    // Notification
    $worker->notifyEventJob($job);

    // Unserialize contents passed to the job
    $package = unserialize($job->workload());

    // Close any database resource available before the process is forked
    // Issue "MySQL server has gone away"
    Propel::close();

    // Fork process
    $pid = pcntl_fork();

    if ($pid == -1)
    {
      echo "Fork request failed.\n";
    }
    else if ($pid)
    {
      echo "Forked requested, waiting...";
      pcntl_wait($status);
    }
    else
    {
      echo " forked!\n";

      // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
      sfConfig::add(QubitSetting::getSettingsArray());

      if (!is_writable(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'index') ||
          !is_writable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.sfConfig::get('app_upload_dir')))
      {
        throw new sfGearmanException('ERROR: Read-write access needed in {sf_data_dir}/index and {sf_web_dir}/{app_upload_dir}, sudo-me! (example: sudo -u www-data ...)?');
      }

      $pid = getmypid();
      echo sprintf("[%s] Running new fork...\n", $pid);

      if (isset($package['location']))
      {
        echo sprintf("[%s] A package was deposited by reference.\n", $pid);
        echo sprintf("[%s] Location: %s\n", $pid, $package['location']);
        echo sprintf("[%s] Processing...\n", $pid);
      }
      else if (isset($package['filename']))
      {
        echo sprintf("[%s] A package was deposited by upload. Processing...\n", $pid);
      }

      try
      {
        $resource = QubitInformationObject::getById($package['information_object_id']);

        echo sprintf("[%s] Object slug: %s\n", $pid, $resource->slug);

        $extractor = qtPackageExtractorFactory::build($package['format'],
          $package + array('resource' => $resource, 'job' => $job));

        $extractor->run();

        # Simulating a exception for testing fork behavior
        # throw new Exception('Foobar');
      }
      catch (Exception $e)
      {
        echo sprintf("[%s] Exception: %s\n", $pid, $e);
      }

      Propel::close();

      echo sprintf("[%s] Fork finished\n", $pid);
    } // Fork ends

    echo sprintf("Job finished\n");

    return true;
  }
}
