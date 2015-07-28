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

          // Directory for the METS file
          $dirPath = sfConfig::get('sf_uploads_dir').
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

          break;
        }
      }

      closedir($handle);
    }

    // Stop if the METS file wasn't found
    if (!$entry)
    {
      throw new sfException('METS XML file was not found.');
    }

    // Load METS file (it will stop the process if the file can't be opened)
    $this->metsParser = new QubitMetsParser($this->filename.DIRECTORY_SEPARATOR.$entry);

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

    // Finally, update target resource in ES
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

    foreach ($files as $file)
    {
      if(!isset($file['ID']))
      {
        continue;
      }

      $fileId = (string)$file['ID'];

      // Object UUID
      $objectUUID = $this->mappings['uuidMapping'][$fileId];

      sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP - objectUUID: '.$objectUUID);

      // DIP paths
      if (false === $absolutePathWithinDip = $this->getAccessCopyPath($objectUUID))
      {
        // This is actually not too bad, maybe normalization failed but we still
        // want to have an information object
        sfContext::getInstance()->getLogger()->info('METSArchivematicaDIP -             Access copy cannot be found in the DIP');
      }
      else
      {
        $absolutePathWithinDipParts = pathinfo($absolutePathWithinDip);
        $relativePathWithinDip = 'objects'.DIRECTORY_SEPARATOR.$absolutePathWithinDipParts['basename'];
      }

      // Create child
      $child = new QubitInformationObject;
      $child->setPublicationStatus($this->publicationStatus);
      $child->setLevelOfDescriptionByName('item');
      $child->parentId = $parent->id;

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

      $child->title = $filename;

      // Process metatadata from METS file
      if ((null !== $dmdId = $this->mappings['dmdMapping'][$fileId])
        && (null !== $dmdSec = $this->metsParser->getDmdSec($dmdId)))
      {
        $child = $this->metsParser->processDmdSec($dmdSec, $child);
      }

      // Storage UUIDs
      $child->addProperty('objectUUID', $objectUUID);
      $child->addProperty('aipUUID', $this->aip->uuid);

      // Add digital object
      if (false !== $absolutePathWithinDip && is_readable($absolutePathWithinDip))
      {
        $digitalObject = new QubitDigitalObject;
        $digitalObject->assets[] = new QubitAsset($absolutePathWithinDip);
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $child->digitalObjects[] = $digitalObject;
      }

      // Create relation with AIP
      $relation = new QubitRelation;
      $relation->subjectId = $this->aip->id;
      $relation->typeId = QubitTerm::AIP_RELATION_ID;
      $child->relationsRelatedByobjectId[] = $relation;

      // A lot more data from the METS file (object metadata, events, agents)
      // is obtained in arElasticSearchInformationObjectPdo
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

        // A lot more data from the METS file (object metadata, events, agents)
        // is obtained in arElasticSearchInformationObjectPdo
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
