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
        if (!$this->context->user->isAuthenticated() || !$this->context->user->hasCredential(['editor', 'administrator'], false)) {
            $message = $this->context->i18n->__(
                'You are not allowed to export this entity type.'
            );
            $this->getUser()->setFlash('error', $message);
        } else {
            $options = [
                'params' => [
                    'slugs' => ['*'],
                ],
            ];

            QubitJob::runJob('arAccessionCsvExportJob', $options);

            // Let user know export has started
            $this->context->getConfiguration()->loadHelpers(['Url', 'I18N']);

            $message = '<strong>';
            $message .= $this->context->i18n->__(
                'Your %entity_type% export package is being built.',
                ['%entity_type%' => strtolower(sfConfig::get('app_ui_label_accession', __('Accession')))]
            );
            $message .= '</strong> ';

            $message .= $this->context->i18n->__(
                'The %open_link%job management page%close_link% will show progress and a download link when complete.',
                [
                    '%open_link%' => sprintf(
                        '<strong><a class="alert-link" href="%s">',
                        $this->context->routing->generate(null, [
                            'module' => 'jobs',
                            'action' => 'browse',
                        ])
                    ),
                    '%close_link%' => '</a></strong>',
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
