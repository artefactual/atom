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
 * Digital Object metadata component
 *
 * @package    AccesstoMemory
 * @subpackage digitalobject
 * @author     Mike G <mikeg@artefactual.com>
 */
class DigitalObjectMetadataComponent extends sfComponent
{
  public function execute($request)
  {
    $id = $this->infoObj->id;
    $this->denyFileNameByPremis = false;

    // Special case: If all digital object representations are
    // denied by premis, we will hide the filename when displaying
    // the digital object metadata for security reasons.
    if (!QubitGrantedRight::checkPremis($id, 'readThumb') &&
        !QubitGrantedRight::checkPremis($id, 'readReference') &&
        !QubitGrantedRight::checkPremis($id, 'readMaster'))
    {
      $this->denyFileNameByPremis = true;
    }

    // Provide Google Maps API key to template
    $this->googleMapsApiKey = sfConfig::get('app_google_maps_api_key');

    // Provide latitude to template
    $latitudeProperty = $this->infoObj->digitalObjects[0]->getPropertyByName('latitude');
    $this->latitude = $latitudeProperty->value;

    // Provide longitude to template
    $longitudeProperty = $this->infoObj->digitalObjects[0]->getPropertyByName('longitude');
    $this->longitude = $longitudeProperty->value;
  }
}
