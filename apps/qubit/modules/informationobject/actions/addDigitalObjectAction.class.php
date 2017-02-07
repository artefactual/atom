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
 * Digital Object edit component
 *
 * @package    AccesstoMemory
 * @subpackage digital object
 * @author     david juhasz <david@artefactual.com>
 */
class InformationObjectAddDigitalObjectAction extends sfAction
{
  protected function addFields($request)
  {
    // Single upload
    if (0 < count($request->getFiles()))
    {
      $this->form->setValidator('file', new sfValidatorFile);
    }

    $this->form->setWidget('file', new sfWidgetFormInputFile);

    // URL
    if (isset($request->url) && 'http://' != $request->url)
    {
      $this->form->setValidator('url', new QubitValidatorUrl);
    }

    $this->form->setDefault('url', 'http://');
    $this->form->setWidget('url', new sfWidgetFormInput);
  }

  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = $this->getRoute()->resource;

    // Get repository to test upload limits
    $this->repository = $this->resource->getRepository(array('inherit' => true));

    // Check that object exists and that it is not the root
    if (!isset($this->resource) || !isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check if already exists a digital object
    if (null !== $digitalObject = $this->resource->getDigitalObject())
    {
      $this->redirect(array($digitalObject, 'module' => 'digitalobject', 'action' => 'edit'));
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

    // Add form fields
    $this->addFields($request);

    // Process form
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters(), $request->getFiles());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();
        $this->resource->updateXmlExports();

        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }
  }

  /**
   * Upload the asset selected by user and create a digital object with appropriate
   * representations.
   *
   * @return DigitalObjectEditAction this action
   */
  public function processForm()
  {
    $digitalObject = new QubitDigitalObject;

    if (null !== $this->form->getValue('file'))
    {
      $name = $this->form->getValue('file')->getOriginalName();
      $content = file_get_contents($this->form->getValue('file')->getTempName());

      $digitalObject->assets[] = new QubitAsset($name, $content);
      $digitalObject->usageId = QubitTerm::MASTER_ID;
    }
    else if (null !== $this->form->getValue('url'))
    {
      // Catch errors trying to download remote resource
      try
      {
        $digitalObject->importFromURI($this->form->getValue('url'));
      }
      catch (sfException $e)
      {
        // Log download exception
        $this->logMessage($e->getMessage, 'err');
      }
    }

    $this->resource->digitalObjects[] = $digitalObject;
  }
}
