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

class ApiInformationObjectsComponentsBrowseAction extends QubitApiAction
{
  protected function get($request)
  {
    $results = $this->getResults();
    $data['results'] = $results['results'];
    $data['facets'] = $results['facets'];
    $data['total'] = $results['total'];

    return $data;
  }

  protected function getResults()
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

    // Filter selected facets
    $this->filterEsFacet('class', 'tmsComponent.type.id', $queryBool);
    $this->filterEsFacet('type', 'levelOfDescriptionId', $queryBool);
    $this->filterEsRangeFacet('ingestedFrom', 'ingestedTo', 'aips.createdAt', $queryBool);

    // Add facets to the query
    $this->facetEsQuery('Terms', 'classification', 'tmsComponent.type.id', $query);
    $this->facetEsQuery('Terms', 'type', 'levelOfDescriptionId', $query);

    $now = new DateTime();
    $now->setTime(0, 0);

    $dateRanges = array(
      array('to' => $now->modify('-1 year')->getTimestamp().'000'),
      array('from' => $now->getTimestamp().'000'),
      array('from' => $now->modify('+11 months')->getTimestamp().'000'),
      array('from' => $now->modify('+1 month')->modify('-7 days')->getTimestamp().'000'));

    $this->dateRangesLabels = array(
      'Older than a year',
      'From last year',
      'From last month',
      'From last week');

    $this->facetEsQuery('Range', 'dateIngested', 'aips.createdAt', $query, array('ranges' => $dateRanges));

    // Range facet with script to facet total size
    $sizeRanges = array(
      array('to' => 512000),
      array('from' => 512000, 'to' => 1048576),
      array('from' => 1048576, 'to' => 2097152),
      array('from' => 2097152, 'to' => 5242880),
      array('from' => 5242880, 'to' => 10485760),
      array('from' => 10485760));

    $scriptStr = 'sum=0; foreach( size : doc[\'aips.sizeOnDisk\'].values) { sum = sum + size }; return sum;';

    $rangeFacet = new \Elastica\Facet\Range('totalSize');
    $rangeFacet->setKeyValueScripts($scriptStr, $scriptStr);
    $rangeFacet->setRanges($sizeRanges);

    $query->addFacet($rangeFacet);

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
      'tmsComponent',
      'aips',
      'artwork'));

    // Filter materials total size, must be a filter to use scripts and
    // must be a filtered query so it happens before faceting
    if ((isset($this->request->totalSizeFrom) && ctype_digit($this->request->totalSizeFrom))
      || (isset($this->request->totalSizeTo) && ctype_digit($this->request->totalSizeTo)))
    {
      $scriptStr = 'sum=0; foreach( size : doc[\'aips.sizeOnDisk\'].values) { sum = sum + size }; ';

      if (isset($this->request->totalSizeFrom) && isset($this->request->totalSizeTo))
      {
        $scriptStr .= $this->request->totalSizeFrom.' < sum && sum < '.$this->request->totalSizeTo.';';
      }
      else if (isset($this->request->totalSizeFrom) && ctype_digit($this->request->totalSizeFrom))
      {
        $scriptStr .= $this->request->totalSizeFrom.' < sum;';
      }
      else if (isset($this->request->totalSizeTo) && ctype_digit($this->request->totalSizeTo))
      {
        $scriptStr .= 'sum < '.$this->request->totalSizeTo.';';
      }

      $script = new \Elastica\Script($scriptStr);
      $scriptFilter = new \Elastica\Filter\Script($scriptStr);
      $filteredQuery = new \Elastica\Query\Filtered($queryBool, $scriptFilter);

      $query->setQuery($filteredQuery);
    }
    else
    {
      $query->setQuery($queryBool);
    }

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
      $this->addItemToArray($result, 'type', get_search_i18n($doc['tmsComponent']['type'][0], 'name'));
      $this->addItemToArray($result, 'install_comments', get_search_i18n($doc['tmsComponent']['installComments'][0], 'content'));
      $this->addItemToArray($result, 'prep_comments', get_search_i18n($doc['tmsComponent']['prepComments'][0], 'content'));
      $this->addItemToArray($result, 'storage_comments', get_search_i18n($doc['tmsComponent']['storageComments'][0], 'content'));
      $this->addItemToArray($result, 'text_entries', get_search_i18n($doc['tmsComponent']['textEntries'][0], 'content'));
      $this->addItemToArray($result, 'count', $doc['tmsComponent']['compCount']);
      $this->addItemToArray($result, 'number', $doc['tmsComponent']['componentNumber']);
      $this->addItemToArray($result, 'lod_name', $this->getFacetLabel('classification', $doc['levelOfDescriptionId']));
      $this->addItemToArray($result, 'artwork_id', $doc['tmsComponent']['artwork']['id']);
      $this->addItemToArray($result, 'artwork_title', get_search_i18n($doc['tmsComponent']['artwork'], 'title'));
      $this->addItemToArray($result, 'artwork_thumbnail', $doc['tmsComponent']['artwork']['thumbnail']);

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

  protected function getFacetLabel($name, $id)
  {
    if ($name === 'classification' || $name === 'type')
    {
      if (null !== $item = QubitTerm::getById($id))
      {
        return $item->getName(array('cultureFallback' => true));
      }
    }

    if ($name === 'dateIngested')
    {
      return $this->dateRangesLabels[$id];
    }
  }
}
