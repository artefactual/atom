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

  public function run($parameters)
  {
    $this->checkRequiredParameters($parameters);

    $this->logger = sfContext::getInstance()->getLogger();
    $this->job = QubitJob::getById($parameters['id']);

    if ($this->job === null)
    {
      throw new Net_Gearman_Job_Exception('Called a Gearman worker with an invalid QubitJob id.');
    }

    $this->clearCache();

    // Catch all possible exceptions in job execution and throw
    // Net_Gearman_Job_Exception to avoid breaking the worker
    try
    {
      $this->createJobsDownloadsDirectory();
      $this->runJob($parameters);
    }
    catch (Exception $e)
    {
      // TODO: Create undoJob() functions in subclasses for cleanups

      // Mark QubitJob as failed
      $this->error('Exception: '.$e->getMessage());

      throw new Net_Gearman_Job_Exception($e->getMessage());
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

    $this->logger->err($this->formatLogMsg($message));
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

    $this->logger->info($this->formatLogMsg($message));
  }

  /**
   * Adds valuable meta-data to log messages.
   *
   * @param string  $message  the log message
   */
  private function formatLogMsg($message)
  {
    return sprintf('Job %d "%s": %s', $this->job->id, $this->job->name, $message);
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
  private function getJobsDownloadDirectory()
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
   * Clear various Qubit classes' caches.
   */
  private function clearCache()
  {
    foreach (get_declared_classes() as $c)
    {
      if (strpos($c, 'Qubit') === 0 && method_exists($c, 'clearCache'))
      {
        $c::clearCache();
      }
    }
  }
}
