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

class ApiInformationObjectsMetsAction extends QubitApiAction
{
  protected function get($request)
  {
    return $this->getResults();
  }

  protected function getResults()
  {
    $query = new \Elastica\Query;
    $query->setQuery(new \Elastica\Query\Term(array('_id' => $this->request->id)));
    $query->setFields(array('metsData'));

    $results = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    if (false === $doc = $results->current())
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $data = $doc->getData();

    if (!isset($data['metsData']))
    {
      throw new QubitApi404Exception('METS data not found');
    }

    return $data['metsData'];
  }
}
