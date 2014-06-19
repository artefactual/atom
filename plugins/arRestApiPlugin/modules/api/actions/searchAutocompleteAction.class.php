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
        'level' => 'work',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('identifier', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId')),
      array(
        'type' => 'QubitInformationObject',
        'level' => 'component',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('identifier', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId', 'tmsComponent')),
      array(
        'type' => 'QubitInformationObject',
        'level' => 'technology-record',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('identifier', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId', 'collectionRootId')),
      array(
        'type' => 'QubitInformationObject',
        'level' => 'file',
        'field' => sprintf('i18n.%s.title', $culture),
        'fields' => array('identifier', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId', 'slug', 'digitalObject', 'aipUuid', 'aipName', 'originalRelativePathWithinAip'))) as $item)
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

      switch ($item['level'])
      {
        case 'aip':
          $query->setQuery($queryText);

          break;

        case 'work':
          $queryBool = new \Elastica\Query\Bool;
          $queryBool->addMust($queryText);

          // Filter to Artwork Records
          $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_artwork_record_id'))));

          $query->setQuery($queryBool);

          break;

        case 'component':
          $queryBool = new \Elastica\Query\Bool;
          $queryBool->addMust($queryText);

          // Filter to Components
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

          break;

        case 'technology-record':
          $queryBool = new \Elastica\Query\Bool;
          $queryBool->addMust($queryText);

          // Filter to Technology Records
          $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_supporting_technology_record_id'))));

          $query->setQuery($queryBool);

          break;

        case 'file':
          $queryBool = new \Elastica\Query\Bool;
          $queryBool->addMust($queryText);

          // Filter to Digital Objects
          $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_digital_object_id'))));

          $query->setQuery($queryBool);

          break;
      }

      $search->setQuery($query);

      $mSearch->addSearch($search);
    }

    $resultSets = $mSearch->search();

    $aips = $resultSets[0];
    $artworks = $resultSets[1];
    $components = $resultSets[2];
    $techRecords = $resultSets[3];
    $files = $resultSets[4];

    $results = array();

    foreach ($aips->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'name', get_search_i18n_highlight($hit, 'filename.autocomplete', array('notI18n' => true)));

      $results['aips'][$doc['uuid']] = $result;
    }

    $results['totals']['aips'] = $aips->getTotalHits();

    foreach ($artworks->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n_highlight($hit, 'title.autocomplete'));

      $results['artworks'][$hit->getId()] = $result;
    }

    $results['totals']['artworks'] = $artworks->getTotalHits();

    foreach ($components->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n_highlight($hit, 'title.autocomplete'));
      $this->addItemToArray($result, 'level_of_description_id', $doc['levelOfDescriptionId']);
      $this->addItemToArray($result, 'artwork_id', $doc['tmsComponent']['artwork']['id']);

      $results['components'][$hit->getId()] = $result;
    }

    $results['totals']['components'] = $components->getTotalHits();

    foreach ($techRecords->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n_highlight($hit, 'title.autocomplete'));
      $this->addItemToArray($result, 'collection_root_id', $doc['collectionRootId']);

      $results['technology_records'][$hit->getId()] = $result;
    }

    $results['totals']['technology_records'] = $techRecords->getTotalHits();

    foreach ($files->getResults() as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $result['id'] = (int)$hit->getId();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n_highlight($hit, 'title.autocomplete'));
      $this->addItemToArray($result, 'filename', get_search_i18n($doc, 'title'));
      $this->addItemToArray($result, 'slug', $doc['slug']);
      $this->addItemToArray($result, 'media_type_id', $doc['digitalObject']['mediaTypeId']);
      $this->addItemToArray($result, 'byte_size', $doc['digitalObject']['byteSize']);
      $this->addItemToArray($result, 'mime_type', $doc['digitalObject']['mimeType']);
      $this->addItemToArray($result, 'thumbnail_path', image_path($doc['digitalObject']['thumbnailPath'], true));
      $this->addItemToArray($result, 'aip_uuid', $doc['aipUuid']);
      $this->addItemToArray($result, 'aip_title', $doc['aipName']);
      $this->addItemToArray($result, 'original_relative_path_within_aip', $doc['originalRelativePathWithinAip']);

      $results['files'][$hit->getId()] = $result;
    }

    $results['totals']['files'] = $files->getTotalHits();

    return $results;
  }
}
