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

    // Display alert, linking to job status page, if anonymous jobs exist
    $manager = new QubitUnauthenticatedUserJobManager($this->context->user);

    if (!$this->context->user->isAuthenticated() && count($jobs = $manager->getJobs()))
    {
      if (!in_array($this->context->routing->getCurrentInternalUri(), array('jobs/browse', 'object/export')))
      {
        foreach ($jobs as $job)
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

          // Add as conditional alert
          $deleteUrl = $this->context->controller->genUrl('jobs/delete?id='. $job->id);
          array_push($this->conditionalAlerts, array('type' => 'info', 'message' => $message, 'deleteUrl' => $deleteUrl));
        }
      }
    }
  }
}
