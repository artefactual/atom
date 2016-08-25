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

class InformationObjectDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'delete'))
    {
      QubitAcl::forwardUnauthorized();
    }

    if ($request->isMethod('delete'))
    {
      $parent = $this->resource->parent;
      $this->resource->deleteFullHierarchy();

      if (isset($parent->parent))
      {
        $this->redirect(array($parent, 'module' => 'informationobject'));
      }

      $this->redirect(array('module' => 'informationobject', 'action' => 'browse'));
    }

    // Apparently we can't slice a QubitQuery. `previewSize` is shared with
    // the template so we can break the loop when desired.
    $this->count = count($this->resource->descendants);
    $this->previewSize = (int)sfConfig::get('app_hits_per_page', 10);
    $this->previewIsLimited = $this->count > $this->previewSize;
  }
}
