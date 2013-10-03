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

class AccessionBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->context->user->hasCredential(array('contributor', 'editor', 'administrator'), false))
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (!isset($request->sort))
    {
      if ($this->getUser()->isAuthenticated())
      {
        $request->sort = sfConfig::get('app_sort_browser_user');
      }
      else
      {
        $request->sort = sfConfig::get('app_sort_browser_anonymous');
      }
    }

    $criteria = new Criteria;

    if (isset($request->subquery))
    {
      $criteria->add(QubitAccession::IDENTIFIER, "%$request->subquery%", Criteria::LIKE);
    }

    switch ($request->sort)
    {
      case 'alphabetic':
        $criteria->addAscendingOrderByColumn('identifier');

        break;

      case 'lastUpdated':
      default:
        $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);

        break;
    }

    // Page results
    $this->pager = new QubitPager('QubitAccession');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
