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

class InformationObjectRenameAction extends sfAction
{
  // Allow modification of title, slug, and digital object filename
  public function execute($request)
  {
    // Return 401 if unauthorized
    if (!sfContext::getInstance()->user->isAuthenticated()
      || !QubitAcl::check($this->resource, 'update'))
    {
      $this->response->setStatusCode(401);
      return sfView::NONE;
    }

    // Return 400 if incorrect HTTP method
    if ($request->isMethod('post'))
    {
      // Internationalization needed for flash messages
      ProjectConfiguration::getActive()->loadHelpers('I18N');

      $this->renameForm = new InformationObjectRenameForm;

      $this->renameForm->bind($request->rename);

      if ($this->renameForm->isValid())
      {
        $resource = $this->updateResource();

        // Let user know description was updated (and if slug had to be adjusted)
        $message = __('Description updated.');

        $postedSlug = $this->renameForm->getValue('slug');

        if ((null !== $postedSlug) && $resource->slug != $postedSlug)
        {
          $message .= ' '. __('Slug was adjusted to remove special characters or because it has already been used for another description.');
        }

        $this->getUser()->setFlash('notice', $message);

        $this->redirect(array($resource, 'module' => 'informationobject'));
      }
    }
    else
    {
      $this->response->setStatusCode(400);
      return sfView::NONE;
    }
  }

  private function updateResource()
  {
    $resource = $this->getRoute()->resource;

    $postedTitle    = $this->renameForm->getValue('title');
    $postedSlug     = $this->renameForm->getValue('slug');
    $postedFilename = $this->renameForm->getValue('filename');

    // Update title, if title sent
    if (null !== $postedTitle)
    {
      $resource->title = $postedTitle;
    }

    // Attempt to update slug if slug sent
    if (null !== $postedSlug)
    {
      $slug = QubitSlug::getByObjectId($resource->id);

      // Attempt to change slug if submitted slug's different than current slug
      if ($postedSlug != $slug->slug)
      {
        $slug->slug = InformationObjectSlugPreviewAction::determineAvailableSlug($postedSlug);
        $slug->save();
      }
    }

    // Update digital object filename, if filename sent
    if ((null !== $postedFilename) && count($resource->digitalObjects))
    {
      // Parse filename so special characters can be removed
      $fileParts = pathinfo($postedFilename);
      $filename = QubitSlug::slugify($fileParts['filename']) .'.'. QubitSlug::slugify($fileParts['extension']);

      $digitalObject = $resource->digitalObjects[0];

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

    $resource->save();

    return $resource;
  }
}
