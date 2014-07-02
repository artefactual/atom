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

class ApiRecoverRequestAction extends QubitApiAction
{
  protected function post($request)
  {
    if (null == $report = QubitFixityReport::getById($request->id))
    {
      throw new QubitApi404Exception('Fixity report not found');
    }

    if (null === $report->uuid)
    {
      throw new QubitApi404Exception('UUID not found');
    }

    // Check that no unresolved recovery requests exists for this AIP
    $criteria = new Criteria;

    $criteria->add(QubitFixityRecovery::AIP_ID, $report->aipId);
    $criteria->add(QubitFixityRecovery::TIME_COMPLETED, null, Criteria::ISNULL);

    if (null != QubitFixityRecovery::getOne($criteria))
    {
      throw new QubitApiBadRequestException('Recovery has already been initiated for this AIP');
    }

    $urlPath = 'api/v2/file/'. $report->uuid .'/recover_aip/?format=json';
    $client = new QubitApiStorageServiceClient($urlPath);

    $postData = array(
      "event_reason" => 'Request from DRMC',
      "pipeline" => $client->config['ARCHIVEMATICA_SS_PIPELINE_UUID'],
      "user_id" => $this->context->user->getUserID(),
      "user_email" => $this->context->user->user->email
    );

    $resultJSON = $client->post($urlPath, json_encode($postData));
    $results = json_decode($resultJSON);

    if ($results == null)
    {
      $results = array(
        'message' => 'Error relaying recovery request.'
      );
    } else {
      // Record start of recovery process
      $recovery = new QubitFixityRecovery();
      $recovery->aipId = $report->aipId;
      $recovery->userId = $this->context->user->getUserID();
      $recovery->storageServiceEventId = $results->id;
      $recovery->timeStarted = date('Y-m-d H:i:s');
      $recovery->save();
    }

    return $results;
  }
}
