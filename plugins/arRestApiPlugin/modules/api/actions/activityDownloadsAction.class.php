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

class ApiActivityDownloadsAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $data['results'] = $this->getResults();

    return $data;
  }

  protected function getResults()
  {
    $results = array();
    $limit = ($this->request->limit) ? $this->request->limit : 10;

    // pull download log data in reverse chronological order
    $criteria = new Criteria;
    $criteria->add(QubitProperty::NAME, 'aip_file_download');
    $criteria->addJoin(QubitProperty::ID, QubitPropertyI18n::ID);
    $criteria->addDescendingOrderByColumn(QubitPropertyI18n::VALUE);

    $criteria->setLimit($limit);

    $properties = QubitProperty::get($criteria);

    // deserialize property values
    foreach($properties as $property)
    {
      $columns = explode('|', $property->value);
      $download = array(
        'date' => $columns[0],
        'username' => $columns[1],
        'reason' => trim($columns[2])
      );

      if ($columns[3])
      {
        $download['file'] = $columns[3];
      }

      $results[] = $download;
    }

    return $results;
  }
}
