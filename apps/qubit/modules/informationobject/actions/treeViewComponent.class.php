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

class InformationObjectTreeViewComponent extends sfComponent
{
  public function execute($request)
  {
    $this->resource = $request->getAttribute('sf_route')->resource;

    $this->treeviewType = sfConfig::get('app_treeview_type__source', 'sidebar');
    if ($this->treeviewType != 'sidebar')
    {
      return sfView::SUCCESS;
    }

    // We don't want to support sorting when sorting by other than lft
    $this->sortable = 'none' == sfConfig::get('app_sort_treeview_informationobject') && QubitAcl::check($this->resource, 'update');

    // At this point we don't need to do any ACL check on ancestors
    $this->ancestors = $this->resource->getAncestors()->orderBy('lft');

    // Number of siblings that we are showing above and below the current node
    // It's good to keep this number small since getTreeViewSiblings can be very
    // slow (when sorting by title or identifierTitle)
    $numberOfPreviousOrNextSiblings = 4;

    $this->hasPrevSiblings = false;
    $this->hasNextSiblings = false;
    $this->siblingCountNext = 0;
    $this->siblingCountPrev = 0;

    // Child descriptions
    if ($this->resource->hasChildren())
    {
      list($this->children, $this->hasNextSiblings) = $this->resource->getTreeViewChildren(array('numberOfPreviousOrNextSiblings' => $numberOfPreviousOrNextSiblings), $this->siblingCountNext);
    }
    // Show siblings if there's no children, but not for root descriptions
    else if (QubitInformationObject::ROOT_ID != $this->resource->parentId)
    {
      // Previous siblings
      // Get an extra sibling just to know if the + button is necessary
      $this->prevSiblings = $this->resource->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'previous'), $this->siblingCountPrev);
      $this->hasPrevSiblings = count($this->prevSiblings) > $numberOfPreviousOrNextSiblings;

      if ($this->hasPrevSiblings)
      {
        array_pop($this->prevSiblings);
      }

      // Reverse array
      $this->prevSiblings = array_reverse($this->prevSiblings);

      // Next siblings, same logic than above with the + button
      $this->nextSiblings = $this->resource->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'next'), $this->siblingCountNext);
      $this->hasNextSiblings = count($this->nextSiblings) > $numberOfPreviousOrNextSiblings;

      if ($this->hasNextSiblings)
      {
        array_pop($this->nextSiblings);
      }
    }
  }
}
