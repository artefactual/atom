<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Digital Object edit component
 *
 * @package    qubit
 * @subpackage digital object
 * @author     David Juhasz <david@artefactual.com>
 * @version    SVN: $Id: editAction.class.php 12014 2012-07-31 03:43:00Z sevein $
 */
class DigitalObjectEditAction extends sfAction
{
  protected function addFormFields()
  {
    // Media type field
    $choices = array();
    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);
    foreach (QubitTerm::get($criteria) as $item)
    {
      $choices[$item->id] = $item->getName(array('cultureFallback' => true));
    }

    asort($choices); // Sort media types by name

    $this->form->setValidator('mediaType', new sfValidatorChoice(array('choices' => array_keys($choices))));
    $this->form->setWidget('mediaType', new sfWidgetFormSelect(array('choices' => $choices)));
    $this->form->setDefault('mediaType', $this->resource->mediaTypeId);

    // Only display "compound digital object" toggle if we have a child with a
    // digital object
    $this->showCompoundObjectToggle = false;
    foreach ($this->informationObject->getChildren() as $item)
    {
      if (null !== $item->getDigitalObject())
      {
        $this->showCompoundObjectToggle = true;

        break;
      }
    }

    if ($this->showCompoundObjectToggle)
    {
      $this->form->setValidator('displayAsCompound', new sfValidatorBoolean);
      $this->form->setWidget('displayAsCompound', new sfWidgetFormSelectRadio(
        array('choices' => array(
          '1' => $this->context->i18n->__('Yes'),
          '0' => $this->context->i18n->__('No')))));

      // Set "displayAsCompound" value from QubitProperty
      $criteria = new Criteria;
      $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
      $criteria->add(QubitProperty::NAME, 'displayAsCompound');

      if (null != $compoundProperty = QubitProperty::getOne($criteria))
      {
        $this->form->setDefault('displayAsCompound', $compoundProperty->getValue(array('sourceCulture' => true)));
      }
    }

    // Add rights component
    $this->rightEditComponent = new RightEditComponent($this->context, 'right', 'edit');
    $this->rightEditComponent->resource = $this->resource;
    $this->rightEditComponent->execute($this->request);

    $maxUploadSize = QubitDigitalObject::getMaxUploadSize();

    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    // If reference representation doesn't exist, include upload widget
    foreach ($this->representations as $usageId => $representation)
    {
      if (null === $representation)
      {
        $repName = "repFile_$usageId";
        $derName = "generateDerivative_$usageId";

        $this->form->setValidator($repName, new sfValidatorFile);
        $this->form->setWidget($repName, new sfWidgetFormInputFile);

        if (-1 < $maxUploadSize)
        {
          $this->form->getWidgetSchema()->$repName->setHelp($this->context->i18n->__('Max. size ~%1%', array('%1%' => hr_filesize($maxUploadSize))));
        }
        else
        {
          $this->form->getWidgetSchema()->$repName->setHelp('');
        }

        // Add "auto-generate" checkbox
        $this->form->setValidator($derName, new sfValidatorBoolean);
        $this->form->setWidget($derName, new sfWidgetFormInputCheckbox(array(), array('value' => 1)));
      }
      // Otherwise, load right component
      else
      {
        $this["rightEditComponent_$usageId"] = new RightEditComponent($this->context, 'right', 'edit');
        $this["rightEditComponent_$usageId"]->resource = $representation;
        $this["rightEditComponent_$usageId"]->nameFormat = 'editRight'.$usageId.'[%s]';
        $this["rightEditComponent_$usageId"]->execute($this->request);
      }
    }
  }

  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = $this->getRoute()->resource;

    // Check that resource exists
    if (!isset($this->resource))
    {
      $this->forward404();
    }

    $this->informationObject = $this->resource->informationObject;

    // Check user authorization
    if (!QubitAcl::check($this->informationObject, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Get representations
    $this->representations = array(
      QubitTerm::REFERENCE_ID => $this->resource->getChildByUsageId(QubitTerm::REFERENCE_ID),
      QubitTerm::THUMBNAIL_ID => $this->resource->getChildByUsageId(QubitTerm::THUMBNAIL_ID));

    $this->addFormFields();

    // Process forms
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters(), $request->getFiles());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->informationObject, 'module' => 'informationobject'));
      }
    }
  }

  /**
   * Update digital object properties, or upload new digital object derivatives.
   *
   * @return DigitalObjectEditAction this action
   */
  public function processForm()
  {
    // Set property 'displayAsCompound'
    $this->resource->setDisplayAsCompoundObject($this->form->getValue('displayAsCompound'));

    // Update media type
    $this->resource->mediaTypeId = $this->form->getValue('mediaType');

    // Process master rights component
    $this->rightEditComponent->processForm();

    // Process reference/thumbnail rights components
    foreach ($this->representations as $usageId => $representation)
    {
      if (!isset($this["rightEditComponent_$usageId"]))
      {
        continue;
      }

      $this["rightEditComponent_$usageId"]->processForm();
      $representation->save();
    }

    // Upload new representations
    $uploadedFiles = array();
    foreach ($this->representations as $usageId => $representation)
    {
      if (null !== $uf = $this->form->getValue("repFile_$usageId"))
      {
        $uploadedFiles[$usageId] = $uf;
      }
    }

    foreach ($uploadedFiles as $usageId => $uploadFile)
    {
      $content = file_get_contents($uploadFile->getTempName());

      if (QubitDigitalObject::isImageFile($uploadFile->getOriginalName()))
      {
        $tmpFile = Qubit::saveTemporaryFile($uploadFile->getOriginalName(), $content);

        if (QubitTerm::REFERENCE_ID == $usageId)
        {
          $maxwidth = (sfConfig::get('app_reference_image_maxwidth')) ? sfConfig::get('app_reference_image_maxwidth') : 480;
          $maxheight = null;
        }
        else if (QubitTerm::THUMBNAIL_ID == $usageId)
        {
          $maxwidth = 100;
          $maxheight = 100;
        }

        $content = QubitDigitalObject::resizeImage($tmpFile, $maxwidth, $maxheight);

        @unlink($tmpFile);
      }

      $representation = new QubitDigitalObject;
      $representation->usageId = $usageId;
      $representation->assets[] = new QubitAsset($uploadFile->getOriginalName(), $content);
      $representation->parentId = $this->resource->id;
      $representation->createDerivatives = false;

      $representation->save();
    }

    // Generate new reference
    if (null != $this->form->getValue('generateDerivative_'.QubitTerm::REFERENCE_ID))
    {
      $this->resource->createReferenceImage();
    }

    // Generate new thumb
    if (null != $this->form->getValue('generateDerivative_'.QubitTerm::THUMBNAIL_ID))
    {
      $this->resource->createThumbnail();
    }
  }
}
