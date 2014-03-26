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

    $allowedLevels = array(
      sfConfig::get('app_drmc_lod_artwork_record_id'),
      sfConfig::get('app_drmc_lod_archival_master_id'),
      sfConfig::get('app_drmc_lod_artist_supplied_master_id'),
      sfConfig::get('app_drmc_lod_artist_verified_proof_id'),
      sfConfig::get('app_drmc_lod_exhibition_format_id'),
      sfConfig::get('app_drmc_lod_miscellaneous_id'),
      sfConfig::get('app_drmc_lod_component_id')
    );

    if (!in_array($this->io->levelOfDescriptionId, $allowedLevels))
    {
      throw new QubitApiException('TMS data not available for this level of description');
    }

    switch ($this->io->levelOfDescriptionId)
    {
      case sfConfig::get('app_drmc_lod_artwork_record_id'):
        $tmsData = $this->getTmsObjectData();

        break;

      default:
        $tmsData = $this->getTmsComponentData();

        break;
    }

    return $tmsData;
  }

  protected function getTmsObjectData()
  {
    $result = array();

    $this->addItemToArray($result, 'title', $this->io->getTitle(array('sourceCulture' => true)));
    $this->addItemToArray($result, 'accessionNumber', $this->getProperty('ObjectNumber'));
    $this->addItemToArray($result, 'objectId', $this->io->identifier);

    if (0 < count($creationEvents = $this->io->getCreationEvents()))
    {
      $this->addItemToArray($result, 'year', $creationEvents[0]->getDate(array('sourceCulture' => true)));

      if (null !== $actor = $creationEvents[0]->actor)
      {
        $this->addItemToArray($result, 'artist', $actor->getAuthorizedFormOfName(array('sourceCulture' => true)));
        $this->addItemToArray($result, 'artistDate', $actor->datesOfExistence);
      }
    }

    if (0 < count($termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_taxonomy_classifications_id'))))
    {
      $this->addItemToArray($result, 'classification', $termRelations[0]->term->getName(array('sourceCulture' => true)));
    }

    if (0 < count($termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_taxonomy_departments_id'))))
    {
      $this->addItemToArray($result, 'department', $termRelations[0]->term->getName(array('sourceCulture' => true)));
    }

    $this->addItemToArray($result, 'medium', $this->io->extentAndMedium);
    $this->addItemToArray($result, 'dimensions', $this->io->physicalCharacteristics);

    $this->addItemToArray($result, 'thumbnail', $this->getProperty('Thumbnail'));
    $this->addItemToArray($result, 'fullImage', $this->getProperty('FullImage'));

    $result['type'] = 'Object';

    // TODO: Description

    return $result;
  }

  protected function getTmsComponentData()
  {
    $result = array();

    $this->addItemToArray($result, 'componentName', $this->io->getTitle(array('sourceCulture' => true)));
    $this->addItemToArray($result, 'componentID', $this->io->identifier);

    if (0 < count($termRelations = $this->io->getTermRelations(sfConfig::get('app_drmc_taxonomy_component_types_id'))))
    {
      $this->addItemToArray($result, 'componentType', $termRelations[0]->term->getName(array('sourceCulture' => true)));
    }

    if (0 < count($notes = $this->io->getNotesByType($options = array('noteTypeId' => sfConfig::get('app_drmc_term_installcomments_id')))))
    {
      $this->addItemToArray($result, 'installComments', $notes[0]->getContent(array('sourceCulture' => true)));
    }

    if (0 < count($notes = $this->io->getNotesByType($options = array('noteTypeId' => sfConfig::get('app_drmc_term_prepcomments_id')))))
    {
      $this->addItemToArray($result, 'prepComments', $notes[0]->getContent(array('sourceCulture' => true)));
    }

    if (0 < count($notes = $this->io->getNotesByType($options = array('noteTypeId' => sfConfig::get('app_drmc_term_storagecomments_id')))))
    {
      $this->addItemToArray($result, 'storageComments', $notes[0]->getContent(array('sourceCulture' => true)));
    }

    if (0 < count($notes = $this->io->getNotesByType($options = array('noteTypeId' => QubitTerm::GENERAL_NOTE_ID))))
    {
      $this->addItemToArray($result, 'textEntries', $notes[0]->getContent(array('sourceCulture' => true)));
    }

    $this->addItemToArray($result, 'physDesc', $this->io->extentAndMedium);
    $this->addItemToArray($result, 'dimensions', $this->io->physicalCharacteristics);

    $this->addItemToArray($result, 'compCount', $this->getProperty('CompCount'));
    $this->addItemToArray($result, 'componentNumber', $this->getProperty('ComponentNumber'));

    $result['type'] = 'Component';

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
