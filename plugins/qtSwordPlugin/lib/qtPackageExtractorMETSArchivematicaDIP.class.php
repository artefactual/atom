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
  protected function process()
  {
    // Get AIP UUID from filename
    $aipUUID = $this->getUUID($this->filename);

    // Find and save METS file
    if ($handle = opendir($this->filename))
    {
      while (false !== $entry = readdir($handle))
      {
        if (0 < preg_match('/^METS\..*\.xml$/', $entry))
        {
          $path = $this->filename.DIRECTORY_SEPARATOR.$entry;

          sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Opening '.$path);

          $this->document = new SimpleXMLElement(@file_get_contents($path));

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

    // Initialice METS parser, used in addDigitalObjects to add
    // the required data from the METS file to the digital objects
    $this->metsParser = new QubitMetsParser($this->document);
    
    // Stop if there isn't a proper structMap
    if (null === $structMap = $this->metsParser->getStructMap())
    {
      throw new sfException('A proper structMap could not be found in the METS file.');
    }

    // Load mappings (it will stop the process if there is a wrong LOD)
    $this->mappings = $this->metsParser->getDipUploadMappings($structMap);

    // Create AIP
    $parts = pathinfo($this->filename);
    $aipName = substr($parts['basename'], 0, -37);

    $this->aip = new QubitAip;
    $this->aip->uuid = $aipUUID;
    $this->aip->filename = $aipName;
    $this->aip->digitalObjectCount = count($this->metsParser->getFilesFromOriginalFileGrp());
    $this->aip->partOf = $this->resource->id;
    $this->aip->sizeOnDisk = $this->metsParser->getAipSizeOnDisk();
    $this->aip->createdAt = $this->metsParser->getAipCreationDate();
    $this->aip->indexOnSave = false;
    $this->aip->save();

    sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - aipUUID: '.$aipUUID);

    $this->publicationStatus = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);

    // Determine DIP upload method based on the structMap type
    switch ((string)$structMap['TYPE'])
    {
      case 'logical':
        // Hieararchical DIP upload method
        $this->recursivelyAddChildsFromLogicalStructMapDiv($structMap, $this->resource);

        break;

      case 'physical':
        // Non-hierarchical DIP upload method
        $this->addChildsFromOriginalFileGrp();

        break;
    }

    // Finally, update target resource and AIP in ES
    QubitSearch::getInstance()->update($this->aip);
    QubitSearch::getInstance()->update($this->resource);

    parent::process();
  }

  protected function addChildsFromOriginalFileGrp()
  {
    // Main object
    if (null !== $dmdSec = $this->metsParser->getMainDmdSec())
    {
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Main dmdSec found!');

      $parent = new QubitInformationObject;
      $parent->setLevelOfDescriptionByName('file');
      $parent->parentId = $this->resource->id;
      $parent = $this->metsParser->processDmdSec($dmdSec, $parent);

      // Create relation with AIP
      $relation = new QubitRelation;
      $relation->subjectId = $this->aip->id;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $parent->relationsRelatedByobjectId[] = $relation;

      $parent->save();
    }
    else
    {
      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - Main dmdSec not found!');

      $parent = $this->resource;
    }

    $files = $this->metsParser->getFilesFromOriginalFileGrp();
    if (false === $files || count($files) === 0)
    {
      sfContext::getInstance()->getLogger()->err('METSArchivematicaDIP - No files found in original fileGrp');
      return;
    }

    // Create array with children data
    $children = array();
    foreach ($files as $file)
    {
      if(!isset($file['ID']))
      {
        continue;
      }

      $fileId = (string)$file['ID'];

      // Object UUID
      $objectUUID = $this->mappings['uuidMapping'][$fileId];

      // DIP paths
      if (false === $absolutePathWithinDip = $this->getAccessCopyPath($objectUUID))
      {
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             Access copy cannot be found in the DIP');

        // Do not create IOs for files without access copy,
        // if normalization fails, Archivematica copies the original into the DIP
        continue;
      }
      else
      {
        $absolutePathWithinDipParts = pathinfo($absolutePathWithinDip);
        $relativePathWithinDip = 'objects'.DIRECTORY_SEPARATOR.$absolutePathWithinDipParts['basename'];
      }

      // Determine filename to use as title (uploaded or, from METS, original filename)
      $filename = substr($absolutePathWithinDipParts['basename'], 37);

      if (null !== $originalFilename = $this->metsParser->getOriginalFilename($fileId))
      {
        $filename = $originalFilename;
      }

      // Optionally strip the filename's extension
      $stripExtensions = QubitSetting::getByName('stripExtensions');

      if ((null !== $stripExtensions) && $stripExtensions->value)
      {
        $fileParts = pathinfo(trim($filename));
        $filename = $fileParts['filename'];
      }

      $children[$fileId]['title'] = $filename;
      $children[$fileId]['objectUUID'] = $objectUUID;
      $children[$fileId]['absolutePathWithinDip'] = $absolutePathWithinDip;
    }

    // Sort children by title, use asort to keep index association
    asort($children, function ($elem1, $elem2) {
      return strcasecmp($elem1['title'], $elem2['title']);
    });

    // Create children in order
    foreach ($children as $fileId => $data)
    {
      // Create child
      $child = new QubitInformationObject;
      $child->setPublicationStatus($this->publicationStatus);
      $child->setLevelOfDescriptionByName('item');
      $child->parentId = $parent->id;
      $child->title = $data['title'];

      // Process metatadata from METS file
      if ((null !== $dmdId = $this->mappings['dmdMapping'][$fileId])
        && (null !== $dmdSec = $this->metsParser->getDmdSec($dmdId)))
      {
        $child = $this->metsParser->processDmdSec($dmdSec, $child);
      }

      // Storage UUIDs
      $child->addProperty('objectUUID', $data['objectUUID']);
      $child->addProperty('aipUUID', $this->aip->uuid);

      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$data['objectUUID']);

      // Add digital object
      if (false !== $data['absolutePathWithinDip'] && is_readable($data['absolutePathWithinDip']))
      {
        $digitalObject = new QubitDigitalObject;
        $digitalObject->assets[] = new QubitAsset($data['absolutePathWithinDip']);
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $child->digitalObjects[] = $digitalObject;
      }

      // Create relation with AIP
      $relation = new QubitRelation;
      $relation->subjectId = $this->aip->id;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $child->relationsRelatedByobjectId[] = $relation;

      // Save IO without updating the ES document
      $child->indexOnSave = false;
      $child->save();

      // Add required data from METS file to the database
      $error = $this->metsParser->addMetsDataToInformationObject($child, $data['objectUUID']);
      if (isset($error))
      {
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             ' . $error);
      }

      // Save IO updating the ES document
      $child->indexOnSave = true;
      $child->save();
    }
  }

  protected function recursivelyAddChildsFromLogicalStructMapDiv($structMapDiv, $parent)
  {
    $structMapDiv->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

    foreach ($structMapDiv->xpath('m:div') as $item)
    {
      $item->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

      // Directory
      if (count($fptr = $item->xpath('m:fptr')) == 0)
      {
        // If there isn't a LOD set in TYPE add children to its parent
        if (!isset($item['TYPE']))
        {
          $child = $parent;
        }
        // LOD set in div labeled as "objects" and children are added to the parent
        else if (isset($item['LABEL']) && (string)$item['LABEL'] == 'objects')
        {
          $parent->levelOfDescriptionId = $this->mappings['lodMapping'][(string)$item['TYPE']];
          $parent->save();

          $child = $parent;
        }
        // Otherwise create info object with LABEL as title
        // and TYPE as LOD, and add children to it
        else
        {
          $child = $this->createInformationObjectFromStructMapDiv($item, $parent);
          $child->save();
        }

        // Add children
        $this->recursivelyAddChildsFromLogicalStructMapDiv($item, $child);
      }
      // File (only files under use original and inside the objects folder will be added)
      else if (isset($fptr[0]['FILEID'])
        && (null !== $objectUUID = $this->mappings['uuidMapping'][(string)$fptr[0]['FILEID']])
        && (false !== $absolutePathWithinDip = $this->getAccessCopyPath($objectUUID)))
      {
        $child = $this->createInformationObjectFromStructMapDiv($item, $parent);

        // Store UUIDs
        $child->addProperty('aipUUID', $this->aip->uuid);
        $child->addProperty('objectUUID', $objectUUID);
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$objectUUID);

        // Digital object
        if (is_readable($absolutePathWithinDip))
        {
          $digitalObject = new QubitDigitalObject;
          $digitalObject->assets[] = new QubitAsset($absolutePathWithinDip);
          $digitalObject->usageId = QubitTerm::MASTER_ID;
          $child->digitalObjects[] = $digitalObject;
        }

        // Process metatadata from METS file
        if ((null !== $dmdId = $this->mappings['dmdMapping'][(string)$fptr[0]['FILEID']])
          && (null !== $dmdSec = $this->metsParser->getDmdSec($dmdId)))
        {
          $child = $this->metsParser->processDmdSec($dmdSec, $child);
        }

        // Create relation with AIP
        $relation = new QubitRelation;
        $relation->subjectId = $this->aip->id;
        $relation->typeId = QubitTerm::AIP_RELATION_ID;
        $child->relationsRelatedByobjectId[] = $relation;

        // Save IO without updating the ES document
        $child->indexOnSave = false;
        $child->save();

        // Add required data from METS file to the database
        $error = $this->metsParser->addMetsDataToInformationObject($child, $objectUUID);
        if (isset($error))
        {
          sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             ' . $error);
        }

        // Save IO updating the ES document
        $child->indexOnSave = true;
        $child->save();
      }
    }
  }

  protected function createInformationObjectFromStructMapDiv($div, $parent)
  {
    $io = new QubitInformationObject;
    $io->parentId = $parent->id;
    $io->setPublicationStatus($this->publicationStatus);

    if (null !== $div['LABEL'])
    {
      $io->title = (string)$div['LABEL'];
    }

    if (null !== $div['TYPE'])
    {
      $io->levelOfDescriptionId = $this->mappings['lodMapping'][(string)$div['TYPE']];
    }
    else
    {
      $io->setLevelOfDescriptionByName('Item');
    }

    return $io;
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
