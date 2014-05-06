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
      $queryId = new \Elastica\Query\Bool;
      // $queryId->addShould(new \Elastica\Query\Term(array('_id' => $request->id)));
      $queryId->addShould(new \Elastica\Query\Term(array('ancestors' => $request->id)));
      $queryBool->addMust($queryId);
    }

    $queryDo = new \Elastica\Query\Term;
    $queryDo->setTerm('hasDigitalObject', true);
    $queryBool->addMust($queryDo);

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
      $this->addItemToArray($item, 'media_type_id', $doc['digitalObject']['mediaTypeId']);
      $this->addItemToArray($item, 'mime_type', $doc['digitalObject']['mimeType']);
      $this->addItemToArray($item, 'byte_size', $doc['digitalObject']['byteSize']);
      $this->addItemToArray($item, 'thumbnail_path', image_path($doc['digitalObject']['thumbnailPath'], true));
      $this->addItemToArray($item, 'master_path', image_path($doc['digitalObject']['masterPath'], true));

      // Why "aip" is a list in the ES document?
      $this->addItemToArray($item, 'aip_uuid', $doc['aip'][0]['uuid']);
      $this->addItemToArray($item, 'aip_title', $doc['aip'][0]['filename']);
      $this->addItemToArray($item, 'original_relative_path_within_aip', $doc['originalRelativePathWithinAip']);

      $data[] = $item;
    }

    return array(
      'results' => $data,
      'total' => $resultSet->getTotalHits()
    );
  }
}
