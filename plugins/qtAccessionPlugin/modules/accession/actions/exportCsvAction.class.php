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

class AccessionExportCsvAction extends sfAction
{
    // Export CSV representation of descriptions occurring in search/browse results
    public function execute($request)
    {
        if ($this->context->user->isAuthenticated()) {
            // The only way to filter accessions is with the search subquery.
            // $getParameters = $request->getGetParameters();
            // $subquery = $getParameters['subquery'];

            $options = [
                'params' => [
                    'slugs' => ['*'],
                ],
            ];

            QubitJob::runJob('arAccessionCsvExportJob', $options);

            // Let user know export has started
            sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);

            $message = $this->context->i18n->__(
                '<strong>Export of accessions initiated.</strong> Check <a class="alert-link" href="%1%">job management</a> page to download the results when it has completed.',
                [
                    '%1%' => url_for(['module' => 'jobs', 'action' => 'browse']),
                ]
            );
            $this->getUser()->setFlash('notice', $message);
        }

        // If referer URL is valid, redirect to it... otherwise, redirect to the information objects browse page)
        if (true === filter_var($request->getHttpHeader('referer'), FILTER_VALIDATE_URL)) {
            $this->redirect($request->getHttpHeader('referer'));
        } else {
            $this->redirect($this->context->routing->generate(null, [null, 'module' => 'accession', 'action' => 'browse']));
        }
    }
}
