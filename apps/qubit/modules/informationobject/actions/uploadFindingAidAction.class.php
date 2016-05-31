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

class InformationObjectUploadFindingAidAction extends sfAction
{
  public function execute($request)
  {
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

    // Check if a finding aid file already exists
    if (null !== arFindingAidJob::getFindingAidPathForDownload($this->resource->id))
    {
      $this->redirect(array($this->resource, 'module' => 'informationobject'));
    }

    $this->format = arFindingAidJob::getFindingAidFormat();
    $accept = 'application/' . $this->format;
    $mimeTypes = array($accept);

    // sfValidatorFile gets 'text/rtf' as the mime type of RTF files
    // but the accept attribute works better with only 'application/rtf'
    if ($this->format == 'rtf')
    {
      $mimeTypes[] = 'text/rtf';
    }

    // Create form for file upload
    $this->form = new sfForm;
    $this->form->setWidget('file', new sfWidgetFormInputFile(array(), array('accept' => $accept)));
    $this->form->setValidator('file', new sfValidatorFile(array('required' => true, 'mime_types' => $mimeTypes)));

    // Process form
    if ($request->isMethod('post'))
    {
      $this->form->bind(array(), $request->getFiles());

      if (!$this->form->isValid())
      {
        return;
      }

      $file = $this->form->getValue('file');
      $i18n = $this->context->i18n;

      // Move temporary file before it's deleted at the end of the request
      $path = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . arFindingAidJob::getFindingAidPath($this->resource->id);

      if (!move_uploaded_file($file->getTempName(), $path))
      {
        $this->errorMessage = $i18n->__('Uploaded finding aid could not be moved to the downloads directory.');

        return;
      }

      // Obtain FA transcript and properties using the AtoM worker
      $params = array(
        'objectId'    => $this->resource->id,
        'description' => $i18n->__('Uploading finding aid for: %1%', array('%1%' => $this->resource->getTitle(array('cultureFallback' => true)))),
        'uploadPath'  => $path
      );

      QubitJob::runJob('arFindingAidJob', $params);

      $this->redirect(array($this->resource, 'module' => 'informationobject'));
    }
  }
}
