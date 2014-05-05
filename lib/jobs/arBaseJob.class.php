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
  private $requiredParameters = array();

  public function run($parameters)
  {
    $this->addRequiredParameters(array('id', 'name'));
    $this->checkRequiredParameters($parameters);

    $this->logger = sfContext::getInstance()->getLogger();

    $this->job = QubitJob::getById($parameters['id']);

    if ($this->job === null)
    {
      throw new Exception('Called a Gearman worker with an invalid QubitJob id.');
    }
  }

  /**
   * Add a parameter name to a list of required parameters.
   *
   * @param   $parameters  the parameters passed to this job that are required
   */
  protected function addRequiredParameters($parameters)
  {
    $this->requiredParameters = array_merge($this->requiredParameters, $parameters);
  }

  /**
   * Check if all required parameters are present in $parameters,
   * if one is missing throw an exception.
   *
   * @param   $parameters  the parameters passed to this job
   */
  protected function checkRequiredParameters($parameters)
  {
    foreach ($this->requiredParameters as $paramName)
    {
      if (!isset($parameters[$paramName]))
      {
        throw new Exception("Required parameter not found for job: $paramName");
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
      throw new Exception('Called arBaseJob::error() before QubitJob fetched.');
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
      throw new Exception('Called arBaseJob::info() before QubitJob fetched.');
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
}