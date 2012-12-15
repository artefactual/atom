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

class RepositoryEditThemeAction extends sfAction
{
  public static
    $NAMES = array(
      'background',
      'banner',
      'content',
      'logo',
      'logo_delete');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'background':

        break;

      case 'banner':

        break;

      case 'content':

        break;

      case 'logo':
        $this->form->setValidator($name, new sfValidatorFile(array(
          'max_size' => '262144',
          'mime_types' => 'web_images',
          'path' => $this->resource->getUploadsPath(true),
          'required' => false)));

        sfContext::getInstance()->getConfiguration()->loadHelpers('Url');

        $this->form->setWidget($name, new sfWidgetFormInputFileEditable(array(
          'file_src' => public_path($this->resource->getLogoPath()),
          'edit_mode' => true,
          'is_image' => true,
          'with_delete' => true)));

        break;

      case 'logo_delete':
        $this->form->setValidator($name, new sfValidatorBoolean);
        $this->form->setWidget($name, new sfWidgetFormInputCheckbox);

        break;
    }
  }

  protected function processField($field)
  {
    switch ($name = $field->getName())
    {
      case 'background':

        break;

      case 'banner':

        break;

      case 'content':

        break;
    }
  }

  public function processForm()
  {
    foreach ($this->form as $field)
    {
      if (isset($this->request[$field->getName()]))
      {
        $this->processField($field);
      }
    }

    return $this;
  }

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    $this->form = new sfForm;

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters(), $request->getFiles());

      if ($this->form->isValid())
      {
        $this->processForm();

        // Process logo and logo_delete since they are related
        if (null !== $this->form->getValue('logo_delete'))
        {
          unlink($this->resource->getLogoPath(true));
        }
        else if (null !== $logo = $this->form->getValue('logo'))
        {
          // Call save() method found in sfValidatedFile
          $logo->save($this->resource->getLogoPath(true));
        }

        $this->redirect(array($this->resource, 'module' => 'repository'));
      }
    }
  }
}
