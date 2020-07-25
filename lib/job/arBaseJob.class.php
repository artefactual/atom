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
 * A base for a Gearman job in AtoM
 *
 * @package    AccesstoMemory
 * @subpackage jobs
 * @author     Mike G <mikeg@artefactual.com>
 */

class arBaseJob extends Net_Gearman_Job_Common
{
  /*
   * Required parameters:
   *
   * Declares parameters that are mandatory for the jobs execution. They can be
   * extended on each job subclasse using the $extraRequiredParameters property.
   * If any of the required paramareters is missing the job will fail.
   */
  private $requiredParameters = array('id', 'name');

  /*
   * Parallel execution and retry time:
   *
   * In instances where two or more workers are setup, multiple jobs could run in parallel.
   * We want to avoid that in jobs that make sensitive changes to the nested set, like the
   * arObjectMoveJob and the import jobs. The Gearman job server doesn't include a built in
   * system to postpone/schedule jobs so, if multiple jobs from the $avoidParallelExecutionJobs
   * variable bellow are executed at the same time, the late ones will wait, retrying after
   * the amount of seconds indicated in $waitForRetryTime, until the previous ones are finished
   * or the maximun amount of tries ($maxTries) is reached. Due to the limitations of the Gearman
   * job server, the waiting jobs will block the workers executing them until they are ended.
   */
  protected $avoidParallelExecutionJobs = array('arObjectMoveJob', 'arFileImportJob');
  protected $waitForRetryTime = 10;
  protected $maxRetries = 10;

  protected $dispatcher = null;
  protected $downloadFileExtension = null; // Child class should set if creating user downloads

  public function run($parameters)
  {
    $context = sfContext::getInstance();
    $this->i18n = $context->i18n;
    $this->user = $context->user;
    $this->dispatcher = $context->getEventDispatcher();
    sfConfig::add(QubitSetting::getSettingsArray());

    $this->checkRequiredParameters($parameters);

    // Instantiate QubitJob
    if (null === $this->job = QubitJob::getById($parameters['id']))
    {
      throw new Net_Gearman_Job_Exception('Called a Gearman worker with an invalid QubitJob id.');
    }

    $this->logger = new arJobLogger($this->dispatcher, array('level' => sfLogger::INFO, 'job' => $this->job));

    // Catch all possible exceptions in job execution and throw
    // Net_Gearman_Job_Exception to avoid breaking the worker
    try
    {
      $this->info($this->i18n->__('Job started.'));

      // If this is a sensitive job
      if (in_array(get_class($this), $this->avoidParallelExecutionJobs))
      {
        // Wait until other sensitive jobs are finished by order
        $retries = 0;
        while (!$this->canBeFullyExecuted())
        {
          // Fail the job if we have reached the max. amount of retries
          if ($retries++ == $this->maxRetries)
          {
            $this->error($this->i18n->__('Maximum retries reached (%1). Please, try to launch the job again when other sensitive jobs are finished or contact an administrator', array('%1' => $this->maxRetries)));

            return false;
          }

          // Log retry info
          $this->info($this->i18n->__('Another sensitive job is being executed, will retry in %1 seconds', array('%1' => $this->waitForRetryTime)));

          sleep($this->waitForRetryTime);
        }
      }

      Qubit::clearClassCaches();

      // Attempt signIn based on job's user. Before calling signIn(), $this->
      // user->isAuthenticated() will always evaluate to false - user object is
      // assigned in signIn().
      $this->signIn();

      // Run un-authenticated job cleanup if this is an unauthenticated job.
      if (!$this->user->isAuthenticated())
      {
        $this->deleteOldUnauthenticatedJobs();
      }

      $this->createJobsDownloadsDirectory();

      $this->runJob($parameters);

      QubitSearch::getInstance()->flushBatch();

      $this->signOut();

      $this->info($this->i18n->__('Job finished.'));
    }
    catch (Exception $e)
    {
      // TODO: Create undoJob() functions in subclasses for cleanups

      // Mark QubitJob as failed
      $this->error('Exception: '.$e->getMessage());
    }
  }

