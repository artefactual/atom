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
  // allow modifications of certain fields
  public function execute($request)
  {
    // return 401 if unauthorized
    if (!sfContext::getInstance()->user->isAuthenticated())
    {
      $this->response->setStatusCode(401);
      return sfView::NONE;
    }

    // return 400 if incorrect HTTP method
    if ($this->request->getMethod() != 'POST')
    {
      $this->response->setStatusCode(400);
      return sfView::NONE;
    }

    $resource = $this->updateFields();

    $this->redirect(array($resource, 'module' => 'informationobject'));
  }

  private function updateFields()
  {
    $resource = $this->getRoute()->resource;

    $postData = $this->request->getPostParameters();

    // update title, if requested
    if (isset($postData['title']))
    {
      $resource->title = $postData['title'];
    }

    // update slug, if requested
    if (isset($postData['slug']))
    {
      $slug = QubitSlug::getByObjectId($resource->id);
      $slug->slug = $postData['slug'];
      $slug->save();
    }

    // update digital object filename, if requested
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
