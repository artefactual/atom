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

class UserListAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (!isset($request->filter))
    {
      $request->filter = 'onlyActive';
    }

    $criteria = new Criteria;

    if (isset($request->subquery))
    {
      // Search over username or email
      $c1 = $criteria->getNewCriterion(QubitUser::USERNAME, "%$request->subquery%", Criteria::LIKE);
      $c2 = $criteria->getNewCriterion(QubitUser::EMAIL, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $criteria->add($c1);
    }

    $criteria->addAscendingOrderByColumn(QubitUser::USERNAME);

    switch ($request->filter)
    {
      case 'onlyInactive':
        $criteria->add(QubitUser::ACTIVE, 0);

        break;

      case 'onlyActive':
      default:
        $criteria->add(QubitUser::ACTIVE, 1);
    }

    $this->pager = new QubitPager('QubitUser');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->users = $this->pager->getResults();
  }
}
