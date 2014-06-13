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

class ApiAipsRecoverAction extends QubitApiAction
{
  protected function post($request)
  {
    if (null === $aip = QubitAip::getByUuid($request->uuid))
    {
      throw new QubitApi404Exception('UUID not found');
    }

    $urlPath = 'api/v2/file/'. $request->uuid .'/recover_aip/?format=json';
    $client = new QubitApiStorageServiceClient($urlPath);

    $postData = array(
      "event_reason" => 'Request from DRMC',
      "pipeline" => $client->config['ARCHIVEMATICA_SS_PIPELINE_UUID'],
      "user_id" => $this->context->user->getUserID(),
      "user_email" => $this->context->user->user->email
    );

    $results = $client->post($urlPath, json_encode($postData));

    return json_decode($results);
  }
}
