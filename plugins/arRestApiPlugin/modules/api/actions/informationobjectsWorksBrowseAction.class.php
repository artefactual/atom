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

class ApiInformationObjectsWorksBrowseAction extends QubitApiAction
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
    $filterBool = new \Elastica\Filter\Bool;
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
      $culture = sfContext::getInstance()->user->getCulture();

      $queryFields = array(
        'i18n.'.$culture.'.title.autocomplete',
        'identifier',
        'i18n.'.$culture.'.extentAndMedium',
        'i18n.'.$culture.'.physicalCharacteristics',
        'tmsObject.accessionNumber',
        'creators.i18n.'.$culture.'.authorizedFormOfName',
      );

      $queryText = new \Elastica\Query\QueryString($this->request->query);
      $queryText->setFields($queryFields);

      $queryBool->addMust($queryText);
    }

    // Filter selected facets
    $this->filterEsFacetQuery('classification', 'tmsObject.classification.id', $queryBool);
    $this->filterEsFacetQuery('department', 'tmsObject.department.id', $queryBool);

    $this->filterEsRangeFacet('collectedFrom', 'collectedTo', 'tmsObject.dateCollected', $queryBool);
    $this->filterEsRangeFacet('createdFrom', 'createdTo', 'tmsObject.dateCreated', $queryBool);
    $this->filterEsRangeFacet('ingestedFrom', 'ingestedTo', 'aips.createdAt', $queryBool);

    $this->filterEsFacetQuery('format', 'aips.digitalObjects.metsData.mediainfo.generalTracks.format', $queryBool, 'AND', array('noInteger' => true));
    $this->filterEsFacetQuery('videoCodec', 'aips.digitalObjects.metsData.mediainfo.videoTracks.codec', $queryBool, 'AND', array('noInteger' => true));
    $this->filterEsFacetQuery('audioCodec', 'aips.digitalObjects.metsData.mediainfo.audioTracks.codec', $queryBool, 'AND', array('noInteger' => true));
    $this->filterEsFacetQuery('resolution', 'aips.digitalObjects.metsData.mediainfo.videoTracks.resolution', $queryBool);
    $this->filterEsFacetQuery('chromaSubSampling', 'aips.digitalObjects.metsData.mediainfo.videoTracks.chromaSubsampling', $queryBool, 'AND', array('noInteger' => true));
    $this->filterEsFacetQuery('colorSpace', 'aips.digitalObjects.metsData.mediainfo.videoTracks.colorSpace', $queryBool, 'AND', array('noInteger' => true));
    $this->filterEsFacetQuery('sampleRate', 'aips.digitalObjects.metsData.mediainfo.audioTracks.samplingRate', $queryBool);
    $this->filterEsFacetQuery('bitDepth', 'aips.digitalObjects.metsData.mediainfo.videoTracks.bitDepth', $queryBool);

    // Add facets to the query
    $this->facetEsQuery('Terms', 'format', 'aips.digitalObjects.metsData.mediainfo.generalTracks.format', $query);
    $this->facetEsQuery('Terms', 'videoCodec', 'aips.digitalObjects.metsData.mediainfo.videoTracks.codec', $query);
    $this->facetEsQuery('Terms', 'audioCodec', 'aips.digitalObjects.metsData.mediainfo.audioTracks.codec', $query);
    $this->facetEsQuery('Terms', 'resolution', 'aips.digitalObjects.metsData.mediainfo.videoTracks.resolution', $query);
    $this->facetEsQuery('Terms', 'chromaSubSampling', 'aips.digitalObjects.metsData.mediainfo.videoTracks.chromaSubsampling', $query);
    $this->facetEsQuery('Terms', 'colorSpace', 'aips.digitalObjects.metsData.mediainfo.videoTracks.colorSpace', $query);
    $this->facetEsQuery('Terms', 'sampleRate', 'aips.digitalObjects.metsData.mediainfo.audioTracks.samplingRate', $query);
    $this->facetEsQuery('Terms', 'bitDepth', 'aips.digitalObjects.metsData.mediainfo.videoTracks.bitDepth', $query);

    $this->facetEsQuery('Terms', 'classification', 'tmsObject.classification.id', $query);
    $this->facetEsQuery('Terms', 'department', 'tmsObject.department.id', $query);

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

    $this->facetEsQuery('Range', 'dateCollected', 'tmsObject.dateCollected', $query, array('ranges' => $dateRanges));
    $this->facetEsQuery('Range', 'dateCreated', 'tmsObject.dateCreated', $query, array('ranges' => $dateRanges));
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
      'tmsObject',
      'dates',
      'creators',
      'aips'));

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

    // Set filter
    if (0 < count($filterBool->toArray()))
    {
      $query->setFilter($filterBool);
    }

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

  protected function getFacetLabel($name, $id)
  {
    switch ($name)
    {
      case 'classification':
      case 'department':
        if (null !== $item = QubitTerm::getById($id))
        {
          return $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'dateCollected':
      case 'dateCreated':
      case 'dateIngested':
        return $this->dateRangesLabels[$id];

        break;

      case 'format':
      case 'videoCodec':
      case 'audioCodec':
      case 'chromaSubSampling':
      case 'colorSpace':
        return $id;

        break;

      case 'resolution':
      case 'bitDepth':
        return $id.' bits';

        break;

      case 'sampleRate':
        return $id.' Hz';

        break;
    }
  }
}
