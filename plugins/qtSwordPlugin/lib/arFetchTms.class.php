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
  public static function getTmsObjectData($tmsObject, $tmsObjectId)
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
    $curl = curl_init();

    $url = sfConfig::get('app_drmc_tms_url').'/GetTombstoneDataRest/ObjectID/'.$tmsObjectId;
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FAILONERROR => true,
        CURLOPT_URL => $url));

    if (false === $resp = curl_exec($curl))
    {
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.curl_error($curl));
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - URL: '.$url);
    }
    else
    {
      $data = json_decode($resp, true);
      $data = $data['GetTombstoneDataRestIdResult'];

      foreach ($data as $name => $value)
      {
        if (isset($value) && 0 < strlen($value))
        {
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
              self::addOrUpdateProperty($name, $value, $tmsObject);

              break;

            // Object/term relations
            case 'Classification':
            case 'Department':
              self::addOrUpdateObjectTermRelation($name, $value, $tmsObject);

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
              // Delete actual digital object if exists
              if (null !== $digitalObject = $tmsObject->getDigitalObject())
              {
                // TODO: Update digital object
              }
              else
              {
                // Create digital object from URI
                $errors = array();
                $tmsObject->importDigitalObjectFromUri($value, $errors);

                foreach ($errors as $error)
                {
                  sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - '.$error);
                }
              }

              // Add property
              self::addOrUpdateProperty($name, $value, $tmsObject);

              break;

            case 'Thumbnail':
              $artworkThumbnail = $value;
              self::addOrUpdateProperty($name, $value, $tmsObject);

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
              sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - ErrorMsg: '.$value);

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
    }

    curl_close($curl);

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

  public static function getTmsComponentData($tmsComponent, $tmsComponentId, $artworkThumbnail)
  {
    // Mapping from TMS status to level of descriptions
    $statusMapping = array(
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

    // Request component from TMS API
    $curl = curl_init();
    $url = sfConfig::get('app_drmc_tms_url').'/GetComponentDetails/Component/'.$tmsComponentId;

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FAILONERROR => true,
        CURLOPT_URL => $url));

    if (false === $resp = curl_exec($curl))
    {
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.curl_error($curl));
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - URL: '.$url);
    }
    else
    {
      $data = json_decode($resp, true);
      $data = $data['GetComponentDetailsResult'];

      foreach ($data as $name => $value)
      {
        if (isset($value) && 0 < strlen($value))
        {
          switch ($name)
          {
            // Level of description from status attribute
            case 'Attributes':
              foreach (json_decode($value, true) as $item)
              {
                if (isset($item['Status']) && 0 < strlen($item['Status']) && isset($statusMapping[$item['Status']]))
                {
                  $tmsComponent->levelOfDescriptionId = $statusMapping[$item['Status']];
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
              self::addOrUpdateProperty($name, $value, $tmsComponent);

              break;

            // Object/term relation
            case 'ComponentType':
              self::addOrUpdateObjectTermRelation('component_type', $value, $tmsComponent);

              break;

            // Notes
            case 'InstallComments':
            case 'PrepComments':
            case 'StorageComments':
              $note = new QubitNote;
              $note->content = $value;
              $note->culture = 'en';
              $note->typeId = sfConfig::get('app_drmc_term_'.strtolower($name).'_id');

              $tmsComponent->notes[] = $note;

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

              $note = new QubitNote;
              $note->culture = 'en';
              $note->content = implode($content, "\n");
              $note->typeId = QubitTerm::GENERAL_NOTE_ID;

              $tmsComponent->notes[] = $note;

              break;

            // Log error
            case 'ErrorMsg':
              sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - ErrorMsg: '.$value);

              break;

            // Nothing yet
            case 'ObjectID':

              break;
          }
        }
      }
    }

    curl_close($curl);

    // Add thumbnail from artwork
    if (isset($artworkThumbnail))
    {
      $tmsComponent->addProperty('artworkThumbnail', $artworkThumbnail);
    }

    $tmsComponent->save();

    return $tmsComponent->id;
  }

  public static function getLastModifiedCheckDate($tmsObjectId)
  {
    // Request object from TMS API
    $curl = curl_init();

    $url = sfConfig::get('app_drmc_tms_url').'/GetTombstoneDataRest/ObjectID/'.$tmsObjectId;
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FAILONERROR => true,
        CURLOPT_URL => $url));

    if (false === $resp = curl_exec($curl))
    {
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.curl_error($curl));
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - URL: '.$url);
    }
    else
    {
      $data = json_decode($resp, true);
      $data = $data['GetTombstoneDataRestIdResult'];

      if (isset($data['LastModifiedCheckDate']))
      {
        return $data['LastModifiedCheckDate'];
      }
    }

    curl_close($curl);

    return null;
  }

  public static function addOrUpdateProperty($name, $value, $io)
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

  public static function addOrUpdateObjectTermRelation($name, $value, $io)
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
}
