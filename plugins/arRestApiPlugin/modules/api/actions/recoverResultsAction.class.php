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

class ApiRecoverResultsAction extends QubitApiAction
{
  protected function post($request, $payload)
  {
    // Use storage service event ID to locate recovery record
    $criteria = new Criteria;
    $criteria->add(QubitFixityRecovery::STORAGE_SERVICE_EVENT_ID, $payload->event_id);

    if (null == $recovery = QubitFixityRecovery::getOne($criteria))
    {
      throw new QubitApi404Exception('Fixity recovery not found');
    }

    if ($recovery->timeCompleted)
    {
      throw new QubitApiBadRequestException('Recovery has already been completed');
    }

    // Mark recovery record with results
    $recovery->success = $payload->success;
    $recovery->message = $payload->message;
    $recovery->timeCompleted = date('Y-m-d H:i:s'); 

    $recovery->save();

    $results = array(
      'message' => 'DRMC recovery status updated.'
    );

    return $results;
  }
}
