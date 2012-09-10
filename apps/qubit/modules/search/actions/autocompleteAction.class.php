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

class SearchAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    // remove wildcard characters so we have a clean term query
    $querystring = strtr($this->request->query, array('*' => '', '?' => ''));

    // if querystring is empty, don't query
    if ('' == preg_replace('/[\s\t\r\n]*/', '', $querystring))
    {
      return sfView::NONE;
    }

    $query = new Elastica_Query();
    $query->setLimit(3);
    $query->setSort(array('_score' => 'desc', 'slug' => 'asc'));

    $queryString = new Elastica_Query_QueryString($querystring . '*');
    $queryString->setDefaultOperator('AND');

    // repositories
    $queryString->setFields(array('actor.authorizedFormOfName'));
    $query->setFields(array('slug', 'actor'));
    $query->setQuery($queryString);

    $this->repositories = QubitSearch::getInstance()->index->getType('QubitRepository')->search($query);
    $this->repositoriesHits = $this->repositories->getTotalHits();

    // actors
    $queryString->setFields(array('i18n.authorizedFormOfName'));
    $query->setFields(array('slug', 'i18n'));
    $query->setQuery($queryString);

    $this->actors = QubitSearch::getInstance()->index->getType('QubitActor')->search($query);
    $this->actorsHits = $this->actors->getTotalHits();

    // information objects
    $queryString->setFields(array('i18n.title'));
    $query->setFields(array('slug', 'levelOfDescriptionId', 'i18n'));
    $query->setQuery($queryString);

    $this->descriptions = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
    $this->descriptionsHits = $this->descriptions->getTotalHits();

    if (0 < $this->descriptionsHits)
    {
      $sql = '
        SELECT
          t.id,
          ti18n.name
        FROM
          '.QubitTerm::TABLE_NAME.' AS t
        LEFT JOIN '.QubitTermI18n::TABLE_NAME.' AS ti18n ON (t.id = ti18n.id AND ti18n.culture = ?)
        WHERE
          t.taxonomy_id = ?';

      $this->levelsOfDescription = array();
      foreach (QubitPdo::fetchAll($sql, array($this->context->user->getCulture(), QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID)) as $item)
      {
        $this->levelsOfDescription[$item->id] = $item->name;
      }
    }

    // terms
    $queryString->setFields(array('i18n.name'));
    $query->setFields(array('slug', 'i18n', 'taxonomyId'));
    $query->setQuery($queryString);

    $filter = new Elastica_Filter_Term();
    $this->subjects = QubitSearch::getInstance()->index->getType('QubitTerm')->search($query->setFilter($filter->setTerm('taxonomyId', QubitTaxonomy::SUBJECT_ID)));
    $this->subjectsHits = $this->subjects->getTotalHits();

    if (0 == $this->descriptionsHits && 0 == $this->actorsHits && 0 == $this->repositoriesHits && 0 == $this->subjectsHits)
    {
      return sfView::NONE;
    }
  }
}
