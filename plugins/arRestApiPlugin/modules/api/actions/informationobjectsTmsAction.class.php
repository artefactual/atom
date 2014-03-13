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

class ApiInformationObjectsTmsAction extends QubitApiAction
{
  protected function get($request)
  {
    $this->io = QubitInformationObject::getById($request->id);

    if (null === $this->io)
    {
      throw new QubitApi404Exception('Information object not found');
    }

    $allowedLevels = array(sfConfig::get('app_drmc_lod_artwork_record_id'), sfConfig::get('app_drmc_lod_digital_object_id'));
    if (!in_array($this->io->levelOfDescriptionId, $allowedLevels))
    {
      throw new QubitApiException('TMS data not available for this level of description');
    }

    switch ($this->io->levelOfDescriptionId)
    {
      case sfConfig::get('app_drmc_lod_artwork_record_id'):
        $tmsData = $this->getTmsObjectData();

        break;

      case sfConfig::get('app_drmc_lod_digital_object_id'):
        $tmsData = $this->getTmsComponentData();

        break;
    }

    return $tmsData;
  }

  protected function getTmsObjectData()
  {
    $result = array();

    $result['title'] = $this->io->getTitle(array('sourceCulture' => true));
    $result['accessionNumber'] = $this->getProperty('ObjectNumber');
    $result['objectId'] = $this->io->identifier;

    $creationEvents = $this->io->getCreationEvents();
    $result['year'] = $creationEvents[0]->getDate(array('sourceCulture' => true));
    $result['artist'] = $creationEvents[0]->actor->getAuthorizedFormOfName(array('sourceCulture' => true));
    $result['artistDate'] = $creationEvents[0]->actor->datesOfExistence;

    $termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_lod_classifications_id'));
    $result['classification'] = $termRelations[0]->term->getName(array('sourceCulture' => true));

    $termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_lod_departments_id'));
    $result['department'] = $termRelations[0]->term->getName(array('sourceCulture' => true));

    $result['medium'] = $this->io->extentAndMedium;
    $result['dimensions'] = $this->io->physicalCharacteristics;

    $result['thumbnail'] = $this->getProperty('Thumbnail');
    $result['fullImage'] = $this->getProperty('FullImage');

    $result['description'] = '';

    return $result;
  }

  protected function getTmsComponentData()
  {
    $result = array();

    $result['title'] = $this->io->getTitle(array('sourceCulture' => true));
    $result['accessionNumber'] = $this->getProperty('ObjectNumber');
    $result['objectId'] = $this->io->identifier;

    $creationEvents = $this->io->getCreationEvents();
    $result['year'] = $creationEvents[0]->getDate(array('sourceCulture' => true));
    $result['artist'] = $creationEvents[0]->actor->getAuthorizedFormOfName(array('sourceCulture' => true));
    $result['artistDate'] = $creationEvents[0]->actor->datesOfExistence;

    $termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_lod_classifications_id'));
    $result['classification'] = $termRelations[0]->term->getName(array('sourceCulture' => true));

    $termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_lod_departments_id'));
    $result['department'] = $termRelations[0]->term->getName(array('sourceCulture' => true));

    $result['medium'] = $this->io->extentAndMedium;
    $result['dimensions'] = $this->io->physicalCharacteristics;

    $result['thumbnail'] = $this->getProperty('Thumbnail');
    $result['fullImage'] = $this->getProperty('FullImage');

    $result['description'] = '';

    return $result;
  }

  protected function getProperty($name)
  {
    $criteria = new Criteria;
    $this->io->addPropertysCriteria($criteria);
    $criteria->add(QubitProperty::NAME, $name);

    $property = QubitProperty::getOne($criteria);

    if (null !== $property)
    {
      return $property->getValue(array('sourceCulture' => true));
    }
  }
}
