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

class APIInformationObjectsViewAction extends QubitAPIAction
{
  protected function get($request)
  {
    $data = $this->getInformationObject();

    return $data;
  }

  protected function getInformationObject()
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    if (QubitInformationObject::ROOT_ID === (int)$this->request->id)
    {
      return $this->forward404('Information object not found');
    }

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::ID, $this->request->id);
    if (isset($this->request->level_id) and true === ctype_digit($this->request->level_id))
    {
      $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, $this->request->level_id);
    }

    if (null === $io = QubitInformationObject::getById($this->request->id))
    {
      return $this->forward404('Information object not found');
    }

    $data = array(
      'id' => $io->id,
      'level_of_description_id' => $io->levelOfDescriptionId,
      'title' => $io->getTitle(array('cultureFallback' => true)));

    if (sfConfig::get('app_drmc_lod_artwork_record_id') == $io->levelOfDescriptionId)
    {
      $data['tms'] = array(
        'accessionNumber' => '1098.2005.a-c',
        'objectId' => '100620',
        'title' => 'Play Dead; Real Time',
        'year' => '2003',
        'artist' => 'Douglas Gordon',
        'classification' => 'Installation',
        'medium' => 'Three-channel video',
        'dimensions' => '19:11 min, 14:44 min. (on larger screens), 21:58 min. (on monitor). Minimum Room Size: 24.8m x 13.07m',
        'description' => 'Exhibition materials: 3 DVD and players, 2 projectors, 3 monitor, 2 screens. The complete work is a three-screen piece, consisting of '
      );
    }

    return $data;
  }
}
