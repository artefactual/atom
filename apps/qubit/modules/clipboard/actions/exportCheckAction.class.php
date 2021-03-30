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

class ClipboardExportCheckAction extends sfAction
{
    // Arrays not allowed in class constants
    public static $ALERT_TYPES = [
        QubitTerm::JOB_STATUS_IN_PROGRESS_ID => 'info',
        QubitTerm::JOB_STATUS_COMPLETED_ID => 'success',
        QubitTerm::JOB_STATUS_ERROR_ID => 'error',
    ];

    public function execute($request)
    {
        ProjectConfiguration::getActive()->loadHelpers('Qubit');

        $alerts = $missingTokens = [];
        $tokens = $request->getParameter('tokens', []);

        foreach ($tokens as $token) {
            // Validate token
            if (!ctype_xdigit($token) || 32 != strlen($token)) {
                $missingTokens[] = $token;

                continue;
            }

            $job = QubitJob::getByUserTokenProperty($token);

            // Save and return missing tokens to clear front-end storage
            if (!isset($job)) {
                $missingTokens[] = $token;

                continue;
            }

            // Assemble job description
            $message = $this->context->i18n->__(
                '%1% (started: %2%, status: %3%).',
                [
                    '%1%' => (string) $job,
                    '%2%' => $job->getCreationDateString(),
                    '%3%' => $job->getStatusString(),
                ]
            );

            // Add download path if applicable
            if (isset($job->downloadPath) && QubitTerm::JOB_STATUS_COMPLETED_ID == $job->statusId) {
                $message .= $this->context->i18n->__(
                    ' %1%Download%2% (%3% b)',
                    [
                        '%1%' => sprintf('<a href="%s">', sfConfig::get('app_siteBaseUrl').'/'.$job->downloadPath),
                        '%2%' => '</a>',
                        '%3%' => hr_filesize(filesize($job->downloadPath)),
                    ]
                );
            } else {
                $message .= ' '.$this->context->i18n->__(
                    '%1%Refresh the page%2% for progress updates.',
                    [
                        '%1%' => '<a href="javascript:location.reload();">',
                        '%2%' => '</a>',
                    ]
                );
            }

            // Determine alert type
            $type = $this::$ALERT_TYPES[$job->statusId];

            // If job is complete, allow it to be deleted by the user
            $deleteUrl = $this->context->controller->genUrl('jobs/delete?token='.$token);
            $deleteUrl = QubitTerm::JOB_STATUS_COMPLETED_ID == $job->statusId ? $deleteUrl : null;

            // Add to response data
            $alerts[] = ['type' => $type, 'message' => $message, 'deleteUrl' => $deleteUrl];
        }

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
        $this->response->setStatusCode(200);

        return $this->renderText(json_encode(['alerts' => $alerts, 'missingTokens' => $missingTokens]));
    }
}
