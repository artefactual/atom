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

class APIAIPsViewAction extends QubitAPIAction
{
  protected function get($request)
  {
    $data = $this->getResults();

    return $data;
  }

  protected function getResults()
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;

    // Query
    $queryTerm = new \Elastica\Query\Term;
    $queryTerm->setTerm('uuid', $this->request->uuid);
    $queryBool->addMust($queryTerm);

    // Assign query
    $query->setQuery($queryBool);

    $results = QubitSearch::getInstance()->index->getType('QubitAip')->search($query)->getResults();
    if (1 > count($results))
    {
      throw new sfError404Exception();
    }

    $doc = $results[0]->getData();

    // Build array from result
    $aip = array();
    $aip['id'] = $results[0]->getId();
    $aip['name'] = $doc['filename'];
    $aip['uuid'] = $doc['uuid'];
    $aip['size'] = $doc['sizeOnDisk'];
    $aip['type']['id'] = $doc['type']['id'];
    $aip['type']['name'] = get_search_i18n($doc['type'], 'name');
    $aip['part_of']['id'] = $doc['partOf']['id'];
    $aip['part_of']['title'] = get_search_i18n($doc['partOf'], 'title');
    $aip['digitalObjectCount'] =  $doc['digitalObjectCount'];
    if (isset($doc['digitalObjects']))
    {
      $aip['digitalObjects'] = $doc['digitalObjects'];
    }
    $aip['created_at'] = $doc['createdAt'];

    return $aip;
  }
}
