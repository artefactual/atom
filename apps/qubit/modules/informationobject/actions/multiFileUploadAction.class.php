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
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    // Check that object exists and that it is not the root
    if (!isset($this->resource) || !isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Check if uploads are allowed
    if (!QubitDigitalObject::isUploadAllowed())
    {
      QubitAcl::forwardToSecureAction();
    }

    // Add javascript libraries
    $this->response->addJavaScript('/vendor/yui/logger/logger', 'last');
    $this->response->addJavaScript('/vendor/yui/uploader/uploader-min', 'last');
    $this->response->addJavaScript('multiFileUpload', 'last');

    // Get max upload size limits
    $this->maxUploadSize = QubitDigitalObject::getMaxUploadSize();

    // Paths for uploader javascript
    $this->uploadSwfPath = "{$this->request->getRelativeUrlRoot()}/vendor/yui/uploader/assets/uploader.swf";
    $this->uploadResponsePath = "{$this->context->routing->generate(null, array('module' => 'digitalobject', 'action' => 'upload'))}?".http_build_query(array(session_name() => session_id()));
    $this->uploadTmpDir = "{$this->request->getRelativeUrlRoot()}/uploads/tmp";

    // Build form
    $this->form->setValidator('files', new QubitValidatorCountable(array('required' => true)));

    $this->form->setValidator('title', new sfValidatorString);
    $this->form->setWidget('title', new sfWidgetFormInput);
    $this->form->setDefault('title', 'image %dd%');

    $this->form->setValidator('levelOfDescription', new sfValidatorString);

    $choices = array();
    $choices[null] = null;
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item)
    {
      $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
    }

    $this->form->setWidget('levelOfDescription', new sfWidgetFormSelect(array('choices' => $choices)));

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters(), $request->getFiles());
      if ($this->form->isValid())
      {
        $this->processForm();
      }
    }
  }

  public function processForm()
  {
    $tmpPath = sfConfig::get('sf_upload_dir').'/tmp';

    // Upload files
    $i = 0;

    foreach ($this->form->getValue('files') as $file)
    {
      if (0 == strlen($file['infoObjectTitle'] || 0 == strlen($file['tmpName'])))
      {
        continue;
      }

      $i++;

      // Create an information object for this digital object
      $informationObject = new QubitInformationObject;
      $informationObject->parentId = $this->resource->id;

      if (0 < strlen($title = $file['infoObjectTitle']))
      {
        $informationObject->title = $title;
      }

      if (null !== $levelOfDescription = $this->form->getValue('levelOfDescription'))
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($levelOfDescription));
        $informationObject->levelOfDescription = $params['_sf_route']->resource;
      }

      $informationObject->setStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID, 'statusId' => sfConfig::get('app_defaultPubStatus')));

      // Save description
      $informationObject->save();

      if (file_exists("$tmpPath/$file[tmpName]"))
      {
        // Upload asset and create digital object
        $digitalObject = new QubitDigitalObject;
        $digitalObject->informationObject = $informationObject;
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $digitalObject->assets[] = new QubitAsset($file['name'], file_get_contents("$tmpPath/$file[tmpName]"));

        $digitalObject->save();
      }

      $thumbnailIsGeneric = (bool) strstr($file['thumb'], 'generic-icons');

      // Clean up temp files
      if (file_exists("$tmpPath/$file[tmpName]"))
      {
        unlink("$tmpPath/$file[tmpName]");
      }
      if (!$thumbnailIsGeneric && file_exists("$tmpPath/$file[thumb]"))
      {
        unlink("$tmpPath/$file[thumb]");
      }
    }

    $this->redirect(array($this->resource, 'module' => 'informationobject'));
  }
}
