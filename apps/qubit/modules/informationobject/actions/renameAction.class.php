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
    if ($this->request->getMethod() != 'POST')
    {
      $this->response->setStatusCode(400);
      return sfView::NONE;
    }

    $resource = $this->updateFields();

    // Let user know description was updated (and if slug had to be adjusted)
    ProjectConfiguration::getActive()->loadHelpers('I18N');

    $message = __('Description updated.');

    $postData = $this->request->getPostParameters();

    if (isset($postData['slug']) && $resource->slug != $postData['slug'])
    {
      $message .= ' '. __('Slug was adjusted to remove special characters or because it has already been used for another description.');
    }

    $this->getUser()->setFlash('notice', $message);

    $this->redirect(array($resource, 'module' => 'informationobject'));
  }

  private function updateFields()
  {
    $resource = $this->getRoute()->resource;

    $postData = $this->request->getPostParameters();

    // Update title, if requested
    if (isset($postData['title']))
    {
      $resource->title = $postData['title'];
    }

    // Attempt to update slug if slug sent
    if (isset($postData['slug']))
    {
      $slug = QubitSlug::getByObjectId($resource->id);

      // Attempt to change slug if submitted slug's different than current slug
      if ($postData['slug'] != $slug->slug)
      {
        $slug->slug = InformationObjectSlugPreviewAction::determineAvailableSlug($postData['slug']);
        $slug->save();
      }
    }

    // Update digital object filename, if requested
    if (isset($postData['filename']) && count($resource->digitalObjects))
    {
      $digitalObject = $resource->digitalObjects[0];
      $digitalObject->name = $postData['filename'];
      $digitalObject->save();
    }

    $resource->save();

    return $resource;
  }
}
