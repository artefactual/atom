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

class ApiFixityCreateAction extends QubitApiAction
{
  protected function post($request, $payload)
  {
    $this->report = new QubitFixityReport;
    $this->report->uuid = $request->uuid;

    if (null !== $aip = QubitAip::getByUuid($request->uuid))
    {
      $this->report->aipId = $aip->id;
    }

    foreach ($payload as $field => $value)
    {
      $this->processField($field, $value);
    }

    $this->report->save();

    $this->response->setStatusCode(201);

    return array('id' => $this->report->id);
  }

  protected function processField($field, $value)
  {
    switch ($field)
    {
      case 'success':
      case 'message':
        $this->report->$field = $value;

        break;

      case 'failures':
        $this->report->failures = json_encode($value);

        break;

      case 'started':
        $date = new DateTime();
        $date->setTimestamp($value);
        $this->report->timeStarted = $date;

        break;

      case 'finished':
        $date = new DateTime();
        $date->setTimestamp($value);
        $this->report->timeCompleted = $date;

        break;
    }
  }
}
