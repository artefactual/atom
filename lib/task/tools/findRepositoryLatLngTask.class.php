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

class findRepositoryLatLngTask extends sfBaseTask
{
  protected $errorCount = 0;

  protected function configure()
  {
    $this->addArguments(array(
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('overwrite', null, sfCommandOption::PARAMETER_NONE, 'Overwrite existing values'),
    ));

    $this->namespace = 'tools';
    $this->name = 'find-repository-latlng';
    $this->briefDescription = 'Search for the lat/lng values of your contacts in Google Maps';
    $this->detailedDescription = <<<EOF
This task won't overwrite existing values unless you use "--overwrite".
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    foreach (QubitContactInformation::getAll() as $item)
    {
      if (!empty($item->latitude) || !empty($item->longitude))
      {
        if (false === $options['overwrite'])
        {
          $this->logSection('latlng', sprintf('Skipping entry (%s, %s)', $item->latitude, $item->longitude));
          continue;
        }
      }

      $address = array();

      foreach (array('streetAddress', 'city', 'region', 'postalCode', 'countryCode') as $field)
      {
        if (isset($item->$field) && !empty($item->$field))
        {
          $address[] = $item->$field;
        }
      }

      if (0 < count($address))
      {
        list($lat, $lng) = $this->getLatLng(implode(', ', $address));

        if (!is_null($lat) && !is_null($lng))
        {
          $item->latitude = $lat;
          $item->longitude = $lng;

          $item->save();

          $this->logSection('latlng', 'Saved!');
          $this->logSection('latlng', ' ');
        }
        else
        {
          $this->errorCount++;
        }
      }
    }

    $this->logSection('latlng', sprintf('Summary: %s errors.', $this->errorCount));
  }

  protected function getLatLng($address)
  {
    $url = sprintf('http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false', urlencode($address));
    if (false === $response = file_get_contents($url))
    {
      $this->logSection('latlng', sprintf('Failed to locate address: %s.', $this->wordLimiter($address)));
    }
    else
    {
      $data = json_decode($response);
      $data = array_pop($data->results);

      $lat = $data->geometry->location->lat;
      $lng = $data->geometry->location->lng;

      $address = $this->wordLimiter($address);
      $address = preg_replace('/[\n\r\f]+/m', ', ', $address);

      $this->logSection('latlng', sprintf('Address: %s', $address));
      if (!is_float($lat) || !is_float($lng))
      {
        $this->logSection('latlng', 'ERROR!');
        $this->logSection('latlng', ' ');

        return;
      }

      $this->logSection('latlng', sprintf('Latitude: %s. Longitude: %s.', $lat, $lng));

      return array($lat, $lng);
    }
  }

  protected function wordLimiter($str, $limit = 100, $end_char = '...')
  {
    if (trim($str) == '')
    {
      return $str;
    }

    preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);

    if (strlen($str) == strlen($matches[0]))
    {
      $end_char = '';
    }

    return rtrim($matches[0]).$end_char;
  }
}
