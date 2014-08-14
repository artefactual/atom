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

class ApiInformationObjectsFilesAction extends QubitApiAction
{
  protected function get($request)
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $this->prepareEsSorting($query, array(
      'name' => 'filename',
      'size' => 'byteSize'));

    // Find document give its id and optionally its descendants
    if (isset($request->excludeDescendants) && true === filter_var($request->excludeDescendants, FILTER_VALIDATE_BOOLEAN))
    {
      $queryBool->addMust(new \Elastica\Query\Term(array('_id' => $request->id)));
    }
    else
    {
      $queryBool->addMust(new \Elastica\Query\Term(array('ancestors' => $request->id)));
    }

    $queryBool->addMust(new \Elastica\Query\Term(array('levelOfDescriptionId' => sfConfig::get('app_drmc_lod_digital_object_id'))));

    // Assign query
    $query->setQuery($queryBool);

    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
      $results = $resultSet->getResults();
    }
    catch (\Elastica\Exception\NotFoundException $e)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $data = array();

    foreach ($results as $hit)
    {
      $doc = $hit->getData();

      $item = array();

      $item['id'] = (int)$hit->getId();

      $this->addItemToArray($item, 'slug', $doc['slug']);
      $this->addItemToArray($item, 'filename', get_search_i18n($doc, 'title'));

      $this->addItemToArray($item, 'puid', $doc['metsData']['puid']);
      $this->addItemToArray($item, 'mime_type', $doc['metsData']['mimeType']);
      $this->addItemToArray($item, 'byte_size', $doc['metsData']['size']);

      // Not needed?
      $this->addItemToArray($item, 'media_type_id', $doc['digitalObject']['mediaTypeId']);

      if (!empty($doc['digitalObject']['thumbnailPath']))
      {
        $this->addItemToArray($item, 'thumbnail_path', image_path($doc['digitalObject']['thumbnailPath'], true));
      }

      if (!empty($doc['digitalObject']['masterPath']))
      {
        $this->addItemToArray($item, 'master_path', image_path($doc['digitalObject']['masterPath'], true));
      }

      $this->addItemToArray($item, 'aip_uuid', $doc['aipUuid']);
      $this->addItemToArray($item, 'aip_title', $doc['aipName']);
      $this->addItemToArray($item, 'original_relative_path_within_aip', $doc['originalRelativePathWithinAip']);

      // Add mime_type and size to AIP METS file
      // from digital object instead of metsData. See #7002
      if (isset($item['filename']) && isset($item['aip_uuid'])
       && false !== strpos($item['filename'], $item['aip_uuid']))
      {
        $this->addItemToArray($item, 'mime_type', $doc['digitalObject']['mimeType']);
        $this->addItemToArray($item, 'byte_size', $doc['digitalObject']['byteSize']);
      }

      $data[] = $item;
    }

    return array(
      'results' => $data,
      'total' => $resultSet->getTotalHits()
    );
  }
}
