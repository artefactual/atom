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
class JobsExportAction extends DefaultBrowseAction
{
  public function execute($request)
  {
    if (!$this->context->user || !$this->context->user->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    if ($this->context->user->isAdministrator())
    {
      $jobs = QubitJob::getAll();
    }
    else
    {
      $jobs = QubitJob::getJobsByUser($user);
    }

    $csvFilename = 'atom-job-history-' . strftime('%Y-%m-%d') . '.csv';

    $response = $this->getResponse();
    $response->setContentType('text/csv');
    $response->setHttpHeader('Content-Disposition', 'attachment; filename="' . $csvFilename . '"');
    $response->setContent($this->getCSVString($jobs));

    return sfView::NONE;
  }

  /**
   * Generate a CSV with a jobs history for all jobs in $jobs.
   * @param  array $jobs  The array of jobs to write information to CSV for
   */
  private function getCSVString($jobs)
  {
    $output = array(
      array('startDate', 'endDate', 'jobName', 'jobStatus', 'jobInfo', 'jobUser')
    );

    foreach ($jobs as $job)
    {
      // Get notes, separated by | if multiple
      $notes = $job->getNotes();
      $notesString = '';
      foreach ($notes as $note)
      {
        if (strlen($notesString) > 0)
        {
          $notesString .= ' | ';
        }

        $notesString .= $note->content;
      }

      // Get user name
      $name = 'None';
      if ($job->userId != null)
      {
        $user = QubitUser::getById($job->userId);
      }

      if (isset($user))
      {
        $name = $user->username;
      }

      $output[] = array(
        $job->getCreationDateString(),
        $job->getCompletionDateString(),
        $job->name,
        $job->getStatusString(),
        $notesString,
        $name
      );
    }

    ob_start();

    $fp = fopen('php://output', 'w');
    foreach ($output as $row)
    {
      fputcsv($fp, $row);
    }

    fclose($fp);

    $ret = ob_get_contents();
    ob_end_clean();

    return $ret;
  }
}
