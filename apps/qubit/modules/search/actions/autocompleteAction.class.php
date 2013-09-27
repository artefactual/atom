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

/**
 * Use _msearch to query ES multiple times at once, using query_string.
 *
 * @package AccesstoMemory
 * @subpackage search
 */
class SearchAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    // Store user query string, erase wildcards
    $this->queryString = strtr($request->query, array('*' => '', '?' => ''));

    // If the query is empty, don't query
    if ('' == preg_replace('/[\s\t\r\n]*/', '', $this->queryString))
    {
      $this->forward404();
    }

    // Should I be doing this in ES with search_analyzer?
    $this->queryString = mb_strtolower($this->queryString);

    // Current culture
    $culture = $this->context->user->getCulture();

    $client = QubitSearch::getInstance()->client;
    $index = QubitSearch::getInstance()->index;

    // Multisearch object
    $mSearch = new \Elastica\Multi\Search($client);

    foreach (array(
      array(
        'type' => 'QubitInformationObject',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('slug', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId')),
      array(
        'type' => 'QubitRepository',
        'field' => sprintf('i18n.%s.authorizedFormOfName', $culture),
        'fields' => array('slug', sprintf('i18n.%s.title', $culture))),
      array(
        'type' => 'QubitActor',
        'field' => sprintf('i18n.%s.authorizedFormOfName', $culture),
        'fields' => array('slug', sprintf('i18n.%s.title', $culture))),
      array(
        'type' => 'QubitTerm',
        'field' => sprintf('i18n.%s.name', $culture),
        'fields' => array('slug', sprintf('i18n.%s.title', $culture)))) as $item)
    {
      $search = new \Elastica\Search($client);
      $search
        ->addIndex($index)
        ->addType($index->getType($item['type']));

      $query = new \Elastica\Query();
      $query
        ->setSize(3)
        ->setFields($item['fields'])
        ->setHighlight(array(
            'require_field_match' => true, // Restrict highlighting to matched fields
            'fields' => array(
              $item['field'].'.autocomplete' => array(
                  'fragment_size' => 100, // Size limit for the highlighted fragmetns
                  'number_of_fragments' => 0, // Request the entire field
              ))));

      $queryText = new \Elastica\Query\Text();
      $queryText->setFieldQuery($item['field'].'.autocomplete', $this->queryString);

      if (isset($request->realm) && ctype_digit($request->realm) && 'QubitInformationObject' == $item['type'])
      {
        $queryBool = new \Elastica\Query\Bool;
        $queryBool->addMust($queryText);
        $queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $request->realm)));
        $query->setQuery($queryBool);

        // Store realm in user session
        $this->context->user->setAttribute('search-realm', $request->realm);
      }
      else
      {
        $query->setQuery($queryText);
      }

      $search->setQuery($query);

      if ('QubitInformationObject' == $item['type'])
      {
        // Filter
        $filter = new \Elastica\Filter\Bool;

        // Filter drafts
        QubitAclSearch::filterDrafts($filter);

        // Set filter when needed
        if (0 < count($filter->toArray()))
        {
          $query->setFilter($filter);
        }
      }

      $mSearch->addSearch($search);
    }

    $resultSets = $mSearch->search();

    $this->descriptions = $resultSets[0];
    $this->repositories = $resultSets[1];
    $this->actors = $resultSets[2];
    $this->subjects = $resultSets[3];

    // Return a 404 response if there are no results
    if (0 == $this->descriptions->getTotalHits() + $this->repositories->getTotalHits() + $this->actors->getTotalHits() + $this->subjects->getTotalHits())
    {
      $this->forward404();
    }

    // Preload levels of descriptions
    if (0 < $this->descriptions->getTotalHits())
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
  }
}