  /**
   * Check if all required parameters are present in $parameters,
   * if one is missing throw an exception.
   *
   * @param   $parameters  the parameters passed to this job
   */
  protected function checkRequiredParameters($parameters)
  {
    if (isset($this->extraRequiredParameters))
    {
      $this->requiredParameters = array_merge($this->requiredParameters, $this->extraRequiredParameters);
    }

    foreach ($this->requiredParameters as $paramName)
    {
      if (!isset($parameters[$paramName]))
      {
        throw new Net_Gearman_Job_Exception("Required parameter not found for job: $paramName");
      }
    }
  }

  /**
   * A wrapper to log error messages and set the QubitJob status to error.
   * This will also attach the error message as a note in the QubitJob.
   *
   * @param string  $message  the error message
   */
  protected function error($message)
  {
    if (!isset($this->job) || !isset($this->job->name))
    {
      throw new Net_Gearman_Job_Exception('Called arBaseJob::error() before QubitJob fetched.');
    }

    $this->logger->info($message);
    $this->job->setStatusError($message);
    $this->job->save();
  }

  /**
   * A wrapper to log info messages.
   *
   * @param string  $message  the error message
   */
  protected function info($message)
  {
    if (!isset($this->job->name))
    {
      throw new Net_Gearman_Job_Exception('Called arBaseJob::info() before QubitJob fetched.');
    }

    $this->logger->info($message);
  }

  /**
   * Return the job's download file path (or null if job doesn't create
   * a download).
   *
   * @return string  file path
   */
  public function getDownloadFilePath()
  {
    $downloadFilePath = null;

    if (!is_null($this->downloadFileExtension))
    {
      $downloadFilePath = $this->getJobsDownloadDirectory() . DIRECTORY_SEPARATOR . $this->getJobDownloadFilename();
    }

    return $downloadFilePath;
  }

  /**
   * Return the job's download file's relative path (or null if job doesn't
   * create a download).
   *
   * @return string  file path
   */
  public function getDownloadRelativeFilePath()
  {
    $downloadRelativeFilePath = null;

    if (!is_null($this->downloadFileExtension))
    {
      $relativeBaseDir = 'downloads' . DIRECTORY_SEPARATOR . 'jobs';
      $downloadRelativeFilePath = $relativeBaseDir . DIRECTORY_SEPARATOR . $this->getJobDownloadFilename();
    }

    return $downloadRelativeFilePath;
  }

  /**
   * Get the jobs download directory, a subdirectory of main AtoM downloads directory
   *
   * @return string  directory path
   */
  public function getJobsDownloadDirectory()
  {
    $downloadsPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'downloads';
    return $downloadsPath . DIRECTORY_SEPARATOR . 'jobs';
  }

  private function getJobDownloadFilename()
  {
    return md5($this->job->id) .'.'. $this->downloadFileExtension;
  }

  /**
   * Create job temporary directory where the files will be added before
   * they are compressed and added to the downloads folder. Use a MD5 hash
   * created from instance info, job id and the current Epoch time to avoid
   * collisions when multiple AtoM instances are available on the same machine
   * and in instances where the database is regenerated from another dump (like
   * it's done in sites with public and private instances), where the job id
   * could be repeated, adding the export results to an existing export folder.
   *
   * @return string  Temporary directory path
   */
  protected function createJobTempDir()
  {
    $name = md5(
      sfConfig::get('sf_root_dir') .
      sfConfig::get('app_workers_key', '') .
      $this->job->id .
      date_timestamp_get()
    );
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
    mkdir($path);

    return $path;
  }

  /**
   * Create jobs download directory, a subdirectory of main AtoM downloads
   * directory, if it doesn't already exist.
   *
   * @return void
   */
  private function createJobsDownloadsDirectory()
  {
    if (!is_null($this->downloadFileExtension) && !is_dir($this->getJobsDownloadDirectory()))
    {
      mkdir($this->getJobsDownloadDirectory(), 0755, true);
    }
  }

