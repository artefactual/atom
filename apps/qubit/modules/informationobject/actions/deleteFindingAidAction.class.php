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

class InformationObjectDeleteFindingAidAction extends sfAction
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
    if (!$this->context->user->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    $this->form = new sfForm;
    $this->path = arFindingAidJob::getFindingAidPathForDownload($this->resource->id);
    $parts = explode(DIRECTORY_SEPARATOR, $this->path);
    $this->filename = array_pop($parts);

    if ($request->isMethod('delete'))
    {
      $i18n = $this->context->i18n;

      $params = array(
        'objectId'    => $this->resource->id,
        'description' => $i18n->__('Deleting finding aid for: %1%', array('%1%' => $this->resource->getTitle(array('cultureFallback' => true)))),
        'delete'      => true
      );

      QubitJob::runJob('arFindingAidJob', $params);

      $this->redirect(array($this->resource, 'module' => 'informationobject'));
    }
  }
}
