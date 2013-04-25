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

class TermBrowseTermAction extends DefaultBrowseAction
{
  // Arrays not allowed in class constants
  public static
    $FACETS = array();

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;
    if (!$this->resource instanceof QubitTerm)
    {
      $this->forward404();
    }

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    parent::execute($request);

    $this->queryBool->addMust(new Elastica_Query_Terms('terms.id', array($this->resource->id)));

    switch ($this->resource->taxonomyId)
    {
      case QubitTaxonomy::PLACE_ID:
        $this->icon = 'places';
        $this->queryBool->addMust(new Elastica_Query_Terms('terms.taxonomyId', array(QubitTaxonomy::PLACE_ID)));

        break;

      case QubitTaxonomy::SUBJECT_ID:
        $this->icon = 'subjects';
        $this->queryBool->addMust(new Elastica_Query_Terms('terms.taxonomyId', array(QubitTaxonomy::SUBJECT_ID)));

        break;

      default:
        // pass
        $queryFilter = new Elastica_Query_MatchAll();

        break;
    }

    // Filter drafts
    $this->query = QubitAclSearch::filterDrafts($this->query);

    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);

    // Load the list of termss
    $this->terms = QubitTaxonomy::getTaxonomyTerms($this->resource->taxonomyId);
  }
}
