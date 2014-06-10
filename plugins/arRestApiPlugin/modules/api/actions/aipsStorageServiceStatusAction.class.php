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

class ApiAipsStorageServiceStatusAction extends QubitApiAction
{
  protected function get($request)
  {
    return $this->getResults($request->status);
  }

  protected function getResults($status)
  {
    $urlPath = 'api/v2/file?format=json&limit=1000&status='. $status;
    $client = new QubitApiStorageServiceClient($urlPath);
    $results = json_decode($client->get($urlPath));

    // parse UUIDs from results
    $uuids = array();
    if (isset($results->objects) && count($results->objects))
    {
      foreach($results->objects as $index => $data)
      {
        array_push($uuids, $results->objects[$index]->uuid);
      }
    }

    return $uuids;
  }
}
