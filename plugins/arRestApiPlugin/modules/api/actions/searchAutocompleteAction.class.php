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

class ApiSearchAutocompleteAction extends QubitApiAction
{
  protected function get($request)
  {
    // Store user query string, erase wildcards
    $queryString = strtr($request->query, array('*' => '', '?' => ''));

    // If the query is empty, don't query
    if (1 === preg_match('/^[\s\t\r\n]*$/', $queryString))
    {
      throw new QubitApi404Exception('A query must be set');
    }

    // Should I be doing this in ES with search_analyzer?
    $queryString = mb_strtolower($queryString);

    // Current culture
    $culture = $this->context->user->getCulture();

    $client = QubitSearch::getInstance()->client;
    $index = QubitSearch::getInstance()->index;

    // Multisearch object
    $mSearch = new \Elastica\Multi\Search($client);

    foreach (array(
       array(
        'type' => 'QubitAip',
        'level' => 'aip',
        'field' => 'filename',
        'fields' => array('uuid', 'filename')),
      array(
        'type' => 'QubitInformationObject',
        'level' => 'tmsObject',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('identifier', sprintf('i18n.%s.title', $culture))),
      array(
        'type' => 'QubitInformationObject',
        'level' => 'tmsComponent',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('identifier', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId'))) as $item)
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
      $queryText->setFieldQuery($item['field'].'.autocomplete', $queryString);

      if ('tmsObject' == $item['level'])
      {
        $queryBool = new \Elastica\Query\Bool;
        $queryBool->addMust($queryText);

        // Filter to TMS Objects
        $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_artwork_record_id'))));

        $query->setQuery($queryBool);
      }
      else if ('tmsComponent' == $item['level'])
      {
        $queryBool = new \Elastica\Query\Bool;
        $queryBool->addMust($queryText);

        // Filter to TMS Objects
        $componentLevels = array(
          sfConfig::get('app_drmc_lod_archival_master_id'),
          sfConfig::get('app_drmc_lod_artist_supplied_master_id'),
          sfConfig::get('app_drmc_lod_artist_verified_proof_id'),
          sfConfig::get('app_drmc_lod_exhibition_format_id'),
          sfConfig::get('app_drmc_lod_miscellaneous_id'),
          sfConfig::get('app_drmc_lod_component_id')
        );

        $queryBool->addMust(new \Elastica\Query\Terms('levelOfDescriptionId', $componentLevels));

        $query->setQuery($queryBool);
      }
      else
      {
        $query->setQuery($queryText);
      }

      $search->setQuery($query);

      $mSearch->addSearch($search);
    }

    $resultSets = $mSearch->search();

    $aips = $resultSets[0];
    $artworks = $resultSets[1];
    $components = $resultSets[2];

    // Return a 404 response if there are no results
    if (0 == $aips->getTotalHits() + $artworks->getTotalHits() + $components->getTotalHits())
    {
      throw new QubitApi404Exception('No results found found');
    }

    $results = array();
    foreach ($aips->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'name', get_search_i18n_highlight($hit, 'filename.autocomplete', array('notI18n' => true)));

      $results['aips'][$doc['uuid']] = $result;
    }

    $results['aips']['total'] = $aips->getTotalHits();

    foreach ($artworks->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n_highlight($hit, 'title.autocomplete'));

      $results['artworks'][$hit->getId()] = $result;
    }

    $results['artworks']['total'] = $artworks->getTotalHits();

    foreach ($components->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n_highlight($hit, 'title.autocomplete'));
      $this->addItemToArray($result, 'level_of_description_id', $doc['levelOfDescriptionId']);

      $results['components'][$hit->getId()] = $result;
    }

    $results['components']['total'] = $components->getTotalHits();

    return $results;
  }
}
