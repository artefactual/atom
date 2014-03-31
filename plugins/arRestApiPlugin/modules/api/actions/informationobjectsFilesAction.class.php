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

      $item = array(
        'id' => $hit->getId(),
        'slug' => $doc['slug'],
        'mediaTypeId' => $doc['digitalObject']['mediaTypeId'],
        'mimeType' => $doc['digitalObject']['mimeType'],
        'byteSize' => $doc['digitalObject']['byteSize'],
        'thumbnailPath' => image_path($doc['digitalObject']['thumbnailPath'], true),
        'filename' => get_search_i18n($doc, 'title')
      );

      if ($doc['originalRelativePathWithinAip'])
      {
        $item['originalRelativePathWithinAip'] = $doc['originalRelativePathWithinAip'];
      }

      $data[] = $item;
    }

    return array(
      'results' => $data,
      'total' => $resultSet->getTotalHits()
    );
  }
}
