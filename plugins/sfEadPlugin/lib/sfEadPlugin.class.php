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

/**
 * @package    AccesstoMemory
 * @subpackage sfEadPlugin
 * @author     David Juhasz <david@artefactual.com>
 */
class sfEadPlugin
{
  public
    $resource;

  public function __construct(QubitInformationObject $resource)
  {
    $this->resource = $resource;
  }

  public function __get($name)
  {
    return $this->resource->$name;
  }

  public function subjectHasNonBlankSourceNotes(&$subject)
  {
    $hasNonBlankNotes = false;

    $notes = $subject->getTerm()->getSourceNotes();
    foreach($notes as $note)
    {
      if ($note != '')
      {
        $hasNonBlankNotes = true;
      }
    }

    return $hasNonBlankNotes;
  }

  public function renderEadId()
  {
    $countryCode = $mainAgencyCode = '';

    if (null !== $this->resource->getRepository(array('inherit' => true)))
    {
      if (null !== $country = $this->resource->getRepository(array('inherit' => true))->getCountryCode())
      {
        $countryCode = " countrycode=\"$country\"";
      }

      if (null !== $agency = $this->resource->getRepository(array('inherit' => true))->getIdentifier())
      {
        if (isset($country))
        {
          $agency = $country.'-'.$agency;
        }

        $mainAgencyCode = " mainagencycode=\"$agency\"";
      }
    }

    $url = url_for(array($this->resource, 'module' => 'informationobject', 'sf_format' => 'xml'), $absolute = true);

    if (null === $identifier = $this->resource->descriptionIdentifier)
    {
      $identifier = url_for($this->resource, $absolute = true);
    }

    return "<eadid identifier=\"$identifier\"$countryCode$mainAgencyCode url=\"$url\" encodinganalog=\"Identifier\">{$this->resource->identifier}</eadid>";
  }

  public function renderEadNormalizedDate($date)
  {
    return str_replace('-', '', $date);
  }

  public static function renderEadDenormalizedDate($date)
  {
    $dateData   = date_parse($date);

    $dateOutput = $dateData['year'];

    if ($dataData['month'])
    {
      $dateOutput .= '-'. $dateData['month'] .'-';

      // if a month is specified, add day specification as well
      $dateOutput .= ($dateData['day']) ? $dateData['day'] : '01';
    }

    return $dateOutput;
  }

  public static function parseEadDenormalizedDateData($date)
  {
    $parsedData = array();
    $dates = explode('/', $date);

    $parsedData['start'] = sfEadPlugin::renderEadDenormalizedDate($dates[0]);

    if (count($dates) > 1)
    {
      $parsedData['end'] = sfEadPlugin::renderEadDenormalizedDate($dates[1]);
    }

    return $parsedData;
  }

  public function renderEadDateFromEvent($eventType, $event)
  {
    $output = '<date type="'. $eventType .'" ';

    // create normalized date/date range
    if ($event->startDate || $event->endDate)
    {
      $normalized = ($event->startDate) ? $this->renderEadNormalizedDate($event->startDate) : '';

      if ($event->endDate)
      {
        $normalized .= ($event->startDate) ? '/' : '';
        $normalized .= $this->renderEadNormalizedDate($event->endDate);
      }
    }

    // add normalized portion of date tag if it exists
    $output .= (isset($normalized)) ? 'normal="'. $normalized .'" ' : '';

    $output .= '>'. $event->date .'</date>';

    return $output;
  }
}
