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

class ObjectValidateCsvAction extends DefaultEditAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [];

    public function execute($request)
    {
        parent::execute($request);

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $this->processForm();

                $this->doBackgroundValidate($request);

                $this->setTemplate('validateCsv');
            }
        } else {
            $this->title = $this->context->i18n->__('Validate CSV');
            $this->response->setTitle("{$this->title} - {$this->response->getTitle()}");
        }
    }

    protected function earlyExecute()
    {
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
    }

    protected function addField($name)
    {
        return parent::addField($name);
    }

    protected function processField($field) {}

    /**
     * Launch the file import background job and return.
     *
     * @param $request data
     */
    protected function doBackgroundValidate($request)
    {
        $file = $request->getFiles('file');

        $validateCsvRoute = ['module' => 'object', 'action' => 'validateCsv'];

        // Move uploaded file to new location to pass off to background arFileImportJob.
        try {
            $file = Qubit::moveUploadFile($file);
        } catch (sfException $e) {
            $this->getUser()->setFlash('error', $e->getMessage());
            $this->redirect($validateCsvRoute);
        }

        // if we got here without a file upload, go to file selection
        if (0 == count($file) || empty($file['tmp_name'])) {
            $this->redirect($validateCsvRoute);
        }

        $options = [
            'objectType' => $request->getParameter('objectType'),
            // Choose import type based on importType parameter
            // This decision used to be based in the file extension but some users
            // experienced problems when the extension was omitted
            'importType' => $importType,
            'file' => $file,
        ];

        try {
            $job = QubitJob::runJob('arValidateCsvJob', $options);

            $this->getUser()->setFlash('notice', $this->context->i18n->__('CSV validation initiated. Check %1%job %2%%3% to view the results of the validation.', [
                '%1%' => sprintf('<a class="alert-link" href="%s">', $this->context->routing->generate(null, ['module' => 'jobs', 'action' => 'report', 'id' => $job->id])),
                '%2%' => $job->id,
                '%3%' => '</a>',
            ]), ['persist' => false]);
        } catch (sfException $e) {
            $this->context->user->setFlash('error', $e->getMessage());
            $this->redirect($validateCsvRoute);
        }
    }
}
