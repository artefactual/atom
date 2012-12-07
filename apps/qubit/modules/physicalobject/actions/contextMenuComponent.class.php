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

/**
 * Context Menu component for physical objects.
 *
 * @package AccesstoMemory
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