  /**
   * Create ZIP file from results
   *
   * @param string   Path of file to write CSV data to
   * @param boolean  Optional: Whether to include digital objects
   *
   * @return array   Error messages
   */
  protected function createZipForDownload($path)
  {
    $errors = array();

    if (!is_writable($this->getJobsDownloadDirectory()))
    {
      $errors[] = $this->i18n->__('Cannot write to directory');
    }
    else
    {
      
      $zip = new ZipArchive();

      if (true != $zip->open($this->getDownloadFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE))
      {
        $errors[] = $this->i18n->__('Cannot initialize file');
      }
      else 
      {
        // Check if we need to include digital objects
        if (array_key_exists('includeDigitalObjects', $this->params) && $this->params['includeDigitalObjects'])
        {
          // Keep track of digital object file names so we can append a bracketed number if any are duplicated
          $fileNames = array();

          // Get permitted digital object ids (if any) and iterate
          foreach($this->getDigitalObjects() as $id)
          {
            $do = QubitDigitalObject::getById($id);
            if (null != $do)
            {
              $doPath = $do->getAbsolutePath();
              if (file_exists($doPath))
              {
                $fileName = basename($doPath);
                if (!array_key_exists($fileName, $fileNames))
                {
                  // Filename not used yet - add to tracker
                  $fileNames[$fileName] = 0;
                }
                else
                {
                  // Filename has been used - increment counter and add to filename
                  $fileNames[$fileName]++;
                  $doPathInfo = pathinfo($doPath);
                  $fileName = "{$doPathInfo['filename']}_{$fileNames[$fileName]}.{$doPathInfo['extension']}";
                }
                try
                {
                  $zip->addFile($doPath, $fileName);
                }
                catch (Exception $e)
                {
                  if ($this->user->isAdministrator())
                  {
                    $errors[] = 'Exception: '.$e->getMessage();
                  }
                  else {
                    $errors[] = $this->i18n->__(
                      'Sorry, but there was an error locating a digital object (#%1%). ' . 
                      'This has prevented any further digital objects from being exported. ' .
                      'Please contact an administrator.' , 
                      array(
                        '%1%' => $id
                      )
                    );
                  }

                  break;
                }
              }
            }
          }
        }

        // Add exported data (files)
        foreach (scandir($path) as $file)
        {
          if (!$error && !is_dir($file))
          {
            try
            {
              $zip->addFile($path . DIRECTORY_SEPARATOR . $file, $file);
            }
            catch (Exception $e)
            {
              if ($this->user->isAdministrator())
              {
                $errors[] = 'Exception: '.$e->getMessage();
              }
              else {
                $errors[] = $this->i18n->__('Sorry, but there was an error retrieving a data file. This has stopped the export process. Please contact an administrator.');
              }

              break;
            }
          }
        }
        $zip->close();
      }
    }

    return $errors;
  }

  /**
   * Set job owner in user Context. ACL checks require this to be set.
   * Job owner's user is grabbed from the QubitJob instance.
   *
   * @return null
   */
  protected function signIn()
  {
    // Unauthenticated jobs were introduced in 2.4.x. If getById()is called
    // on an unauthenticated job it will return null since it will not have
    // a valid user associated with it. Only run signIn() for valid users.
    if (null !== $user = QubitUser::getById($this->job->userId))
    {
      $this->user->signIn($user);
    }
  }

  /**
   * Clean up job owner & user Context.
   *
   * @return null
   */
  protected function signOut()
  {
    // Need to delete the ACL instance because we are in a gearman worker loop.
    // Calling destruct() forces a new QubitAcl instance for each job.
    QubitAcl::destruct();
    if (null !== $user = QubitUser::getById($this->job->userId))
    {
      $this->user->signOut();
    }
  }

