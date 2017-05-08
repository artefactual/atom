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
 * Unauthenticated job manager
 *
 * @package    symfony
 * @subpackage library
 * @author     Mike Cantelon <mike@artefactual.com>
 */

class QubitUnauthenticatedUserJobManager
{
  function __construct($user)
  {
    $this->user = $user;
  }

  /**
   * Get IDs of QubitJobs associated with unauthenticated user.
   *
   * @return array  ID integers
   */
  function getJobIds()
  {
    return $this->user->getAttribute('jobs', array());
  }  

  /**
   * Store IDs of QubitJobs assiciated with unauthenticated user.
   *
   * @param array $jobIds  array of QubitJob Ids
   *
   * @return array  void
   */
  function setJobIds($jobIds)
  {
    $this->user->setAttribute('jobs', $jobIds);
  }

  /**
   * Get QubitJobs associated with unauthenticated user.
   *
   * @return array  QubitJob instances
   */
  function getJobs()
  {
    $jobs = array();

    foreach ($this->getJobIds() as $jobId)
    {
      if (null !== QubitJob::getById($jobId))
      {
        array_push($jobs, QubitJob::getById($jobId));
      }
    }

    return $jobs;
  }

  /**
   * Associate QubitJob with unauthenticated user.
   *
   * @param object $job  QubitJob instance
   *
   * @return array  void
   */
  function addJobAssociation($job)
  {
    $jobs = $this->getJobIds();
    array_push($jobs, $job->id);
    $this->setJobIds($jobs);
  }

  /**
   * Delete QubitJob if it's complete and associated with unauthenticated user.
   *
   * @param integer $jobId  QubitJob ID
   *
   * @return bool  whether job was deleted
   */
  function deleteJobByIdIfAssociatedAndComplete($jobId)
  {
    if(($index = array_search($jobId, $jobIds = $this->getJobIds())) !== false)
    {
      unset($jobIds[$index]);
      $this->setJobIds($jobIds);
      if ((null !== $job = QubitJob::getById($jobId)) && $job->statusId == QubitTerm::JOB_STATUS_COMPLETED_ID)
      {
        $job->delete();
        return true;
      }
      return false;
    }
  }
}
