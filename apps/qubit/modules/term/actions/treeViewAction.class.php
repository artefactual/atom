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

class TermTreeViewAction extends sfAction
{
  public function execute($request)
  {
    // Number of siblings that we are showing above and below the current node
    $numberOfPreviousOrNextSiblings = 4;

    if ('all' == $request->show)
    {
      $taxonomyId = QubitTerm::getById($request->resourceId)->taxonomyId;
    }
    else
    {
      $this->resource = $this->getRoute()->resource;
    }

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

        // Get first child
        $criteria = new Criteria;
        $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
        $criteria->addAscendingOrderByColumn('name');
        $criteria->addAscendingOrderByColumn('lft');
        $criteria->setLimit(1);
        $first = QubitTerm::getOne($criteria);

        // Create array
        $this->items = array();
        $this->items[] = $first;

        // Merge following siblings to the array
        $this->items = array_merge($this->items, $first->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 2, 'position' => 'next')));

        $this->hasNextSiblings = count($this->items) > $numberOfPreviousOrNextSiblings;
        if ($this->hasNextSiblings)
        {
          array_pop($this->items);
        }

        break;

      case 'item':
      default:

        // Get first child
        $criteria = new Criteria;
        $criteria->add(QubitTerm::PARENT_ID, $this->resource->id);
        $criteria->add(QubitTerm::TAXONOMY_ID, $this->resource->taxonomyId);
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
        $criteria->addAscendingOrderByColumn('name');
        $criteria->addAscendingOrderByColumn('lft');
        $criteria->setLimit(1);
        $first = QubitTerm::getOne($criteria);

        // Create array
        $this->items = array();
        $this->items[] = $first;

        // Merge following siblings to the array
        $this->items = array_merge($this->items, $first->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 2, 'position' => 'next')));

        $this->hasNextSiblings = count($this->items) > $numberOfPreviousOrNextSiblings;
        if ($this->hasNextSiblings)
        {
          array_pop($this->items);
        }

        break;
    }
  }
}
