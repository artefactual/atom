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

class RepositoryEditUploadLimitAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->context->user->isAdministrator())
    {
      // 403 - Forbidden
      $this->getResponse()->setStatusCode(403);

      return sfView::HEADER_ONLY;
    }

    $this->resource = $request->getAttribute('sf_route')->resource;
    if (!isset($this->resource))
    {
      $this->forward404();
    }

    $uploadLimit = $request->getParameter('uploadLimit');

    switch ($uploadLimit['type'])
    {
      case 'disabled':
        $this->resource->uploadLimit = 0;
        break;

      case 'unlimited':
        $this->resource->uploadLimit = -1;
        break;

      case 'limited':
        $this->resource->uploadLimit = $uploadLimit['value'];
        break;
    }

    $this->resource->save();
  }
}
