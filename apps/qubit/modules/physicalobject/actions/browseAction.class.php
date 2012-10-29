<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class PhysicalObjectBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->getUser()->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;

    // Do source culture fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitPhysicalObject');

    switch ($request->sort)
    {
      case 'nameDown':
        $criteria->addDescendingOrderByColumn('name');

        break;

      case 'locationDown':
        $criteria->addDescendingOrderByColumn('location');

        break;

      case 'locationUp':
        $criteria->addAscendingOrderByColumn('location');

        break;

      case 'nameUp':
      default:
        $request->sort = 'nameUp';
        $criteria->addAscendingOrderByColumn('name');
    }

    // Page results
    $this->pager = new QubitPager('QubitPhysicalObject');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
