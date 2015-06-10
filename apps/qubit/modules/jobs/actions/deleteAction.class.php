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
 * @package    AccesstoMemory
 * @subpackage jobs
 * @author     Mike G <mikeg@artefactual.com>
 */
class JobsDeleteAction extends sfAction
{
  /**
   * Display a paginated hitlist of information objects (top-level only)
   *
   * @param sfRequest $request
   */
  public function execute($request)
  {
    if (!$this->context->user || !$this->context->user->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    $jobs = QubitJob::getJobsByUser($this->context->user);

    foreach ($jobs as $job)
    {
      if ($job->statusId != QubitTerm::JOB_STATUS_IN_PROGRESS_ID)
      {
        if (isset($job->downloadPath))
        {
          unlink($job->downloadPath);
        }
        $job->delete();
      }
    }

    $this->redirect(array('module' => 'jobs', 'action' => 'browse'));
  }
}
