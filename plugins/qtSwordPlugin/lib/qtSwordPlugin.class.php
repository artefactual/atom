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

class qtSwordPlugin
{
  public static function saveRequestContent()
  {
    $filename = tempnam('/tmp', 'php_qubit_');

    $file = fopen($filename, 'w');
    $source = fopen("php://input", 'r');

    while ($kb = fread($source, 1024))
    {
      fwrite($file, $kb, 1024);
    }

    fclose($file);
    fclose($source);

    return $filename;
  }

  public static function addDataToCreationEvent($event, $data)
  {
    if ($data['actorName'])
    {
      if ($data['actorDate'])
      {
        $actor = QubitFlatfileImport::createOrFetchActor($data['actorName'], array('datesOfExistence' => $options['actorDate']));
      }
      else
      {
        $actor = QubitFlatfileImport::createOrFetchActor($data['actorName']);
      }

      $event->actorId = $actor->id;
    }

    if ($data['date'])
    {
      $date = $data['date'];

      // Normalize expression of date range
      $date = str_replace('/', '|', $date);
      $date = str_replace(' - ', '|', $date);

      if (substr_count($date, '|'))
      {
        // Date is a range
        $dates = explode('|', $date);

        // If date is a range, set start/end dates
        if (count($dates) == 2)
        {
          $parsedDates = array();

          // Parse each component date
          foreach($dates as $dateItem)
          {
            array_push($parsedDates, QubitFlatfileImport::parseDate($dateItem));
          }

          $event->startDate = $parsedDates[0];
          $event->endDate = $parsedDates[1];

          // if date range is similar to ISO 8601 then make it a normal date range
          if ($this->likeISO8601Date(trim($dates[0])))
          {
            if ($event->startDate == $event->endDate)
            {
              $date = $event->startDate;
            }
            else
            {
              $date = $event->startDate.'|'.$event->endDate;
            }
          }
        }

        // If date is a single ISO 8601 date then truncate off time
        if ($this->likeISO8601Date(trim($event->date)))
        {
          $date = substr(trim($event->date), 0, 10);
        }

        // Make date range indicator friendly
        $event->date = str_replace('|', ' - ', $date);
      }
      else
      {
        // Date isn't a range
        $event->date = $date;
        $event->startDate = QubitFlatfileImport::parseDate($date);
        $event->endDate = QubitFlatfileImport::parseDate($date);
      }
    }

    $event->save();
  }
}
