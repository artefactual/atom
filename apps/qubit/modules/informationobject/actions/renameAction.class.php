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

class InformationObjectRenameAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'title',
      'slug',
      'filename');

  protected function earlyExecute()
  {
    $this->resource = $this->getRoute()->resource;

    // Check user authorization
    if (!sfContext::getInstance()->user->isAuthenticated()
      || !QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }
  }

  protected function addField($name)
  {
    if (in_array($name, InformationObjectRenameAction::$NAMES))
    {
      if ($name == 'filename')
      {
        $this->form->setDefault($name, $this->resource->digitalObjects[0]->name);
      }
      else
      {
        $this->form->setDefault($name, $this->resource[$name]);
      }

      $this->form->setValidator($name, new sfValidatorString);
      $this->form->setWidget($name, new sfWidgetFormInput);
    }
  }


  // Allow modification of title, slug, and digital object filename
  public function execute($request)
  {
    parent::execute($request);

    if ($this->request->getMethod() == 'POST')
    {
      // Internationalization needed for flash messages
      ProjectConfiguration::getActive()->loadHelpers('I18N');

      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->updateResource();

        // Let user know description was updated (and if slug had to be adjusted)
        $message = __('Description updated.');

        $postedSlug = $this->form->getValue('slug');

        if ((null !== $postedSlug) && $this->resource->slug != $postedSlug)
        {
          $message .= ' '. __('Slug was adjusted to remove special characters or because it has already been used for another description.');
        }

        $this->getUser()->setFlash('notice', $message);

        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }
  }

  private function updateResource()
  {
    $postedTitle = $this->form->getValue('title');
    $postedSlug = $this->form->getValue('slug');
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
      $findingAidPath = arFindingAidJob::getFindingAidPath($this->resource->id);

      // Attempt to change slug if submitted slug's different than current slug
      if ($postedSlug != $slug->slug)
      {
        $slug->slug = InformationObjectSlugPreviewAction::determineAvailableSlug($postedSlug, $this->resource->id);
        $slug->save();

        // Update finding aid filename
        $newFindingAidPath = arFindingAidJob::getFindingAidPath($this->resource->id);
        if (false === rename($findingAidPath, $newFindingAidPath))
        {
          $message = sprintf('Finding aid document could not be renamed according to new slug (old=%s, new=%s)', $findingAidPath, $newFindingAidPath);
          $this->logMessage($message, 'warning');
        }
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

      // Regenerate derivatives
      digitalObjectRegenDerivativesTask::regenerateDerivatives($digitalObject, array('keepTranscript' => true));
    }

    $this->resource->save();
    $this->resource->updateXmlExports();
  }
}
