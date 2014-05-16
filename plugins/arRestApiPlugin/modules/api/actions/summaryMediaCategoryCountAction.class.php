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

class ApiSummaryMediaCategoryCountAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $data['results'] = $this->getResults();

    return $data;
  }

  protected function getResults()
  {
    // load department terms
    $departmentTaxonomyId = sfConfig::get('app_drmc_taxonomy_departments_id');
    $departmentTerms = QubitFlatfileImport::getTaxonomyTerms($departmentTaxonomyId);

    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    // Get all information objects
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Assign query
    $query->setQuery($queryBool);

    // We don't need details, just facet results
    $query->setLimit(0);

    $this->facetEsQuery('Terms', 'department_count', 'tmsObject.department.id', $query);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    $facets = $resultSet->getFacets();

    foreach($facets['department_count']['terms'] as $index => $term)
    {
      $departmentId = $term['term'];
      $departmentName = $departmentTerms[$departmentId]->name;
      $facets['department_count']['terms'][$index]['department'] = $departmentName;
      unset($facets['department_count']['terms'][$index]['term']);
    }

    return $facets['department_count']['terms'];
  }
}
