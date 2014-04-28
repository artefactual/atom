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

class ApiInformationObjectsFilesBrowseAction extends QubitApiAction
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

    // Filter to TMS Objects (artworks)
    $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_digital_object_id'))));

    // Filter query
    if (isset($this->request->query) && 1 !== preg_match('/^[\s\t\r\n]*$/', $this->request->query))
    {
      $culture = sfContext::getInstance()->user->getCulture();

      $queryFields = array(
        'i18n.'.$culture.'.title.autocomplete',
        'identifier',
        'aipUuid',
        'aipName'
      );

      $queryText = new \Elastica\Query\QueryString($this->request->query);
      $queryText->setFields($queryFields);

      $queryBool->addMust($queryText);
    }

    // Filter selected facets
    $this->filterEsRangeFacet('sizeFrom', 'sizeTo', 'metsData.size', $queryBool);
    $this->filterEsRangeFacet('ingestedFrom', 'ingestedTo', 'metsData.dateIngested', $queryBool);

    // Add facets to the query
    $sizeRanges = array(
      array('to' => 512000),
      array('from' => 512000, 'to' => 1048576),
      array('from' => 1048576, 'to' => 2097152),
      array('from' => 2097152, 'to' => 5242880),
      array('from' => 5242880, 'to' => 10485760),
      array('from' => 10485760));

    $this->facetEsQuery('Range', 'size', 'metsData.size', $query, array('ranges' => $sizeRanges));

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

    $this->facetEsQuery('Range', 'dateIngested', 'metsData.dateIngested', $query, array('ranges' => $dateRanges));

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
      'dates',
      'creators',
      'metsData',
      'digitalObject',
      'aipUuid',
      'aipName',
      'originalRelativePathWithinAip'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    // Build array from results
    $results = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      $this->addItemToArray($result, 'identifier', $doc['identifier']);
      $this->addItemToArray($result, 'filename', get_search_i18n($doc, 'title'));
      $this->addItemToArray($result, 'slug', $doc['slug']);
      $this->addItemToArray($result, 'media_type_id', $doc['digitalObject']['mediaTypeId']);
      $this->addItemToArray($result, 'byte_size', $doc['digitalObject']['byteSize']);
      $this->addItemToArray($result, 'size_in_aip', $doc['metsData']['size']);
      $this->addItemToArray($result, 'date_ingested', $doc['metsData']['dateIngested']);
      $this->addItemToArray($result, 'mime_type', $doc['digitalObject']['mimeType']);
      $this->addItemToArray($result, 'thumbnail_path', image_path($doc['digitalObject']['thumbnailPath'], true));
      $this->addItemToArray($result, 'browse_thumbnail_path', $doc['digitalObject']['thumbnailPath']);

      if (isset($doc['digitalObject']['mediaTypeId']) && !empty($doc['digitalObject']['mediaTypeId']))
      {
        $this->addItemToArray($result, 'media_type', $this->getFacetLabel('mediaType', $doc['digitalObject']['mediaTypeId']));
      }

      $this->addItemToArray($result, 'aip_uuid', $doc['aipUuid']);
      $this->addItemToArray($result, 'aip_title', $doc['aipName']);
      $this->addItemToArray($result, 'original_relative_path_within_aip', $doc['originalRelativePathWithinAip']);

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
    if ($name === 'mediaType')
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
