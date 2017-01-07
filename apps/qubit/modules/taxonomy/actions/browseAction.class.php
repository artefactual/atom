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

class TaxonomyBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (sfConfig::get('app_enable_institutional_scoping'))
    {
      // Remove search-realm
      $this->context->user->removeAttribute('search-realm');
    }

    // HACK Use id deliberately, vs. slug, because "Subjects" and "Places"
    // menus still use id
    $this->resource = QubitTaxonomy::getById($request->id);
    if (!isset($this->resource))
    {
      $this->redirect(array('module' => 'taxonomy', 'action' => 'list'));
    }
    else
    {
      $this->redirect(array($this->resource, 'module' => 'taxonomy'));
    }

    switch ($this->resource->id)
    {
      case QubitTaxonomy::PLACE_ID:
        $this->icon = 'places';

        break;

      case QubitTaxonomy::SUBJECT_ID:
        $this->icon = 'subjects';

        break;
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
      case 'relevancy':
        $criteria->addDescendingOrderByColumn('hits');

      case 'alphabetic':
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
