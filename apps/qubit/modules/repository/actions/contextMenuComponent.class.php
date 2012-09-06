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

class RepositoryContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->resource = $request->getAttribute('sf_route')->resource;
    if (!isset($this->resource))
    {
      return sfView::NONE;
    }

    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitObject::ID);
    $criteria->add(QubitInformationObject::REPOSITORY_ID, $this->resource->id);
    $criteria->addAscendingOrderByColumn('title');

    // Sort holdings alphabetically (w/ fallback)
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');

    // Filter draft descriptions
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    // Paginate holdings list
    $this->pager = new QubitPager('QubitInformationObject');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->holdings = $this->pager->getResults();
  }
}
