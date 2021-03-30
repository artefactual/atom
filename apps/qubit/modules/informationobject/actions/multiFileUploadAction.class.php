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

class InformationObjectMultiFileUploadAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();

        $this->resource = $this->getRoute()->resource;

        // Check that object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'update') && !$this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) {
            QubitAcl::forwardUnauthorized();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        // Get max upload size limits
        $this->maxFileSize = QubitDigitalObject::getMaxUploadSize();
        $this->maxPostSize = QubitDigitalObject::getMaxPostSize();

        // Paths for uploader javascript
        $this->uploadResponsePath = "{$this->context->routing->generate(null, ['module' => 'digitalobject', 'action' => 'upload'])}?".http_build_query([session_name() => session_id()]);
        $this->uploadTmpDir = "{$this->request->getRelativeUrlRoot()}/uploads/tmp";

        // Build form
        $this->form->setValidator('files', new QubitValidatorCountable(['required' => true]));

        $this->form->setValidator('title', new sfValidatorString());
        $this->form->setWidget('title', new sfWidgetFormInput());
        $this->form->setDefault('title', 'image %dd%');

        $this->form->setValidator('levelOfDescription', new sfValidatorString());

        $choices = [];
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item) {
            $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item;
        }

        $this->form->setWidget('levelOfDescription', new sfWidgetFormSelect(['choices' => $choices]));

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters(), $request->getFiles());
            if ($this->form->isValid()) {
                $this->processForm();
            }
        }
    }

    public function processForm()
    {
        $tmpPath = sfConfig::get('sf_upload_dir').'/tmp';

        // Upload files
        $i = 0;
        $informationObjectSlugList = [];

        foreach ($this->form->getValue('files') as $file) {
            if (0 == strlen($file['infoObjectTitle'] || 0 == strlen($file['tmpName']))) {
                continue;
            }

            ++$i;

            // Create an information object for this digital object
            $informationObject = new QubitInformationObject();
            $informationObject->parentId = $this->resource->id;

            if (0 < strlen($title = $file['infoObjectTitle'])) {
                $informationObject->title = $title;
            }

            if (null !== $levelOfDescription = $this->form->getValue('levelOfDescription')) {
                $params = $this->context->routing->parse(Qubit::pathInfo($levelOfDescription));
                $informationObject->levelOfDescription = $params['_sf_route']->resource;
            }

            $informationObject->setStatus(['typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID, 'statusId' => sfConfig::get('app_defaultPubStatus')]);

            // Save description
            $informationObject->save();

            if (file_exists("{$tmpPath}/{$file['tmpName']}")) {
                // Upload asset and create digital object
                $digitalObject = new QubitDigitalObject();
                $digitalObject->object = $informationObject;
                $digitalObject->usageId = QubitTerm::MASTER_ID;
                $digitalObject->assets[] = new QubitAsset($file['name'], file_get_contents("{$tmpPath}/{$file['tmpName']}"));

                $digitalObject->save();
            }

            $informationObjectSlugList[] = $informationObject->slug;

            // Clean up temp files
            if (file_exists("{$tmpPath}/{$file['tmpName']}")) {
                unlink("{$tmpPath}/{$file['tmpName']}");
            }
        }

        $this->redirect([$this->resource, 'module' => 'informationobject', 'action' => 'multiFileUpdate', 'items' => implode(',', $informationObjectSlugList)]);
    }
}
