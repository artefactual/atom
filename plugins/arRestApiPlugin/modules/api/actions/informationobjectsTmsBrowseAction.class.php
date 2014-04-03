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

class ApiInformationObjectsTmsBrowseAction extends QubitApiAction
{
  protected function get($request)
  {
    if (!isset($request->type))
    {
      throw new QubitApiException('A type must be set');
    }

    $data = array();

    switch ($request->type)
    {
      case 'components':
        $results = $this->getTmsComponentsResults();

        break;

      case 'objects':
        $results = $this->getTmsObjectsResults();

        break;

      default:
        throw new QubitApiException('Type not valid, must be \'objects\' or \'components\'');

        break;
    }

    $data['results'] = $results['results'];
    $data['facets'] = $results['facets'];
    $data['total'] = $results['total'];

    return $data;
  }

  protected function getTmsObjectsResults()
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $this->prepareEsSorting($query, array(
      'createdAt' => 'createdAt'));

    // Filter to TMS Objects (artworks)
    $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_artwork_record_id'))));

    // Filter query
    if (isset($this->request->query) && 1 !== preg_match('/^[\s\t\r\n]*$/', $this->request->query))
    {
      $queryText = new \Elastica\Query\Text();
      $queryText->setFieldQuery('i18n.en.title.autocomplete', $this->request->query);

      $queryBool->addMust($queryText);
    }

    // Filter selected facets
    $this->filterEsFacet('classification', 'tmsObject.classification.id', $queryBool);
    $this->filterEsFacet('department', 'tmsObject.department.id', $queryBool);

    // Add facets to the query
    $this->facetEsQuery('Terms', 'classification', 'tmsObject.classification.id', $query);
    $this->facetEsQuery('Terms', 'department', 'tmsObject.department.id', $query);

    // Limit fields
    $query->setFields(array(
      'slug',
      'identifier',
      'inheritReferenceCode',
      'levelOfDescriptionId',
      'publicationStatusId',
      'ancestors',
      'parentId',
      'hasDigitalObject',
      'createdAt',
      'updatedAt',
      'sourceCulture',
      'i18n',
      'tmsObject',
      'dates',
      'creators'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    // Build array from results
    $results = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'id', $doc['identifier']);
      $this->addItemToArray($result, 'title', get_search_i18n($doc, 'title'));
      $this->addItemToArray($result, 'accession_number', $doc['tmsObject']['accessionNumber']);
      $this->addItemToArray($result, 'medium', get_search_i18n($doc, 'extentAndMedium'));
      $this->addItemToArray($result, 'dimensions', get_search_i18n($doc, 'physicalCharacteristics'));
      $this->addItemToArray($result, 'year', get_search_i18n($doc['dates'][0], 'date'));
      $this->addItemToArray($result, 'artist', get_search_i18n($doc['creators'][0], 'authorizedFormOfName'));
      $this->addItemToArray($result, 'artist_date', get_search_i18n($doc['creators'][0], 'datesOfExistence'));
      $this->addItemToArray($result, 'classification', get_search_i18n($doc['tmsObject']['classification'][0], 'name'));
      $this->addItemToArray($result, 'department', get_search_i18n($doc['tmsObject']['department'][0], 'name'));
      $this->addItemToArray($result, 'thumbnail', $doc['tmsObject']['thumbnail']);
      $this->addItemToArray($result, 'full_image', $doc['tmsObject']['fullImage']);

      $results[$hit->getId()] = $result;
    }

    $facets = $resultSet->getFacets();
    $this->populateFacets($facets);

    return
      array(
        'total' => $resultSet->getTotalHits(),
        'facets' => $facets,
        'results' => $results);
  }

  protected function getTmsComponentsResults()
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $this->prepareEsSorting($query, array(
      'createdAt' => 'createdAt'));

    // Filter to TMS Components
    $componentLevels = array(
      sfConfig::get('app_drmc_lod_archival_master_id'),
      sfConfig::get('app_drmc_lod_artist_supplied_master_id'),
      sfConfig::get('app_drmc_lod_artist_verified_proof_id'),
      sfConfig::get('app_drmc_lod_exhibition_format_id'),
      sfConfig::get('app_drmc_lod_miscellaneous_id'),
      sfConfig::get('app_drmc_lod_component_id')
    );

    $queryBool->addMust(new \Elastica\Query\Terms('levelOfDescriptionId', $componentLevels));

    // Filter query
    if (isset($this->request->query) && 1 !== preg_match('/^[\s\t\r\n]*$/', $this->request->query))
    {
      $queryText = new \Elastica\Query\Text();
      $queryText->setFieldQuery('i18n.en.title.autocomplete', $this->request->query);

      $queryBool->addMust($queryText);
    }

    // Limit fields
    $query->setFields(array(
      'slug',
      'identifier',
      'inheritReferenceCode',
      'levelOfDescriptionId',
      'publicationStatusId',
      'ancestors',
      'parentId',
      'hasDigitalObject',
      'createdAt',
      'updatedAt',
      'sourceCulture',
      'i18n',
      'tmsComponent'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    // Build array from results
    $results = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'id', $doc['identifier']);
      $this->addItemToArray($result, 'name', get_search_i18n($doc, 'title'));
      $this->addItemToArray($result, 'phys_desc', get_search_i18n($doc, 'extentAndMedium'));
      $this->addItemToArray($result, 'dimensions', get_search_i18n($doc, 'physicalCharacteristics'));
      $this->addItemToArray($result, 'type', get_search_i18n($doc['tmsComponent']['componentType'][0], 'name'));
      $this->addItemToArray($result, 'install_comments', get_search_i18n($doc['tmsComponent']['installComments'][0], 'content'));
      $this->addItemToArray($result, 'prep_comments', get_search_i18n($doc['tmsComponent']['prepComments'][0], 'content'));
      $this->addItemToArray($result, 'storage_comments', get_search_i18n($doc['tmsComponent']['storageComments'][0], 'content'));
      $this->addItemToArray($result, 'text_entries', get_search_i18n($doc['tmsComponent']['textEntries'][0], 'content'));
      $this->addItemToArray($result, 'count', $doc['tmsComponent']['compCount']);
      $this->addItemToArray($result, 'number', $doc['tmsComponent']['componentNumber']);

      $results[$hit->getId()] = $result;
    }

    $facets = array();

    return
      array(
        'total' => $resultSet->getTotalHits(),
        'facets' => $facets,
        'results' => $results);
  }

  protected function getFacetLabel($name, $term)
  {
    if ($name === 'classification' || $name === 'department')
    {
      if (null !== $item = QubitTerm::getById($term))
      {
        return $item->getName(array('cultureFallback' => true));
      }
    }
  }
}
