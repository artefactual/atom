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

  protected function process()
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    // AIP UUID
    $this->aipUUID = $this->getUUID($this->filename);

    if (null !== QubitAip::getByUuid($this->aipUUID))
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
            DIRECTORY_SEPARATOR.$this->aipUUID.
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

    // Check Archivematica MoMA prefix
    $momaPrefix = substr($this->resource, 0, 3);
    $momaSuffix = substr($this->resource, 3);
    switch ($momaPrefix)
    {
      case 'tr:':
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Technology record');
        $this->processTechnologyRecord($momaSuffix);

        break;

      case 'ar:':
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Artwork record');
        $this->processArtworkRecord($momaSuffix);

        break;

      default:
        throw new sfException('Parameter not recognized '.$this->resource);
    }

    parent::process();
  }

  protected function processTechnologyRecord($momaSuffix)
  {
    if (ctype_digit($momaSuffix))
    {
      $resource = QubitInformationObject::getById($momaSuffix);
    }
    else
    {
      $criteria = new Criteria;
      $criteria->add(QubitSlug::SLUG, $momaSuffix);
      $criteria->addJoin(QubitSlug::OBJECT_ID, QubitInformationObject::ID);

      $resource = QubitInformationObject::getOne($criteria);
    }

    if (null === $resource)
    {
      throw new sfException('Technology record with the given slug/id cannot be found');
    }

    if ($resource->levelOfDescriptionId !== sfConfig::get('app_drmc_lod_supporting_technology_record_id'))
    {
      throw new sfException('The given slug/id doesn\'t belong to a technology record');
    }

    // Get root technology record
    if ($resource->parentId != QubitInformationObject::ROOT_ID)
    {
      $criteria = new Criteria;
      $criteria->add(QubitInformationObject::LFT, $resource->lft, Criteria::LESS_THAN);
      $criteria->add(QubitInformationObject::RGT, $resource->rgt, Criteria::GREATER_THAN);
      $criteria->add(QubitInformationObject::PARENT_ID, QubitInformationObject::ROOT_ID);

      $rootTechRecord = QubitInformationObject::getOne($criteria);
    }

    if (isset($rootTechRecord))
    {
      list($aipIo, $aip) = $this->addAip($resource, $rootTechRecord);
    }
    else
    {
      list($aipIo, $aip) = $this->addAip($resource, $resource);
    }

    $this->addDigitalObjects($aipIo);

    // Create relation between AIP and tech record
    $relation = new QubitRelation;
    $relation->object = $resource;
    $relation->subject = $aip;
    $relation->typeId = QubitTerm::AIP_RELATION_ID;
    $relation->save();

    if (isset($rootTechRecord))
    {
      // Create relation between AIP and the root tech record
      $relation = new QubitRelation;
      $relation->object = $rootTechRecord;
      $relation->subject = $aip;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $relation->save();

      // Add AIP to the root tech record in ES
      QubitSearch::getInstance()->update($rootTechRecord);
    }

    // Add related digital objects to the AIP in ES
    // and save AIP data for the tech record in ES
    QubitSearch::getInstance()->update($aip);
    QubitSearch::getInstance()->update($resource);
  }

  protected function processArtworkRecord($tmsObjectId)
  {
    // Check for existing Artwork
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::IDENTIFIER, $tmsObjectId);
    $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, sfConfig::get('app_drmc_lod_artwork_record_id'));

    if (null !== $tmsObject = QubitInformationObject::getOne($criteria))
    {
      // Get intermediate level
      $criteria = new Criteria;
      $criteria->add(QubitInformationObject::PARENT_ID, $tmsObject->id);
      $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, sfConfig::get('app_drmc_lod_description_id'));

      if (null === $components = QubitInformationObject::getOne($criteria))
      {
        // Or create new one
        $components = new QubitInformationObject;
        $components->parentId = $tmsObject->id;
        $components->levelOfDescriptionId = sfConfig::get('app_drmc_lod_description_id');
        $components->setPublicationStatusByName('Published');
        $components->title = 'Components';
        $components->save();
      }
    }
    else
    {
      // Create artwork
      $tmsObject = new QubitInformationObject;
      $tmsObject->parentId = QubitInformationObject::ROOT_ID;
      $tmsObject->levelOfDescriptionId = sfConfig::get('app_drmc_lod_artwork_record_id');
      $tmsObject->setPublicationStatusByName('Published');

      $fetchTms = new arFetchTms;

      // Get TMS Object data
      list($tmsComponentsIds, $artworkThumbnail) = $fetchTms->getTmsObjectData($tmsObject, $tmsObjectId);

      // Create intermediate level "Components"
      $components = new QubitInformationObject;
      $components->parentId = $tmsObject->id;
      $components->levelOfDescriptionId = sfConfig::get('app_drmc_lod_description_id');
      $components->setPublicationStatusByName('Published');
      $components->title = 'Components';
      $components->save();

      // Obtain and create components from TMS
      if (isset($tmsComponentsIds))
      {
        $tmsComponentsIoIds = array();
        foreach ($tmsComponentsIds as $tmsId)
        {
          // Create component
          $tmsComponent = new QubitInformationObject;
          $tmsComponent->parentId = $components->id;
          $tmsComponent->levelOfDescriptionId = sfConfig::get('app_drmc_lod_component_id');
          $tmsComponent->setPublicationStatusByName('Published');

          // Get TMS Component data
          $tmsComponentsIoIds[] = $fetchTms->getTmsComponentData($tmsComponent, $tmsId, $artworkThumbnail);
        }

        // Save info object components ids as property of the artwork
        // because they are not directly related but added as part of the artwork in ES
        QubitProperty::addUnique($tmsObject->id, 'childComponents', serialize($tmsComponentsIoIds));
      }
    }

    // Check if a ComponentNumber is set in DC identifier of the main dmd section
    if (null != ($dmdSec = $this->getMainDmdSec()))
    {
      $dmdSec->registerXPathNamespace('m', 'http://www.loc.gov/METS/');
      $dmdSec->registerXPathNamespace('dc', 'http://purl.org/dc/terms/');

      if (0 < count($identifier = $dmdSec->xpath('.//m:mdWrap/m:xmlData/dc:dublincore/dc:identifier'))
        && strlen($componentNumber = trim($identifier[0])) > 0)
      {
        // Check for existing TMSComponent
        $criteria = new Criteria;
        $criteria->addJoin(QubitProperty::ID, QubitPropertyI18n::ID);
        $criteria->add(QubitProperty::NAME, 'ComponentNumber');
        $criteria->add(QubitPropertyI18n::VALUE, $componentNumber);

        if (null !== $property = QubitProperty::getOne($criteria))
        {
          $component = QubitInformationObject::getById($property->objectId);
        }
      }
    }

    if (isset($component))
    {
      list($aipIo, $aip) = $this->addAip($component, $tmsObject);

      $this->addDigitalObjects($aipIo);

      // Create relation between AIP and component
      $relation = new QubitRelation;
      $relation->object = $component;
      $relation->subject = $aip;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $relation->save();

      // Add AIP to the component in ES
      QubitSearch::getInstance()->update($component);
    }
    else
    {
      list($aipIo, $aip) = $this->addAip($components, $tmsObject);

      $this->addDigitalObjects($aipIo);
    }

    // Create relation between AIP and artwork
    $relation = new QubitRelation;
    $relation->object = $tmsObject;
    $relation->subject = $aip;
    $relation->typeId = QubitTerm::AIP_RELATION_ID;
    $relation->save();

    // Add related digital objects and ingestionUser to the AIP in ES
    // and save AIP and components data for the artwork in ES
    QubitSearch::getInstance()->update($aip);
    QubitSearch::getInstance()->update($tmsObject);
  }

  /*
   * @param QubitInformationObject $informationObjectParent  Parent of the new AIP information object
   * @param QubitInformationObject $partOfObject             New AIP will be link to this object using partOf
   * @return array Two elements, new AIP information object and new AIP
   */
  protected function addAip($informationObjectParent, $partOfObject)
  {
    // Create intermediate information object "AIP"
    $aipIo = new QubitInformationObject;
    $aipIo->parentId = $informationObjectParent->id;
    $aipIo->levelOfDescriptionId = sfConfig::get('app_drmc_lod_aip_id');
    $aipIo->setPublicationStatusByName('Published');
    $aipIo->title = 'AIP';

    // Add main object data in METS file to the AIP intermediate level
    if (null != ($dmdSec = $this->getMainDmdSec()))
    {
      list($aipIo, $creation) = $this->processDmdSec($dmdSec, $aipIo, $options = array('ignoreTitle' => false));
    }

    $aipIo->save();

    if (count($creation))
    {
      $event = new QubitEvent;
      $event->informationObjectId = $aipIo->id;
      $event->typeId = QubitTerm::CREATION_ID;

      qtSwordPlugin::addDataToCreationEvent($event, $creation);
    }

    // Add creation events to ES
    QubitSearch::getInstance()->update($aipIo);

    sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - $aipIo created '.$aipIo->id);

    $parts = pathinfo($this->filename);
    $filename = $parts['basename'];

    // Store AIP data
    $aip = new QubitAip;
    $aip->uuid = $this->aipUUID;
    $aip->filename = substr($filename, 0, -37);
    $aip->digitalObjectCount = count($this->getFilesFromDirectory($this->filename.DIRECTORY_SEPARATOR.'/objects'));
    $aip->partOf = $partOfObject->id;

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

    // Get AIP ingenstion username
    foreach ($this->document->xpath('//m:amdSec/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:AGENT"]/m:xmlData/m:agent') as $agent)
    {
      $agent->registerXPathNamespace('m', 'http://www.loc.gov/METS/');
      $agentType = $agent->xpath('m:agentIdentifier/m:agentIdentifierType');

      if (0 < count($agentType) && (string)$agentType[0] === 'Archivematica user pk')
      {
        if (0 < count($agentName = $agent->xpath('m:agentName')))
        {
          $agentName = (string)$agentName[0];
          $agentName = split(',', $agentName);
          $agentName = substr($agentName[0], 10, strlen($agentName[0]) - 11);

          QubitProperty::addUnique($aip->id, 'ingestionUser', $agentName);

          break;
        }
      }
    }

    // Add parent title of the AIP information object as attachedTo property for the AIP
    QubitProperty::addUnique($aip->id, 'attachedTo', $informationObjectParent->getTitle(array('sourceCulture' => true)));

    sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - $aip created '.$aip->id);

    return array($aipIo, $aip);
  }

  /**
   * Given the AIP information object ($aipIo), this function will nest one file
   * information object for each file referenced in the METS file. Additionally,
   * a digital object will be attached if the file has been included in the DIP.
   *
   * @param $aipIo QubitInformationObject
   */
  protected function addDigitalObjects($aipIo)
  {
    // Create child file information object for AIP METS file before checking fileGrp
    $child = new QubitInformationObject;
    $child->parentId = $aipIo->id;
    $child->levelOfDescriptionId = sfConfig::get('app_drmc_lod_digital_object_id');
    $child->setPublicationStatusByName('Published');
    $child->title = 'METS.'.$this->aipUUID.'.xml';

    // Add digital object
    $metsPath = $this->filename.DIRECTORY_SEPARATOR.'METS.'.$this->aipUUID.'.xml';
    if (is_readable($metsPath))
    {
      $digitalObject = new QubitDigitalObject;
      $digitalObject->assets[] = new QubitAsset($metsPath);
      $digitalObject->usageId = QubitTerm::MASTER_ID;
      $child->digitalObjects[] = $digitalObject;
    }

    $child->save();

    // Store relative path within AIP and AIP UUID
    QubitProperty::addUnique($child->id, 'original_relative_path_within_aip', 'METS.'.$this->aipUUID.'.xml');
    QubitProperty::addUnique($child->id, 'aipUUID', $this->aipUUID);

    $mapping = $this->getStructMapFileToDmdSecMapping();

    $files = $this->document->xpath('//m:mets/m:fileSec/m:fileGrp/m:file');
    if (false === $files || count($files) === 0)
    {
      sfContext::getInstance()->getLogger()->err('METSArchivematicaDIP - addDigitalObjects(): fileGrp not found');
      return;
    }

    foreach ($files as $file)
    {
      // Parent fileGrp
      $parent = current($file->xpath('parent::*'));

      // Obtain use and UUID
      $use = (string)$parent['USE'];
      $uuid = $this->getUUID($file->attributes()->ID);
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$uuid.' ('.$use.')');

      // Check availability of FLocat
      if (!isset($file->FLocat))
      {
        sfContext::getInstance()->getLogger()->err('METSArchivematicaDIP - FLocat not found');
        continue;
      }

      // Store relative path within AIP
      $fLocatAttrs = $file->FLocat->attributes('xlink', true);
      if (empty($fLocatAttrs->href))
      {
        sfContext::getInstance()->getLogger()->err('METSArchivematicaDIP - FLocat[href] not found or empty');
        continue;
      }

      // AIP paths
      $relativePathWithinAip = $fLocatAttrs->href;
      $relativePathWithinAipParts = pathinfo($relativePathWithinAip);
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             [path->AIP] '.$relativePathWithinAip);

      // Don't add METS file in submissionDocumentation
      if ($use == 'submissionDocumentation' && $relativePathWithinAipParts['basename'] == 'METS.xml')
      {
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             METS file in submissionDocumentation is not added');
        continue;
      }

      // DIP paths
      if (false === $absolutePathWithinDip = $this->getAccessCopyPath($uuid))
      {
        // This is actually not too bad, maybe normalization failed but we still
        // want to have an information object
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             Access copy cannot be found in the DIP');
      }
      else
      {
        $absolutePathWithinDipParts = pathinfo($absolutePathWithinDip);
        $relativePathWithinDip = 'objects'.DIRECTORY_SEPARATOR.$absolutePathWithinDipParts['basename'];
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             [path->DIP] '.$relativePathWithinDip);
      }

      // Create child file information object
      $child = new QubitInformationObject;
      $child->parentId = $aipIo->id;
      $child->levelOfDescriptionId = sfConfig::get('app_drmc_lod_digital_object_id');
      $child->setPublicationStatusByName('Published');
      $child->title = $relativePathWithinAipParts['basename']; // Notice that we use the filename of the original file, not the access copy

      // Files other than the ones under USE="original" are not included
      if ($use === 'original' && false !== $absolutePathWithinDip && is_readable($absolutePathWithinDip))
      {
        // Add digital object
        $digitalObject = new QubitDigitalObject;
        $digitalObject->assets[] = new QubitAsset($absolutePathWithinDip);
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $child->digitalObjects[] = $digitalObject;
      }

      // Process metatadata
      // Child must be saved after processDmdSec. Saving it twice in the job
      // makes the second one inefficient
      if (null !== ($dmdSec = $this->searchFileDmdSec($uuid, $mapping)))
      {
        list($child, $creation) = $this->processDmdSec($dmdSec, $child);

        $child->save();

        if (count($creation))
        {
          $event = new QubitEvent;
          $event->informationObjectId = $child->id;
          $event->typeId = QubitTerm::CREATION_ID;

          qtSwordPlugin::addDataToCreationEvent($event, $creation);
        }
      }
      else
      {
        $child->save();
      }

      // Use property to augment digital object with relative path within AIP
      $property = new QubitProperty;
      $property->objectId = $child->id;
      $property->setName('original_relative_path_within_aip');
      $property->setValue($relativePathWithinAip);
      $property->save();

      // Storage UUIDs
      QubitProperty::addUnique($child->id, 'objectUUID', $uuid);
      QubitProperty::addUnique($child->id, 'aipUUID', $this->aipUUID);

      // Save creation event in ES
      // A lot more data from the METS file (object metadata, events, agents)
      // is obtained in arElasticSearchInformationObjectPdo
      QubitSearch::getInstance()->update($child);
    }

    return;
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
}
