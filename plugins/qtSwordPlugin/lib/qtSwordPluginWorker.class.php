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

class qtSwordPluginWorker extends Net_Gearman_Job_Common
{
  protected $dispatcher = null;

  protected function log($message)
  {
    $this->dispatcher->notify(new sfEvent($this, 'gearman.worker.log',
      array('message' => $message)));
  }

  public function run($package)
  {
    $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

    $this->log('A new job has started to being processed.');

    // Close any database resource available before the process is forked
    // to solve the issue "MySQL server has gone away"
    Propel::close();

    // Fork process
    $pid = pcntl_fork();

    if ($pid == -1)
    {
      $this->log('Fork request failed.');
    }
    else if ($pid)
    {
      $this->log('Fork request, waiting...');
      pcntl_wait($status);
    }
    else
    {
      $pid = getmypid();

      $this->log(sprintf('[%s] Running new fork...', $pid));

      // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
      sfConfig::add(QubitSetting::getSettingsArray());

      if (!is_writable(sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'index') ||
          !is_writable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.sfConfig::get('app_upload_dir')))
      {
        throw new sfGearmanException('ERROR: Read-write access needed in {sf_data_dir}/index and {sf_web_dir}/{app_upload_dir}, sudo-me! (example: sudo -u www-data ...)?');
      }

      if (isset($package['location']))
      {
        $this->log(sprintf('[%s] A package was deposited by reference.', $pid));
        $this->log(sprintf('[%s] Location: %s', $pid, $package['location']));
      }
      else if (isset($package['filename']))
      {
        $this->log(sprintf('[%s] A package was deposited by upload.', $pid));
      }

      $this->log(sprintf('[%s] Processing...', $pid));

      try
      {
        $resource = QubitInformationObject::getById($package['information_object_id']);

        $this->log(sprintf('[%s] Object slug: %s', $pid, $resource->slug));

        $extractor = qtPackageExtractorFactory::build($package['format'],
          $package + array('resource' => $resource, 'job' => $job));

        $extractor->run();

        # Simulating a exception for testing fork behavior
        # throw new Exception('Foobar');
      }
      catch (Exception $e)
      {
        $this->log(sprintf('[%s] Exception: %s', $pid, $e));
      }

      Propel::close();

      $this->log(sprintf('[%s] Fork finished.', $pid));
    } // Fork ends

    $this->log(sprintf('[%s] Job finished.', $pid));

    return true;
  }
}
