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

class InformationObjectTreeViewAction extends sfAction
{
  public function execute($request)
  {
    if ('all' == $request->show)
    {
      $this->resource = QubitInformationObject::getRoot();
    }
    else
    {
      $this->resource = $this->getRoute()->resource;
    }

    // Number of siblings that we are showing above and below the current node
    // It's good to keep this number small since getTreeViewSiblings can be very
    // slow (when sorting by title or identifierTitle)
    $numberOfPreviousOrNextSiblings = 4;

    switch ($request->show)
    {
      case 'prevSiblings':

        $this->items = $this->resource->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'previous'));

        $this->hasPrevSiblings = count($this->items) > $numberOfPreviousOrNextSiblings;
        if ($this->hasPrevSiblings)
        {
          array_pop($this->items);
        }

        // Reverse array
        $this->items = array_reverse($this->items);

        break;

      case 'nextSiblings':

        $this->items = $this->resource->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'next'));

        $this->hasNextSiblings = count($this->items) > $numberOfPreviousOrNextSiblings;
        if ($this->hasNextSiblings)
        {
          array_pop($this->items);
        }

        break;

      case 'all':
      case 'item':
      default:

        // Find first child visible
        $criteria = new Criteria;
        $criteria->add(QubitInformationObject::PARENT_ID, $this->resource->id);
        $criteria = QubitInformationObject::addTreeViewSortCriteria($criteria);
        foreach (QubitInformationObject::get($criteria) as $item)
        {
          // ACL checks
          if (QubitAcl::check($item, 'read'))
          {
            $firstChild = $item;

            break;
          }
        }

        if (isset($firstChild))
        {
          // Merge the first chlid found and its potential siblings
          $this->items = array_merge(array($firstChild), $firstChild->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 2, 'position' => 'next')));

          $this->hasNextSiblings = count($this->items) > $numberOfPreviousOrNextSiblings;
          if ($this->hasNextSiblings)
          {
            array_pop($this->items);
          }
        }
        else
        {
          // This is a rare case where a node looked expandable
          // and then we realized that any children was visible for the user
          $this->forward404();
        }

        break;
    }
  }
}
