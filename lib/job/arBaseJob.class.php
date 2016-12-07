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
  protected $downloadFileExtension = null; // Child class should set if creating user downloads

  protected $dispatcher = null;

  public function run($parameters)
  {
    $context = sfContext::getInstance();
    $this->i18n = $context->i18n;
    $this->user = $context->user;
    $this->dispatcher = $context->getEventDispatcher();

    $this->checkRequiredParameters($parameters);

    // Instantiate QubitJob
    if (null === $this->job = QubitJob::getById($parameters['id']))
    {
      throw new Net_Gearman_Job_Exception('Called a Gearman worker with an invalid QubitJob id.');
    }

    $this->logger = new arJobLogger($this->dispatcher, array('level' => sfLogger::INFO, 'job' => $this->job));

    Qubit::clearClassCaches();

    // Catch all possible exceptions in job execution and throw
    // Net_Gearman_Job_Exception to avoid breaking the worker
    try
    {
      $this->signIn();

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
    return $this->job->id .'.'. $this->downloadFileExtension;
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
   * @param string  Path of file to write CSV data to
   *
   * @return int  success bool
   */
  protected function createZipForDownload($path)
  {
    if (!is_writable($this->getJobsDownloadDirectory()))
    {
      return false;
    }

    $zip = new ZipArchive();

    $success = $zip->open($this->getDownloadFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

    if ($success == true)
    {
      foreach(scandir($path) as $file)
      {
        if (!is_dir($file))
        {
          $zip->addFile($path . DIRECTORY_SEPARATOR . $file, $file);
        }
      }

      $zip->close();
    }

    return $success;
  }

  /**
   * Set job owner in user Context. ACL checks require this to be set.
   * Job owner's user is grabbed from the QubitJob instance.
   *
   * @return null
   */
  protected function signIn()
  {
    $user = QubitUser::getById($this->job->userId);
    $this->user->signIn($user);
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
    $this->user->signOut();
  }
}
