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
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    $this->query = new \Elastica\Query();
    $this->queryBool = new \Elastica\Query\Bool();

    // Query
    $query = new \Elastica\Query\Term;
    $query->setTerm('uuid', $request->uuid);
    $this->queryBool->addMust($query);
    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitAip')->search($this->query);
    $results = $resultSet->getResults();

    if (0 == count($results))
    {
      // TODO? Add errors to response
      $this->forward404();

      return;
    }

    $doc = $results[0]->getData();

    // Build array from result
    $aip = array();
    $aip['id'] = $results[0]->getId();
    $aip['name'] = $doc['filename'];
    $aip['uuid'] = $doc['uuid'];
    $aip['size'] = $doc['sizeOnDisk'];
    $aip['digitalObjectCount'] =  $doc['digitalObjectCount'];
    $aip['subdirectoryCount'] =  'TODO';
    $aip['description'] =  'TODO';
    $aip['created_at'] = $doc['createdAt'];
    $aip['class'] = get_search_i18n($doc['class'][0], 'name');
    $aip['part_of']['id'] = $doc['partOf'][0]['id'];
    $aip['part_of']['title'] = get_search_i18n($doc['partOf'][0], 'title');
    $aip['digitalObjects'] = $doc['digitalObjects'];

    return $aip;
  }
}
