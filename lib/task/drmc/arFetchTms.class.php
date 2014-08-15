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

class arFetchTms
{
  protected
    $tmsBaseUrl,
    $statusMapping,
    $logger;

  public function __construct()
  {
    $this->tmsBaseUrl = sfConfig::get('app_drmc_tms_url');

    // Mapping from TMS status to level of descriptions
    $this->statusMapping = array(
      'Archival'               => sfConfig::get('app_drmc_lod_archival_master_id'),
      'Archival submaster'     => sfConfig::get('app_drmc_lod_archival_master_id'),
      'Artist master'          => sfConfig::get('app_drmc_lod_artist_supplied_master_id'),
      'Artist proof'           => sfConfig::get('app_drmc_lod_artist_verified_proof_id'),
      'Duplication master'     => sfConfig::get('app_drmc_lod_component_id'),
      'Exhibition copy'        => sfConfig::get('app_drmc_lod_exhibition_format_id'),
      'Miscellaneous other'    => sfConfig::get('app_drmc_lod_miscellaneous_id'),
      'Repository File Source' => sfConfig::get('app_drmc_lod_component_id'),
      'Research copy'          => sfConfig::get('app_drmc_lod_component_id')
    );

    $this->logger = sfContext::getInstance()->getLogger();
  }

  protected function getTmsData($path)
  {
    $data = null;
    $url = $this->tmsBaseUrl.$path;

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FAILONERROR => true,
        CURLOPT_URL => $url));

    if (false === $resp = curl_exec($curl))
    {
      $this->logger->info('arFetchTms - Error getting Tombstone data: '.curl_error($curl));
      $this->logger->info('arFetchTms - URL: '.$url);
    }
    else
    {
      $data = json_decode($resp, true);
    }

    curl_close($curl);

    return $data;
  }

  public function getTmsObjectData($tmsObject, $tmsObjectId)
  {
    $tmsComponentsIds = $creation = array();
    $artworkThumbnail = null;

    // Get TMS object ID from METS file
    /*
    $this->document->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');
    $this->document->registerXPathNamespace('f', 'http://hul.harvard.edu/ois/xml/ns/fits/fits_output');

    foreach ($this->document->xpath('//m:amdSec/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:EVENT"]/m:xmlData/s:event') as $item)
    {
      $item->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');

      if (0 < count($value = $item->xpath('s:eventType')) && (string)$value[0] == 'registration')
      {
        if (0 < count($value = $item->xpath('s:eventOutcomeInformation/s:eventOutcomeDetail/s:eventOutcomeDetailNote')) && 0 == strpos((string)$value[0], 'accession#'))
        {
          $tmsId = substr((string)$value[0], 10);

          break;
        }
      }
    }
    */

    // Request object from TMS API
    if (null !== $data = $this->getTmsData('/GetTombstoneDataRest/ObjectID/'.$tmsObjectId))
    {
      $data = $data['GetTombstoneDataRestIdResult'];

      foreach ($data as $name => $value)
      {
        if (!isset($value) || 0 == strlen($value))
        {
          continue;
        }

        switch ($name)
        {
          // Info. object fields
          case 'Dimensions':
            $tmsObject->physicalCharacteristics = $value;

            break;

          case 'Medium':
            $tmsObject->extentAndMedium = $value;

            break;

          case 'ObjectID':
            $tmsObject->identifier = $value;

            break;

          case 'Title':
            $tmsObject->title = $value;

            break;

          // Properties
          case 'AccessionISODate':
          case 'ClassificationID':
          case 'ConstituentID':
          case 'DepartmentID':
          case 'LastModifiedCheckDate':
          case 'ImageID':
          case 'ObjectNumber':
          case 'ObjectStatusID':
          case 'SortNumber':
            $this->addOrUpdateProperty($name, $value, $tmsObject);

            break;

          // Object/term relations
          case 'Classification':
          case 'Department':
            $this->addOrUpdateObjectTermRelation($name, $value, $tmsObject);

            break;

          // Creation event
          case 'Dated':
            $creation['date'] = $value;

            break;

          case 'DisplayName':
            $creation['actorName'] = $value;

            break;

          case 'DisplayDate':
            $creation['actorDate'] = $value;

            break;

          // Digital object
          case 'FullImage':
            // Encode filename in the URL
            $filename = basename(parse_url($value, PHP_URL_PATH));
            $value = str_replace($filename, rawurlencode($filename), $value);

            // Update digital object if exists
            if (null !== $digitalObject = $tmsObject->getDigitalObject())
            {
              $criteria = new Criteria;
              $criteria->add(QubitDigitalObject::PARENT_ID, $digitalObject->id);

              $children = QubitDigitalObject::get($criteria);

              // Delete derivatives
              foreach ($children as $child)
              {
                $child->delete();
              }

              // Import new one
              $digitalObject->importFromUri($value);
            }
            else
            {
              // Or create new one
              $errors = array();
              $tmsObject->importDigitalObjectFromUri($value, $errors);

              foreach ($errors as $error)
              {
                $this->logger->info('arFetchTms - '.$error);
              }
            }

            // Add property
            $this->addOrUpdateProperty($name, $value, $tmsObject);

            break;

          case 'Thumbnail':
            $artworkThumbnail = $value;
            $this->addOrUpdateProperty($name, $value, $tmsObject);

            break;

          // Child components
          case 'Components':
            foreach (json_decode($value, true) as $item)
            {
              $tmsComponentsIds[] = $item['ComponentID'];
            }

            break;

          // Log error
          case 'ErrorMsg':
            $this->logger->info('arFetchTms - ErrorMsg: '.$value);

            break;

          // Nothing yet
          case 'AlphaSort':
          case 'CreditLine':
          case 'FirstName':
          case 'LastName':
          case 'Prints':

            break;
        }
      }
    }

    $tmsObject->save();

    if (count($creation))
    {
      // Check for existing creation event
      if (isset($tmsObject->id))
      {
        $criteria = new Criteria;
        $criteria->add(QubitEvent::INFORMATION_OBJECT_ID, $tmsObject->id);
        $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);

        $creationEvent = QubitEvent::getOne($criteria);
      }

      // Or create new one
      if (!isset($creationEvent))
      {
        $creationEvent = new QubitEvent;
        $creationEvent->informationObjectId = $tmsObject->id;
        $creationEvent->typeId = QubitTerm::CREATION_ID;
      }

      // Add data
      qtSwordPlugin::addDataToCreationEvent($creationEvent, $creation);
    }

    return array($tmsComponentsIds, $artworkThumbnail);
  }

  public function getTmsComponentData($tmsComponent, $tmsComponentId, $artworkThumbnail)
  {
    // Request component from TMS API
    if (null !== $data = $this->getTmsData('/GetComponentDetails/Component/'.$tmsComponentId))
    {
      $data = $data['GetComponentDetailsResult'];

      foreach ($data as $name => $value)
      {
        if (!isset($value) || 0 == strlen($value))
        {
          continue;
        }

        switch ($name)
        {
          case 'Attributes':
            foreach (json_decode($value, true) as $item)
            {
              // Level of description from status attribute
              if (isset($item['Status']) && 0 < strlen($item['Status']) && isset($this->statusMapping[$item['Status']]))
              {
                $tmsComponent->levelOfDescriptionId = $this->statusMapping[$item['Status']];
              }

              // Add property for each attribute
              $count = 0;
              $propertyName = $propertyValue = null;
              foreach ($item as $key => $value)
              {
                if (!isset($key) || 0 == strlen($key) || !isset($value) || 0 == strlen($value))
                {
                  continue;
                }

                // Get property name from first key
                if ($count == 0)
                {
                  $propertyName = $key;
                  $propertyValue = $value;
                }
                else
                {
                  $propertyValue .= '. '.$key;
                  $propertyValue .= ': '.$value;
                }

                $count ++;
              }

              if (isset($propertyName) && isset($propertyValue))
              {
                $this->addOrUpdateProperty($propertyName, $propertyValue, $tmsComponent);
              }
            }

            break;

          // Info. object fields
          case 'ComponentID':
            $tmsComponent->identifier = $value;

            break;

          case 'ComponentName':
            $tmsComponent->title = $value;

            break;

          case 'Dimensions':
            $tmsComponent->physicalCharacteristics = $value;

            break;

          case 'PhysDesc':
            $tmsComponent->extentAndMedium = $value;

            break;

          // Properties
          case 'CompCount':
          case 'ComponentNumber':
            $this->addOrUpdateProperty($name, $value, $tmsComponent);

            break;

          // Object/term relation
          case 'ComponentType':
            $this->addOrUpdateObjectTermRelation('component_type', $value, $tmsComponent);

            break;

          // Notes
          case 'InstallComments':
          case 'PrepComments':
          case 'StorageComments':
            $this->addOrUpdateNote(sfConfig::get('app_drmc_term_'.strtolower($name).'_id'), $value, $tmsComponent);

            break;

          case 'TextEntries':
            $content = array();
            foreach (json_decode($value, true) as $textEntry)
            {
              $row = '';
              foreach ($textEntry as $field => $value)
              {
                if ($field == 'TextDate' && isset($value) && 0 < strlen($value))
                {
                  if (isset($value) && 0 < strlen($value))
                  {
                    $row .= ', Date: '.$value;
                  }
                }
                else if ($field == 'TextAuthor')
                {
                  if (isset($value) && 0 < strlen($value))
                  {
                    $row .= ', Author: '.$value;
                  }
                }
                else
                {
                  $row .= $field.': '.$value;
                }
              }

              $content[] = $row;
            }

            $this->addOrUpdateNote(QubitTerm::GENERAL_NOTE_ID, implode($content, "\n"), $tmsComponent);

            break;

          // Log error
          case 'ErrorMsg':
            $this->logger->info('arFetchTms - ErrorMsg: '.$value);

            break;

          // Nothing yet
          case 'ObjectID':

            break;
        }
      }
    }

    // Add thumbnail from artwork
    if (isset($artworkThumbnail))
    {
      $this->addOrUpdateProperty('artworkThumbnail', $artworkThumbnail, $tmsComponent);
    }

    $tmsComponent->save();

    return $tmsComponent->id;
  }

  public function getLastModifiedCheckDate($tmsObjectId)
  {
    // Request object from TMS API
    if (null !== $data = $this->getTmsData('/GetTombstoneDataRest/ObjectID/'.$tmsObjectId))
    {
      $data = $data['GetTombstoneDataRestIdResult'];

      if (isset($data['LastModifiedCheckDate']))
      {
        return $data['LastModifiedCheckDate'];
      }
    }

    return null;
  }

  protected function addOrUpdateProperty($name, $value, $io)
  {
    if (isset($io->id) && null !== $property = QubitProperty::getOneByObjectIdAndName($io->id, $name))
    {
      $property->value = $value;
      $property->save();
    }
    else
    {
      $io->addProperty($name, $value);
    }
  }

  protected function addOrUpdateObjectTermRelation($name, $value, $io)
  {
    $taxonomyId = sfConfig::get('app_drmc_taxonomy_'.strtolower($name).'s_id');
    $term = QubitFlatfileImport::createOrFetchTerm($taxonomyId, $value);

    // Check for existing term relation
    if (isset($io->id))
    {
      $criteria = new Criteria;
      $criteria->add(QubitObjectTermRelation::OBJECT_ID, $io->id);
      $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
      $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);

      $termRelation = QubitObjectTermRelation::getOne($criteria);
    }

    // Update
    if (isset($termRelation))
    {
      $termRelation->setTermId($term->id);
      $termRelation->save();
    }
    // Or create new one
    else
    {
      $termRelation = new QubitObjectTermRelation;
      $termRelation->setTermId($term->id);

      $io->objectTermRelationsRelatedByobjectId[] = $termRelation;
    }
  }

  protected function addOrUpdateNote($typeId, $content, $io)
  {
    // Check for existing note
    if (isset($io->id))
    {
      $criteria = new Criteria;
      $criteria->add(QubitNote::OBJECT_ID, $io->id);
      $criteria->add(QubitNote::TYPE_ID, $typeId);

      $note = QubitNote::getOne($criteria);
    }

    // Update
    if (isset($note))
    {
      $note->content = $content;
      $note->save();
    }
    // Or create new one
    else
    {
      $note = new QubitNote;
      $note->content = $content;
      $note->typeId = $typeId;

      $io->notes[] = $note;
    }
  }
}
