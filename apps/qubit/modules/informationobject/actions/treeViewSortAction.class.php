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

class InformationObjectTreeViewSortAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // Check parameter
    if (!in_array($request->move, array('moveAfter', 'moveBefore')))
    {
      $this->forward404();
    }

    // Check permissions and make sure that the user is not playing with
    // the root object, we do not want to sort that one
    if (QubitInformationObject::ROOT_ID == $this->resource->id
        || !QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // This is the object reference that it will be used for sorting
    // The object dragged in the treeview will be put before or after this one
    $params = $this->context->routing->parse(Qubit::pathInfo($request->target));

    // Make sure the object exists
    if (!isset($params['_sf_route']))
    {
      $this->forward404();
    }
    $target = $params['_sf_route']->resource;

    // Go! This operation is delegated to the ORM
    // It could take a long time if the tree is very big
    switch ($request->move)
    {
      case 'moveAfter':
        $this->resource->moveToNextSiblingOf($target);
        echo "after";

        break;

      case 'moveBefore':
        $this->resource->moveToPrevSiblingOf($target);
        echo "before";

        break;
    }

    return sfView::NONE;
  }
}
