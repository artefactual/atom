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

class TaxonomyIndexAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->resource = $this->getRoute()->resource;

    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, $this->resource->id);
    $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);

    // Do source culture fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');

    $criteria->addAscendingOrderByColumn('name');

    // Page results
    $this->pager = new QubitPager('QubitTerm');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->terms = $this->pager->getResults();
  }
}
