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

class InformationObjectRenameComponent extends sfComponent
{
  public static
    $NAMES = array(
      'title',
      'slug',
      'filename');

  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    // Add fields
    foreach(InformationObjectRenameComponent::$NAMES as $name)
    {
      $this->addField($name);
    }

    $i18n = sfContext::getInstance()->i18n;

    // Add labels
    $this->form->getWidgetSchema()->setLabels(array(
      'title' => $i18n->__('Description title'),
      'slug' => $i18n->__('Slug'),
      'filename' => $i18n->__('File name')
    ));

    // Add helper text
    $this->form->getWidgetSchema()->setHelps(array(
      'title' => $i18n->__('Editing the description title will automatically update the slug field if the "Update slug" checkbox is selected - you can still edit it after.'),
      'slug' => $i18n->__('Do not use any special characters or spaces in the slug - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the slug will not automatically update the other fields.'),
      'filename' => $i18n->__('Do not use any special characters or spaces in the filename - only lower case alphanumeric characters (a-z, 0-9) and dashes (-) will be saved. Other characters will be stripped out or replaced. Editing the filename will not automatically update the other fields.')
    ));

    // Set rename form values
    $this->form->setDefaults(array(
      'title' => $this->resource->title,
      'slug' => $this->resource->slug,
      'filename' => $this->resource->digitalObjects[0]->name
    ));

    // Set wrapper text for rename form
    $this->form->getWidgetSchema()->setNameFormat('rename[%s]');

    // Handle form submission
    $this->handleSubmit($request);
  }

  public function addField($name)
  {
    switch ($name)
    {
      case 'title':
      case 'slug':
      case 'filename':
        $this->form->setValidator($name, new sfValidatorString(array('required'=>false)));
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      default:

        break;
    }
  }

  // Allow modification of title, slug, and digital object filename
  public function handleSubmit($request)
  {
    // Return 400 if incorrect HTTP method
    if ($request->isMethod('post'))
    {
      $this->form->bind($request->rename);

      if ($this->form->isValid())
      {
        $this->updateResource();
      }
    }
  }

  private function updateResource()
  {
    $postedTitle    = $this->form->getValue('title');
    $postedSlug     = $this->form->getValue('slug');
    $postedFilename = $this->form->getValue('filename');

    // Update title, if title sent
    if (null !== $postedTitle)
    {
      $this->resource->title = $postedTitle;
    }

    // Attempt to update slug if slug sent
    if (null !== $postedSlug)
    {
      $slug = QubitSlug::getByObjectId($this->resource->id);

      // Attempt to change slug if submitted slug's different than current slug
      if ($postedSlug != $slug->slug)
      {
        $slug->slug = InformationObjectSlugPreviewAction::determineAvailableSlug($postedSlug);
        $slug->save();
      }
    }

    // Update digital object filename, if filename sent
    if ((null !== $postedFilename) && count($this->resource->digitalObjects))
    {
      // Parse filename so special characters can be removed
      $fileParts = pathinfo($postedFilename);
      $filename = QubitSlug::slugify($fileParts['filename']) .'.'. QubitSlug::slugify($fileParts['extension']);

      $digitalObject = $this->resource->digitalObjects[0];

      // Rename master file
      $basePath = sfConfig::get('sf_web_dir') . $digitalObject->path;
      $oldFilePath = $basePath . DIRECTORY_SEPARATOR . $digitalObject->name;
      $newFilePath = $basePath . DIRECTORY_SEPARATOR . $filename;
      rename($oldFilePath, $newFilePath);
      chmod($newFilePath, 0644);

      // Change name in database
      $digitalObject->name = $filename;
      $digitalObject->save();

      // Regeneraate derivatives
      digitalObjectRegenDerivativesTask::regenerateDerivatives($digitalObject);
    }

    $this->resource->save();
  }
}
