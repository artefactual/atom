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

class qtPackageExtractorMETSArchivematicaDIP extends qtPackageExtractorBase
{
  protected function processDmdSec($xml, $informationObject, $options = array())
  {
    $xml->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

    $dublincore = $xml->xpath('.//m:mdWrap/m:xmlData/*');
    $dublincore = end($dublincore);
    $dublincore->registerXPathNamespace('dc', 'http://purl.org/dc/terms/');

    $creation = array();

    foreach ($dublincore as $item)
    {
      $value = trim($item->__toString());
      if (0 == strlen($value))
      {
        continue;
      }

      switch (str_replace('dcterms:', '', $item->getName()))
      {
        case 'title':
          if (!isset($options['ignoreTitle']) || $options['ignoreTitle'] == false)
          {
            $informationObject->setTitle($value);
          }

          break;

        case 'creator':
          $creation['actorName'] = $value;
          break;

        case 'provenance':
          $informationObject->acquisition = $value;

          break;

        case 'coverage':
          $informationObject->setAccessPointByName($value, array('type_id' => QubitTaxonomy::PLACE_ID));

          break;

        case 'subject':
          $informationObject->setAccessPointByName($value, array('type_id' => QubitTaxonomy::SUBJECT_ID));

          break;

        case 'description':
          $informationObject->scopeAndContent = $value;

          break;

        case 'publisher':
          $informationObject->setActorByName($value, array('event_type_id' => QubitTerm::PUBLICATION_ID));

          break;

        case 'contributor':
          $informationObject->setActorByName($value, array('event_type_id' => QubitTerm::CONTRIBUTION_ID));

          break;

        case 'date':
          $creation['date'] = $value;

          break;

        case 'type':
          foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DC_TYPE_ID) as $item)
          {
            if (strtolower($value) == strtolower($item->__toString()))
            {
              $relation = new QubitObjectTermRelation;
              $relation->term = $item;

              $informationObject->objectTermRelationsRelatedByobjectId[] = $relation;

              break;
            }
          }

          break;

        case 'extent':
        case 'format':
          $informationObject->extentAndMedium = $value;

          break;

        case 'identifier':
          $informationObject->identifier = $value;

          break;

        case 'source':
          $informationObject->locationOfOriginals = $value;

          break;

        case 'language':
          // TODO: the user could write "English" instead of "en"? (see symfony...widget/i18n/*)
          $informationObject->language = array($value);

          break;

        case 'isPartOf':
          // TODO: ?

          break;

        case 'rights':
          $informationObject->accessConditions = $value;

          break;
      }
    }

    return array($informationObject, $creation);
  }

  protected function addCreationEvent($informationObject, $options)
  {
    $event = new QubitEvent;
    $event->informationObjectId = $informationObject->id;
    $event->typeId = QubitTerm::CREATION_ID;

    if ($options['actorName'])
    {
      if ($options['actorDate'])
      {
        $actor = QubitFlatfileImport::createOrFetchActor($options['actorName'], array('datesOfExistence' => $options['actorDate']));
      }
      else
      {
        $actor = QubitFlatfileImport::createOrFetchActor($options['actorName']);
      }

      $event->actorId = $actor->id;
    }

    if ($options['date'])
    {
      $date = $options['date'];

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
        if (isset($options['parseDate']) && $options['parseDate'] == false)
        {
          $event->date = $date;
        }
        else
        {
          $event->date = QubitFlatfileImport::parseDate($date);
        }

      }
    }

    $event->save();
  }

  protected function likeISO8601Date($date)
  {
    $date = substr($date, 0, 19).'Z';

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $date, $parts) == true)
    {
      $time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);

      $input_time = strtotime($date);

      if ($input_time === false)
      {
        return false;
      }

      return $input_time == $time;
    }
    else
    {
      return false;
    }
  }

  protected function process()
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    // AIP UUID
    $aipUUID = $this->getUUID($this->filename);

    if (null !== QubitAip::getByUuid($aipUUID))
    {
      throw new sfException('There is already a AIP with the given UUID');
    }

    // Find METS file
    if ($handle = opendir($this->filename))
    {
      while (false !== $entry = readdir($handle))
      {
        if (0 < preg_match('/^METS\..*\.xml$/', $entry))
        {
          $path = $this->filename.DIRECTORY_SEPARATOR.$entry;

          sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Opening '.$path);

          // Directory for the METS file
          $dirPath = sfConfig::get('sf_web_dir').
            DIRECTORY_SEPARATOR.'uploads'.
            DIRECTORY_SEPARATOR.'aips'.
            DIRECTORY_SEPARATOR.$aipUUID.
            DIRECTORY_SEPARATOR;

          // Create the target directory
          if (!file_exists($dirPath))
          {
            mkdir($dirPath, 0755, true);
          }

          // Copy METS file
          if (false !== @copy($path, $dirPath.'METS.xml'))
          {
            sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Saving '.$dirPath.'METS.xml');
          }

          $this->document = new SimpleXMLElement(@file_get_contents($this->filename.DIRECTORY_SEPARATOR.$entry));

          break;
        }
      }

      closedir($handle);
    }
    else
    {
      throw new sfException('METS XML file was not found.');
    }

    if (!isset($this->document))
    {
      throw new sfException('METS document could not be opened.');
    }

    $this->document->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

    sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - aipUUID: '.$aipUUID);

    $publicationStatus = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);

    // Ignore $this->resource and create a new top-level from TMS
    list($tmsObject, $tmsComponentsIds) = $this->getTombstoneObjects();

    // Create intermediate level "Components"
    $components = new QubitInformationObject;
    $components->parentId = $tmsObject->id;
    $components->levelOfDescriptionId = sfConfig::get('app_drmc_lod_description_id');
    $components->setPublicationStatusByName('Published');
    $components->title = 'Components';

    // Add main object data in METS file to the intermediate level
    if (null != ($dmdSec = $this->getMainDmdSec()))
    {
      list($components, $creation) = $this->processDmdSec($dmdSec, $components, $options = array('ignoreTitle' => true));
    }

    $components->save();

    $aipIo = new QubitInformationObject;
    $aipIo->parentId = $components->id;
    $aipIo->levelOfDescriptionId = sfConfig::get('app_drmc_lod_aip_id');
    $aipIo->setPublicationStatusByName('Published');
    $aipIo->title = 'AIP';
    $aipIo->save();

    if (count($creation))
    {
      $this->addCreationEvent($components, $creation);
    }

    // Obtain and create components from TMS
    foreach ($tmsComponentsIds as $tmsId)
    {
      $this->createTombstoneComponent($tmsId, $components->id);
    }

    $parts = pathinfo($this->filename);
    $filename = $parts['basename'];

    // Store AIP data
    $aip = new QubitAip;
    $aip->uuid = $aipUUID;
    $aip->filename = substr($filename, 0, -37);
    $aip->digitalObjectCount = count($this->getFilesFromDirectory($this->filename.DIRECTORY_SEPARATOR.'/objects'));
    $aip->partOf = $tmsObject->id;

    // Get size on disk
    $totalSize = 0;
    foreach ($this->document->xpath('//m:amdSec/m:techMD/m:mdWrap[@MDTYPE="PREMIS:OBJECT"]/m:xmlData') as $xmlData)
    {
      $xmlData->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');
      if (0 < count($size = $xmlData->xpath('s:object/s:objectCharacteristics/s:size')))
      {
        $totalSize += $size[0];
      }
    }

    $aip->sizeOnDisk = $totalSize;

    // Get AIP creation date
    $metsHdr = $this->document->xpath('//m:metsHdr');
    if (isset($metsHdr) && null !== $createdAt = $metsHdr[0]['CREATEDATE'])
    {
      $aip->createdAt = $createdAt;
    }

    $aip->save();

    // Create relation between AIP and intermediate level
    $relation = new QubitRelation;
    $relation->object = $components;
    $relation->subject = $aip;
    $relation->typeId = QubitTerm::AIP_RELATION_ID;
    $relation->save();

    // Save creation event and AIP data in ES
    QubitSearch::getInstance()->update($components);

    // Create digital components from files in /objects
    $mapping = $this->getStructMapFileToDmdSecMapping();

    foreach ($this->getFilesFromDirectory($this->filename.DIRECTORY_SEPARATOR.'/objects') as $item)
    {
      $parts = pathinfo($item);
      $filename = $parts['basename'];

      // Object UUID
      $objectUUID = $this->getUUID($filename);

      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$objectUUID);

      // Create child
      $child = new QubitInformationObject;
      $child->parentId = $aipIo->id;
      $child->levelOfDescriptionId = sfConfig::get('app_drmc_lod_digital_object_id');
      $child->setPublicationStatusByName('Published');

      // TODO: use UUID as unique key in the array
      $child->title = substr($filename, 37);
      foreach ($mapping as $k => $v)
      {
        if ($objectUUID == $this->getUUID($k))
        {
          $child->title = $this->getOriginalFilename($k);
        }
      }

      // Add digital object
      $digitalObject = new QubitDigitalObject;
      $digitalObject->assets[] = new QubitAsset($item);
      $digitalObject->usageId = QubitTerm::MASTER_ID;
      $child->digitalObjects[] = $digitalObject;

      $child->save();

      // Process metatadata from METS file
      if (null !== ($dmdSec = $this->searchFileDmdSec($objectUUID, $mapping)))
      {
        list($child, $creation) = $this->processDmdSec($dmdSec, $child);

        if (count($creation))
        {
          $this->addCreationEvent($child, $creation);
        }
      }

      // Storage UUIDs
      $child->addProperty('objectUUID', $objectUUID);
      $child->addProperty('aipUUID', $aipUUID);

      // Create relation with AIP
      $relation = new QubitRelation;
      $relation->object = $child;
      $relation->subject = $aip;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $relation->save();

      // Save creation event and AIP data in ES
      // A lot more data from the METS file (object metadata, events, agents)
      // is obtained in arElasticSearchInformationObjectPdo
      QubitSearch::getInstance()->update($child);
    }

    // Add related digital objects to the AIP in ES
    $aip->save();

    parent::process();
  }

  protected function getStructMapFileToDmdSecMapping()
  {
    switch ((string)$this->structMap['TYPE'])
    {
      case 'logical':
        $items = $this->structMap->div->div;

        break;

      case 'physical':
        $items = $this->structMap->div->div->div;

        break;
    }

    $explore = function(&$items, &$mapping = array()) use (&$explore)
    {
      foreach ($items as $item)
      {
        switch ((string)$item['TYPE'])
        {
          case 'Directory':
            if (in_array((string)$item['LABEL'], array('metadata', 'submissionDocumentation')))
            {
              continue;
            }

            $explore($item, $mapping);

            break;

          case 'Item':
            $mapping[(string)$item->fptr['FILEID']] = (string)$item['DMDID'];

            break;
        }
      }

      return $mapping;
    };

    return $explore($items);
  }

  /**
   * Find the original filename
   * simple_load_string() is used to make xpath queries faster
   */
  protected function getOriginalFilename($fileid)
  {
    if (false !== $file = $this->document->xpath('//m:fileSec/m:fileGrp[@USE="original"]/m:file[@ID="'.$fileid.'"]'))
    {
      if (null !== $admId = $file[0]['ADMID'])
      {
        $fileData = $file[0];
        $fileData = simplexml_load_string($fileData->asXML());

        if (false !== $xmlData = $this->document->xpath('//m:amdSec[@ID="'.(string)$admId.'"]/m:techMD/m:mdWrap/m:xmlData'))
        {
          $xmlData = $xmlData[0];
          $xmlData = simplexml_load_string($xmlData->asXML());

          $xmlData->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');
          if (false !== $originalName = $xmlData->xpath('//s:object//s:originalName'))
          {
            return end(explode('/', (string)$originalName[0]));
          }
        }
      }
    }
  }

  protected function getMainDmdSec()
  {
    foreach ($this->document->xpath('//m:structMap[@TYPE="logical" or @TYPE="physical"]') as $item)
    {
      switch ((string)$item['TYPE'])
      {
        case 'logical':
          $dmdId = $item->div['DMDID'];

          break;

        case 'physical':
          $dmdId = $item->div->div['DMDID'];

          break;

        default:
          throw new sfException('Unrecognized structMap layout: '.$item['TYPE']);
      }

      // We're going to need this later
      $this->structMap = $item;

      if (null === $dmdId)
      {
        continue;
      }

      $dmdSec = $this->document->xpath('//m:dmdSec[@ID="'.(string)$dmdId.'"]');
      if (0 < count($dmdSec))
      {
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - dmdSec found!');

        return $dmdSec[0];
      }
    }

    sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - dmdSec not found!');
  }

  protected function searchFileDmdSec($uuid, $mapping)
  {
    $node = $this->document->xpath('//m:mets/m:fileSec/m:fileGrp[@USE="original"]');

    if (empty($node))
    {
      return;
    }

    foreach ($node[0] as $item)
    {
      if (false !== strstr($item['ID'], $uuid))
      {
        $id = (string)$item['ID'];

        if (isset($mapping[$id]))
        {
          $dmdId = $mapping[$id];

          $dmdSec = $this->document->xpath('//m:mets/m:dmdSec[@ID="'.$dmdId.'"]');
          if (0 < count($dmdSec))
          {
            return $dmdSec[0];
          }
        }
      }
    }
  }

  protected function getTombstoneObjects()
  {
    // Create top-level from TMS
    $tmsObject = new QubitInformationObject;
    $tmsObject->parentId = QubitInformationObject::ROOT_ID;
    $tmsObject->levelOfDescriptionId = sfConfig::get('app_drmc_lod_artwork_record_id');
    $tmsObject->setPublicationStatusByName('Published');

    $tmsComponentsIds = $creation = array();

    // Get TMS object ID from METS file
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

    // Request object from TMS API
    if (isset($tmsId))
    {
      $curl = curl_init();

      curl_setopt_array($curl, array(
          CURLOPT_RETURNTRANSFER => 1,
          CURLOPT_FAILONERROR => true,
          CURLOPT_URL => sfConfig::get('app_drmc_tms_url').'/GetTombstoneDataRest/ObjectID/'.$tmsId));

      if (false === $resp = curl_exec($curl))
      {
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.curl_error($curl));
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
              case 'ClassificationID':
              case 'ConstituentID':
              case 'DepartmentID':
              case 'ImageID':
              case 'ObjectNumber':
              case 'ObjectStatusID':
              case 'SortNumber':
              case 'Thumbnail':
                $tmsObject->addProperty($name, $value);
                break;

              // Object/term relations
              case 'Classification':
              case 'Department':

                $taxonomyId = sfConfig::get('app_drmc_taxonomy_'.strtolower($name).'s_id');
                $term = QubitFlatfileImport::createOrFetchTerm($taxonomyId, $value);

                $newTermRelation = new QubitObjectTermRelation;
                $newTermRelation->setTermId($term->id);

                $tmsObject->objectTermRelationsRelatedByobjectId[] = $newTermRelation;

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

                $errors = array();
                $tmsObject->importDigitalObjectFromUri($value, $errors);

                foreach ($errors as $error)
                {
                  sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - '.$error);
                }

                // Add property
                $tmsObject->addProperty($name, $value);

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
                sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.$value);
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
    }

    curl_close($curl);

    $tmsObject->save();

    if (count($creation))
    {
      $creation['parseDate'] = false;
      $this->addCreationEvent($tmsObject, $creation);
    }

    return array($tmsObject, $tmsComponentsIds);
  }

  protected function createTombstoneComponent($tmsId, $parentId)
  {
    // Create component from TMS
    $tmsComponent = new QubitInformationObject;
    $tmsComponent->parentId = $parentId;
    $tmsComponent->levelOfDescriptionId = sfConfig::get('app_drmc_lod_description_id');
    $tmsComponent->setPublicationStatusByName('Published');

    // Request component from TMS API
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FAILONERROR => true,
        CURLOPT_URL => sfConfig::get('app_drmc_tms_url').'/GetComponentDetails/Component/'.$tmsId));

    if (false === $resp = curl_exec($curl))
    {
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.curl_error($curl));
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
              $tmsComponent->addProperty($name, $value);

              break;

            // Object/term relation
            case 'ComponentType':
              $taxonomyId = sfConfig::get('app_drmc_taxonomy_component_types_id');
              $term = QubitFlatfileImport::createOrFetchTerm($taxonomyId, $value);

              $newTermRelation = new QubitObjectTermRelation;
              $newTermRelation->setTermId($term->id);

              $tmsComponent->objectTermRelationsRelatedByobjectId[] = $newTermRelation;

              break;

            // Notes
            case 'InstallComments':
            case 'PrepComments':
            case 'StorageComments':
                $note = new QubitNote;
                $note->content = $value;
                $note->culture = 'en';
                $note->typeId = sfConfig::get('app_drmc_note_type_'.strtolower($name).'_id');

                $tmsComponent->notes[] = $note;

              break;

            // Log error
            case 'ErrorMsg':
              sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Error getting Tombstone data: '.$value);

              break;

            // Nothing yet
            case 'Attributes':
            case 'ObjectID':
            case 'TextEntries':

              break;
          }
        }
      }
    }

    curl_close($curl);

    $tmsComponent->save();
  }
}