  /**
   * Delete old unauthenticated jobs.
   *
   * @return null
   */
  protected function deleteOldUnauthenticatedJobs()
  {
    $now = new DateTime('now');
    $oldDate = date_sub($now, date_interval_create_from_date_string('2 days'));

    $criteria = new Criteria;
    $criteria->add(QubitJob::CREATED_AT, $oldDate, Criteria::LESS_THAN);
    $criteria->add(QubitJob::USER_ID, null, Criteria::ISNULL);

    foreach (QubitJob::get($criteria) as $job)
    {
      if (isset($job->downloadPath))
      {
        unlink($job->downloadPath);
      }
      $job->delete();
    }
  }

  /**
   * Check if another sensitive job is running.
   *
   * @return boolean true if this is the oldest being executed
   *                 false if there is an older one in execution
   */
  protected function canBeFullyExecuted()
  {
    // Add job names directly to the query to avoid params escaping
    $jobNames = "('" . implode("','", $this->avoidParallelExecutionJobs) . "')";

    // Select sensitive jobs running ordering by created_at
    $sql = "SELECT job.id FROM job
     LEFT JOIN object ON object.id = job.id
     WHERE job.status_id = :statusId
     AND job.name IN $jobNames
     ORDER BY object.created_at;";

    $params = array(':statusId' => QubitTerm::JOB_STATUS_IN_PROGRESS_ID);
    $runningJobs = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));

    // Edge case where the QubitJobs are cleared while this one is waiting
    if (count($runningJobs) == 0)
    {
      throw new Net_Gearman_Job_Exception('There is not a running QubitJob in the database associated this job.');
    }

    // If this job is the first one, it can be fully executed
    return $this->job->id === $runningJobs[0]['id'];
  }

  /**
   * Return an array of digital object ids if any are attached to clipboard items
   * and current user has permission to view masters
   *
   * @return array
   */
  protected function getDigitalObjects()
  {
    // Prepare array for digital object ids
    $digitalObjects = array();

    // Process if export option is set and this is a description or actor export
    if (sfConfig::get('app_clipboard_export_digitalobjects_enabled', false) && ('informationObject' == $this->params['objectType'] || 'actor' == $this->params['objectType']))
    {
      // Get clipboard objects
      $criteria = new Criteria;

      // Filter on clipboard slugs
      $criteria->add(QubitSlug::SLUG, $this->params['params']['slugs'], Criteria::IN);

      switch ($this->params['objectType'])
      {
        case 'informationObject':
          $criteria->addJoin(QubitInformationObject::ID, QubitSlug::OBJECT_ID);
          // Hide drafts if necessary
          if($this->params['public'])
          {
            $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
          }
          $items = QubitInformationObject::get($criteria);

          break;

        case 'actor':
          $criteria->addJoin(QubitActor::ID, QubitSlug::OBJECT_ID);
          $items = QubitActor::get($criteria);

          break;
      }

      // Iterate filtered clipboard objects
      foreach ($items as $item)
      {
        $a = $item->digitalObjectsRelatedByobjectId;
        // Look for digital objects attached to each clipboard item
        if (0 != count($a))
        {
          // Get master object
          $digitalObject = $a[0];
          
          // If we need to add in check for images only, then use:
          // $digitalObject->isImage() or $digitalObject->isWebCompatibleImageFormat()
          // ----------
          // Do appropriate ACL check(s). Master copy of text objects are always allowed for reading
          // QubitActor does not have a ACL check for readmaster - so only enable for authenticated users.
          if (
            $digitalObject->masterAccessibleViaUrl()
            && (
              QubitTerm::TEXT_ID == $digitalObject->mediaTypeId
              || (
                'actor' == $this->params['objectType']
                && $this->user->isAuthenticated()
                && QubitAcl::check($item, 'read')
              )
              || (
                'informationObject' == $this->params['objectType']
                && QubitAcl::check($item, 'readMaster')
                && QubitGrantedRight::checkPremis($item->id, 'readMaster')
                && !$digitalObject->hasConditionalCopyright()
              )
            )
          )
          {
            // Add master image id to array
            $digitalObjects[] = $digitalObject->id;
          }
        }
      }
    }

    return $digitalObjects;
  }
}
