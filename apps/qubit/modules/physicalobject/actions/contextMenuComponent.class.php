<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Context Menu component for physical objects.
 *
 * @package qubit
 * @subpackage physicalobject
 * @author david juhasz <david@artefactual.com>
 */
class PhysicalObjectContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    $this->resource = $request->getAttribute('sf_route')->resource;

    $this->physicalObjects = array();
    foreach (QubitRelation::getRelatedSubjectsByObjectId('QubitPhysicalObject', $this->resource->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID)) as $item)
    {
      $this->physicalObjects[$item->id] = $item;
    }

    if (1 > count($this->physicalObjects))
    {
      return sfView::NONE;
    }
  }
}
