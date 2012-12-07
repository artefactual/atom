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

class TaxonomyAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    $criteria = new Criteria;
    $criteria = QubitTaxonomy::addEditableTaxonomyCriteria($criteria);
    $criteria->addJoin(QubitTaxonomy::ID, QubitTaxonomyI18n::ID);
    $criteria->add(QubitTaxonomyI18n::CULTURE, $this->context->user->getCulture());

    // Narrow results by query
    if (0 < strlen($request->query))
    {
      $criteria->add(QubitTaxonomyI18n::NAME, "$request->query%", Criteria::LIKE);
    }

    // Limit results by ACL
    $criterion = QubitAcl::getFilterCriterion($criteria, QubitTaxonomy::getById(QubitTaxonomy::ROOT_ID), 'createTerm');
    if (isset($criterion) && true !== $criterion)
    {
      $criteria->addAnd($criterion);
    }
    else if (false === $criterion)
    {
      // If access denied to all taxonomies, then return nothing
      return sfView::NONE;
    }

    // Sort by name
    $criteria->addAscendingOrderByColumn(QubitTaxonomyI18n::NAME);

    // Show first 10 results
    $criteria->setLimit(10);

    $this->taxonomies = QubitTaxonomy::get($criteria);
  }
}
