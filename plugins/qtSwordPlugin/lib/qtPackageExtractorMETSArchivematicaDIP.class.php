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
  protected function processDmdSec($xml, $informationObject)
  {
    $xml->registerXPathNamespace('m', 'http://www.loc.gov/METS/');
    $xml->registerXPathNamespace('dc', 'http://purl.org/dc/terms/');

    $dublincore = $xml->xpath('.//m:mdWrap/m:xmlData/dc:dublincore/*');

    $creation = array();

    foreach ($dublincore as $item)
    {
      $value = trim($item->__toString());
      if (0 == strlen($value))
      {
        continue;
      }

      switch (str_replace(array('dcterms:', 'dc:'), '', $item->getName()))
      {
        case 'title':
          $informationObject->setTitle($value);

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
      // Check actor creation with the target repository
      if (null === $actor = QubitActor::getByNameAndRepositoryId($options['actorName'], $this->resource->repositoryId))
      {
        $actor = new QubitActor;
        $actor->parentId = QubitActor::ROOT_ID;
        $actor->setAuthorizedFormOfName($options['actorName']);
        $actor->save();
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
        $event->date = QubitFlatfileImport::parseDate($date);
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

    // Check if there is a logical structMap and create a
    // level of descriptions and dmd sections mapping
    $logicalStructMap = $this->document->xpath('//m:structMap[@TYPE="logical"]');
    $this->lodMapping = $this->dmdMapping = array();

    if (false !== $logicalStructMap && 0 < count($logicalStructMap))
    {
      $logicalStructMap[0]->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

      foreach ($logicalStructMap[0]->xpath('.//m:div') as $item)
      {
        // DMD mapping
        $dmdId = (string)$item['DMDID'];
        $fileId = (string)$item->fptr['FILEID'];

        if (strlen($fileId) > 0 && strlen($dmdId) > 0)
        {
          $this->dmdMapping[$fileId] = $dmdId;
        }

        // LOD mapping
        if (null === $item['TYPE'])
        {
          continue;
        }

        $lodName = (string)$item['TYPE'];

        $sql  = 'SELECT
                    term.id';
        $sql .= ' FROM '.QubitTerm::TABLE_NAME.' term';
        $sql .= ' JOIN '.QubitTermI18n::TABLE_NAME.' i18n
                    ON term.id = i18n.id';
        $sql .= ' WHERE i18n.name = ?
                    AND term.taxonomy_id = ?';

        if (false !== $id = QubitPdo::fetchColumn($sql, array($lodName, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID)))
        {
          $this->lodMapping[$lodName] = $id;
        }
        else
        {
          // If a level of description is not found, the upload process is stoped
          throw new sfException('Level of description not found: '.$lodName);
        }
      }
    }

    sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - aipUUID: '.$aipUUID);

    $this->publicationStatus = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);

    $parts = pathinfo($this->filename);
    $filename = $parts['basename'];

    // Store AIP data
    $this->aip = new QubitAip;
    $this->aip->typeId = QubitTerm::ARTWORK_COMPONENT_ID; // TODO: Get AIP type from METS
    $this->aip->uuid = $aipUUID;
    $this->aip->filename = $filename;
    $this->aip->digitalObjectCount = count($this->getFilesFromDirectory($this->filename.DIRECTORY_SEPARATOR.'/objects'));
    $this->aip->partOf = $this->resource->id;

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

    $this->aip->sizeOnDisk = $totalSize;

    // Get AIP creation date
    $metsHdr = $this->document->xpath('//m:metsHdr');
    if (isset($metsHdr) && null !== $createdAt = $metsHdr[0]['CREATEDATE'])
    {
      $this->aip->createdAt = $createdAt;
    }

    $this->aip->save();

    // Main object
    if (null != ($dmdSec = $this->getMainDmdSec()))
    {
      $parent = new QubitInformationObject;
      list($parent, $creation) = $this->processDmdSec($dmdSec, $parent);
      $parent->setLevelOfDescriptionByName('file');

      $parent->parentId = $this->resource->id;
      $parent->save();

      if (count($creation))
      {
        $this->addCreationEvent($parent, $creation);
      }

      // Create relation with AIP
      $relation = new QubitRelation;
      $relation->object = $parent;
      $relation->subject = $this->aip;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $relation->save();
    }
    else
    {
      $parent = $this->resource;
    }

    if (false !== $logicalStructMap && 0 < count($logicalStructMap))
    {
      // Hierarchical method over logical structMap
      $this->addChildsFromLogicalStructMap($logicalStructMap[0], $parent);
    }
    else
    {
      // Get dmd sections mapping from physical structMap
      $this->dmdMapping = $this->getStructMapFileToDmdSecMapping();

      // Non hierarchical method over physical structMap
      foreach ($this->getFilesFromDirectory($this->filename.DIRECTORY_SEPARATOR.'/objects') as $item)
      {
        $parts = pathinfo($item);
        $filename = $parts['basename'];

        // Object UUID
        $objectUUID = $this->getUUID($filename);

        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$objectUUID);

        // Create child
        $child = new QubitInformationObject;
        $child->setPublicationStatus($publicationStatus);
        $child->setLevelOfDescriptionByName('item');
        $child->parentId = $parent->id;

        // TODO: use UUID as unique key in the array
        $child->title = substr($filename, 37);
        foreach ($this->dmdMapping as $k => $v)
        {
          if ($objectUUID == $this->getUUID($k))
          {
            $child->title = $this->getOriginalFilename($k);
          }
        }

        // Storage UUIDs
        $child->addProperty('objectUUID', $objectUUID);
        $child->addProperty('aipUUID', $aipUUID);

        // Add digital object
        $digitalObject = new QubitDigitalObject;
        $digitalObject->assets[] = new QubitAsset($item);
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $child->digitalObjects[] = $digitalObject;

        // Process metatadata from METS file
        if (null !== ($dmdSec = $this->searchFileDmdSec($objectUUID)))
        {
          list($child, $creation) = $this->processDmdSec($dmdSec, $child);

          // Must be saved after the dmd section is processed
          // and before adding the creation event
          $child->save();

          if (count($creation))
          {
            $this->addCreationEvent($child, $creation);
          }
        }
        else
        {
          $child->save();
        }

        // Create relation with AIP
        $relation = new QubitRelation;
        $relation->object = $child;
        $relation->subject = $this->aip;
        $relation->typeId = QubitTerm::AIP_RELATION_ID;
        $relation->save();

        // Save creation event and AIP data in ES
        // A lot more data from the METS file (object metadata, events, agents)
        // is obtained in arElasticSearchInformationObjectPdo
        QubitSearch::getInstance()->update($child);
      }
    }

    // Update resource in ES
    QubitSearch::getInstance()->update($this->resource);

    parent::process();
  }

  protected function getStructMapFileToDmdSecMapping()
  {
    $this->structMap->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

    switch ((string)$this->structMap['TYPE'])
    {
      case 'logical':
        $items = $this->structMap->xpath('m:div/m:div');

        break;

      case 'physical':
        $items = $this->structMap->xpath('m:div/m:div/m:div');

        break;
    }

    $explore = function(&$items, &$mapping = array()) use (&$explore)
    {
      foreach ($items as $item)
      {
        $item->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

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
            $fptr = $item->xpath('m:fptr');

            $mapping[(string)$fptr[0]['FILEID']] = (string)$item['DMDID'];

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
      $item->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

      switch ((string)$item['TYPE'])
      {
        case 'logical':
          $divs = $item->xpath('m:div');

          break;

        case 'physical':
          $divs = $item->xpath('m:div/m:div');

          break;

        default:
          throw new sfException('Unrecognized structMap layout: '.$item['TYPE']);
      }

      // We're going to need this later
      $this->structMap = $item;

      if (count($divs) > 0)
      {
        $dmdId = $divs[0]['DMDID'];
      }

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

  protected function searchFileDmdSec($uuid)
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

        if (isset($this->dmdMapping[$id]))
        {
          $dmdId = $this->dmdMapping[$id];

          $dmdSec = $this->document->xpath('//m:mets/m:dmdSec[@ID="'.$dmdId.'"]');
          if (0 < count($dmdSec))
          {
            return $dmdSec[0];
          }
        }
      }
    }
  }

  protected function addChildsFromLogicalStructMap($structMap, $parent)
  {
    $structMap->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

    foreach ($structMap->xpath('m:div') as $item)
    {
      $item->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

      // Create child
      $child = new QubitInformationObject;
      $child->parentId = $parent->id;
      $child->setPublicationStatus($this->publicationStatus);

      if (null !== $item['LABEL'])
      {
        $child->title = (string)$item['LABEL'];
      }

      if (null !== $item['TYPE'])
      {
        $child->levelOfDescriptionId = $this->lodMapping[(string)$item['TYPE']];
      }

      // Store AIP UUID
      $child->addProperty('aipUUID', $this->aip->uuid);

      // Add digital object and objectUUID if there is a ftpr child
      if (count($fptr = $item->xpath('m:fptr')) > 0 && null !== $fptr[0]['FILEID'])
      {
        // Object UUID
        $objectUUID = $this->getUUID((string)$fptr[0]['FILEID']);
        $child->addProperty('objectUUID', $objectUUID);

        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$objectUUID);

        // Digital object
        $absolutePathWithinDip = $this->getAccessCopyPath($objectUUID);

        if (false !== $absolutePathWithinDip && is_readable($absolutePathWithinDip))
        {
          $digitalObject = new QubitDigitalObject;
          $digitalObject->assets[] = new QubitAsset($absolutePathWithinDip);
          $digitalObject->usageId = QubitTerm::MASTER_ID;
          $child->digitalObjects[] = $digitalObject;
        }
      }

      // Process metatadata from METS file
      if (isset($objectUUID) && null !== ($dmdSec = $this->searchFileDmdSec($objectUUID)))
      {
        list($child, $creation) = $this->processDmdSec($dmdSec, $child);

        // Must be saved after the dmd section is processed
        // and before adding the creation event
        $child->save();

        if (count($creation))
        {
          $this->addCreationEvent($child, $creation);
        }
      }
      else
      {
        $child->save();
      }

      // Create relation with AIP
      $relation = new QubitRelation;
      $relation->object = $child;
      $relation->subject = $this->aip;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $relation->save();

      // Add childs
      if (count($item->xpath('m:div')) > 0)
      {
        $this->addChildsFromLogicalStructMap($item, $child);
      }

      // Save creation event and AIP data in ES
      // A lot more data from the METS file (object metadata, events, agents)
      // is obtained in arElasticSearchInformationObjectPdo
      QubitSearch::getInstance()->update($child);
    }
  }

  protected function getAccessCopyPath($uuid)
  {
    $glob = $this->filename.DIRECTORY_SEPARATOR.'objects'.DIRECTORY_SEPARATOR.$uuid.'*';
    $matches = glob($glob, GLOB_NOSORT);
    if (empty($matches))
    {
      return false;
    }

    return current($matches);
  }
}
