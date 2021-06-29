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

class PhysicalObjectHoldingsReportExportAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                // Abort if export will be empty
                if (empty($request->includeEmpty) && empty($request->includeDescriptions) && empty($request->includeAccessions)) {
                    $message = $this->context->i18n->__('Please check one or more of the export options.');
                    $this->context->user->setFlash('error', $message);

                    $this->redirect(['module' => 'physicalobject', 'action' => 'holdingsReportExport']);
                } else {
                    $this->doBackgroundExport($request);

                    $this->redirect(['module' => 'physicalobject', 'action' => 'browse']);
                }
            }
        }
    }

    protected function doBackgroundExport($request)
    {
        $options = ['suppressEmpty' => empty($request->includeEmpty)];

        if (!empty($request->includeAccessions) && empty($request->includeDescriptions)) {
            $options['holdingType'] = 'QubitAccession';
        }

        if (empty($request->includeAccessions) && !empty($request->includeDescriptions)) {
            $options['holdingType'] = 'QubitInformationObject';
        }

        if (empty($request->includeAccessions) && empty($request->includeDescriptions)) {
            $options['holdingType'] = 'none';
        }

        $job = QubitJob::runJob('arPhysicalObjectCsvHoldingsReportJob', $options);

        $jobAdminUrl = $this->context->routing->generate(null, ['module' => 'jobs', 'action' => 'browse']);
        $messageParams = [
            '%1%' => '<strong>',
            '%2%' => '</strong>',
            '%3%' => sprintf('<a class="alert-link" href="%s">', $jobAdminUrl),
            '%4%' => '</a>',
        ];

        $message = $this->context->i18n->__(
            '%1%Export initiated.%2% Check %3%job management%4% page to download the results when it has completed.',
            $messageParams
        );

        $this->context->user->setFlash('notice', $message);
    }
}
