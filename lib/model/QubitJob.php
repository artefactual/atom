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
  private $notes = array();

  /**
   * Set the job status to error
   *
   * @param string  $errorNote  Optional note to give additional error information
   */
  public function setStatusError($errorNote = null)
  {
    if ($errorNote !== null)
    {
      $note = new QubitNote;
      $note->content = $errorNote;

      if (!isset($this->id))
      {
        throw new sfException('Tried to set a job status on a job that is not saved yet');
      }

      $note->objectId = $this->id;
      $this->notes[] = $note;
    }

    $this->statusId = QubitTerm::JOB_STATUS_ERROR_ID;
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
} 
