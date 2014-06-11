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
 *
 * @package    symfony
 * @subpackage jobs
 */

class arTestJob extends arBaseJob
{
  public function run($parameters)
  {
    // This will be an array of required parameter names
    $this->addRequiredParameters(array());

    // parent::run() will check parameters and throw an exception if any are missing
    parent::run($parameters);

    $this->info("Got a test job! id: {$this->job->id}\n");

    if (isset($parameters['error']))
    {
      $this->job->setStatusError('The test worker broke!');
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
