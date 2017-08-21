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
 * Alerts component
 *
 * @package AccesstoMemory
 * @subpackage default
 * @author Mike Cantelon <mcantelon@gmail.com>
 */
class DefaultAlertsComponent extends sfComponent
{
  public function execute($request)
  {
    $this->conditionalAlerts = array();

    // Display alert, linking to job status page, if user is unauthenticated and jobs exist for user
    $manager = new QubitUnauthenticatedUserJobManager($this->context->user);

    if (!$this->context->user->isAuthenticated() && count($jobs = $manager->getJobs()))
    {
      foreach ($jobs as $job)
      {
        // Skip first display of job status (actions creating jobs will provide initial notifications with more context)
        $firstJobAlertSkipped = $this->getContext()->getUser()->getAttribute('unauthenticatedJobFirstStatusAlertSkipped', array());

        if (isset($firstJobAlertSkipped[$job->id]))
        {
          // Assemble job description
          $message = $this->context->i18n->__('%1% (started: %2%, status: %3%).', array(
            '%1%' => (string)$job,
            '%2%' => $job->getCreationDateString(),
            '%3%' => $job->getStatusString()));

          // Add download path. if applicable
          if (isset($job->downloadPath) && $job->statusId == QubitTerm::JOB_STATUS_COMPLETED_ID)
          {
            $message .= $this->context->i18n->__(' %1%Download%2% (%3% b)', array(
              '%1%' => sprintf('<a href="%s">', sfConfig::get('siteBaseUrl') .'/'. $job->downloadPath),
              '%2%' => '</a>',
              '%3%' => hr_filesize(filesize($job->downloadPath))));
          }

          // Add refresh link
          $message .= ' &mdash; <a href="javascript:location.reload();">refresh the page</a> for updates.';

          // Determine alert type to show
          $alertTypes = array(
            QubitTerm::JOB_STATUS_IN_PROGRESS_ID => 'info',
            QubitTerm::JOB_STATUS_COMPLETED_ID   => 'success',
            QubitTerm::JOB_STATUS_ERROR_ID       => 'error'
          );
          $alertType = $alertTypes[$job->getStatusId()];

          // If job is complete, allow it to be deleted by the user
          $deleteUrl = $this->context->controller->genUrl('jobs/delete?id='. $job->id);
          $deleteUrl = ($job->getStatusId() == QubitTerm::JOB_STATUS_COMPLETED_ID) ? $deleteUrl : null;

          // Add as conditional alert
          array_push($this->conditionalAlerts, array('type' => $alertType, 'message' => $message, 'deleteUrl' => $deleteUrl));

        }
        else
        {
          // Note that the first job status display was skipped
          $firstJobAlertSkipped[$job->id] = true;
          $this->getContext()->getUser()->setAttribute('unauthenticatedJobFirstStatusAlertSkipped', $firstJobAlertSkipped);
        }
      }
    }
  }
}
