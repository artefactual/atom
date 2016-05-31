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
 * A bare bones worker to test Gearman / AtoM jobs
 * If optional parameter 'error' is set, print an error.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arTestJob extends arBaseJob
{
  public function runJob($parameters)
  {
    $this->info($this->i18n->__('Got a test job! Id: %1', array('%1' => $this->job->id)));

    if (isset($parameters['error']))
    {
      $this->job->setStatusError($this->i18n->__('The test worker broke!'));
    }
    else
    {
      $this->job->setStatusCompleted();
    }

    // Don't forget to set the job status & save at the end!
    $this->job->save();

    return true;
  }
}
