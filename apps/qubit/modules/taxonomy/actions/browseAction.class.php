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

class TaxonomyBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    // HACK Use id deliberately, vs. slug, because "Subjects" and "Places"
    // menus still use id
    $this->resource = QubitTaxonomy::getById($request->id);
    if (!isset($this->resource))
    {
      $this->forward404();
    }

    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, $this->resource->id);

    $criteria->addJoin(QubitTerm::ID, QubitObjectTermRelation::TERM_ID);
    $criteria->addJoin(QubitObjectTermRelation::OBJECT_ID, QubitInformationObject::ID);

    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    // Do culture fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');

    $criteria->addGroupByColumn(QubitTerm::ID);
    $criteria->addAsColumn('hits', 'COUNT('.QubitTerm::ID.')');

    switch ($request->sort)
    {
      case 'hitsDown':
        $criteria->addDescendingOrderByColumn('hits');

        break;

      case 'hitsUp':
        $criteria->addAscendingOrderByColumn('hits');

        break;

      case 'termNameDown':
        $criteria->addDescendingOrderByColumn('name');

        break;

      case 'termNameUp':
      default:
        $criteria->addAscendingOrderByColumn('name');

        break;
    }

    $this->pager = new QubitPager('QubitTerm');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->terms = $this->pager->getResults();
  }
}
