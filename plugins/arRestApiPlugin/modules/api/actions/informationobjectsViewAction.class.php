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

    return array(
      'id' => $io->id,
      'level_of_description_id' => $io->levelOfDescriptionId);
  }
}
