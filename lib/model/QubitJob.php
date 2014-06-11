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
 * Represent an asynchronous job
 *
 * @package    AccesstoMemory
 * @subpackage model
 */

class QubitJob extends BaseJob {
  private
    $notes = array();

  /**
   * Run a job via gearman
   *
   * @param string  $jobName  The name of the ability the worker will execute
   *
   * @param array   $jobParams  Whatever parameters need to be passed to the worker. 
   * You can set 'name' to specify the job name, otherwise the class name is used.
   *
   * @param string  $gearmanPath  Optional parameter specifying the host / port where
   * Gearman is running. Defaults to localhost and the default Gearman port.
   *
   * @return  QubitJob  The job that was just created for the running job
   */
  public static function runJob($jobName, $jobParams = array(), $gearmanPath = 'localhost:4730')
  {
    try
    {
      $job = new QubitJob;

      // You can specify 'name' => 'whatever' to make the name human friendly.
      // Default is we just use the job class name.
      if (!isset($jobParams['name']))
      {
        $jobParams['name'] = $jobName;
      }

      $job->name = $jobParams['name'];
      $job->statusId = QubitTerm::JOB_STATUS_IN_PROGRESS_ID;

      $sfUser = sfContext::getInstance()->user;
      if ($sfUser !== null && $sfUser->isAuthenticated())
      {
        $job->userId = $sfUser->getUserID();
      }

      $job->save();

      // Pass in the job id to the worker so it can update status
      $jobParams['id'] = $job->id; 

      // Send a Gearman client request to start the job in any available workers...
      $gmClient = new Net_Gearman_Client($gearmanPath);
      $gmClient->$jobName($jobParams);
    }
    catch (Exception $e)
    {
      throw new sfException("Gearman failed to start a job: $e");
    }

    return $job;
  }

  /**
   * Set the job status to error
   *
   * @param string  $errorNote  Optional note to give additional error information
   */
  public function setStatusError($errorNote = null)
  {
    if ($errorNote !== null)
    {
      $this->addNoteText($errorNote);
    }

    $this->statusId = QubitTerm::JOB_STATUS_ERROR_ID;
    $this->completedAt = new DateTime('now');
  }

  /**
   * Set the job status to in progress
   */
  public function setStatusInProgress()
  {
    $this->statusId = QubitTerm::JOB_STATUS_IN_PROGRESS_ID;
  }

  /**
   * Set the job status to complete
   */
  public function setStatusCompleted()
  {
    $this->statusId = QubitTerm::JOB_STATUS_COMPLETED_ID;
    $this->completedAt = new DateTime('now');
  }

  /**
   * Get a string representing a date.
   * @return  string  The job's creation date in a human readable string.
   */
  private function formatDate($date)
  {
    $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
    return $dateTime ? $dateTime->format('Y-m-d h:i A') : 'N/A';
  }

  /**
   * Get a string representing the job creation date.
   * @return  string  The job's creation date in a human readable string.
   */
  public function getCreationDateString()
  {
    return $this->formatDate($this->createdAt);
  }

  /**
   * Get a string representing the job completion date.
   * @return  string  The job's creation date in a human readable string.
   */
  public function getCompletionDateString()
  {
    return $this->formatDate($this->completedAt);
  }

  /**
   * Get a string representing the job status.
   * @return  string  The job's status in a human readable string.
   */
  public function getStatusString()
  {
    if (!isset($this->statusId))
    {
      return 'unknown';
    }

    switch ($this->statusId)
    {
      case QubitTerm::JOB_STATUS_COMPLETED_ID:
        return 'completed';
      case QubitTerm::JOB_STATUS_IN_PROGRESS_ID:
        return 'running';
      case QubitTerm::JOB_STATUS_ERROR_ID:
        return 'error';
      default:
        return 'unknown';
    }
  }

  /**
   * Add a basic note to this job
   * @param  string  $contents  The text for the note
   */
  public function addNoteText($contents)
  {
    $note = new QubitNote;
    $note->content = $contents;

    if (!isset($this->id))
    {
      throw new sfException('Tried to add a note to a job that is not saved yet');
    }

    $note->objectId = $this->id;
    $this->notes[] = $note;
  }

  /**
   * Get the notes attached to this job
   * @return  QubitQuery  An query of the notes for this job
   */
  public function getNotes()
  {
    $criteria = new Criteria;
    $criteria->add(QubitNote::OBJECT_ID, $this->id);

    return QubitNote::get($criteria);
  }

  /**
   * Add a basic note to this job
   * @param  myUser  $user  the currently logged in user.
   */
  public static function getJobsByUser($user)
  {
    $criteria = new Criteria;
    $criteria->add(QubitJob::USER_ID, $user->getUserID());

    return QubitJob::get($criteria);
  }

  /**
   * Save the job along with its notes
   */
  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->notes as $note)
    {
      $note->save();
    }
  }

  /**
   * Delete the job along with its notes
   */
  public function delete($connection = null)
  {
    parent::delete($connection);

    foreach ($this->notes as $note)
    {
      $note->delete();
    }
  }

  public function __toString()
  {
    return $this->name;
  }
} 
