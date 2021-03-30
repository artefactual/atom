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
        $metsPath = $this->getMetsFilepath();

        // Create SimpleXML document from the METS file
        $this->document = new SimpleXMLElement(@file_get_contents($metsPath));

        // Initialize METS parser to provide convenience methods for accessing the
        // METS data
        $this->metsParser = new QubitMetsParser($this->document);

        // Stop if there isn't a proper structMap
        if (null === $structMap = $this->metsParser->getStructMap()) {
            throw new sfException(
                'A proper structMap could not be found in the METS file.'
            );
        }

        // Load mappings (it will stop the process if there is a wrong LOD)
        $this->mappings = $this->metsParser->getDipUploadMappings($structMap);

        // Save AIP metadata to the database
        $this->saveAipMetadata();

        // Get app default publication status
        $this->publicationStatus = sfConfig::get(
            'app_defaultPubStatus',
            QubitTerm::PUBLICATION_STATUS_DRAFT_ID
        );

        // Determine DIP upload method based on the structMap type
        switch ((string) $structMap['TYPE']) {
            case 'logical':
                // Hierarchical DIP upload method
                $this->recursivelyAddChildrenFromStructMap($structMap, $this->resource);

                break;

            case 'physical':
                // Non-hierarchical DIP upload method
                $this->addChildrenFromOriginalFileGrp();

                break;
        }

        // Finally, update target resource and AIP in ES
        QubitSearch::getInstance()->update($this->aip);
        QubitSearch::getInstance()->update($this->resource);

        parent::process();
    }

    /**
     * Get the filesystem path of the METS file.
     *
     * @return string filesystem path and filename of METS file
     */
    protected function getMetsFilepath()
    {
        if ($handle = opendir($this->filename)) {
            while (false !== $item = readdir($handle)) {
                if (0 < preg_match('/^METS\..*\.xml$/', $item)) {
                    $path = $this->filename.DIRECTORY_SEPARATOR.$item;

                    break;
                }
            }

            closedir($handle);
        } else {
            throw new sfException('METS directory could not be opened.');
        }

        if (!isset($path)) {
            throw new sfException('METS XML file was not found.');
        }

        return $path;
    }

    /**
     * Search the "objects/" directory by an $fileId to get associate file path.
     *
     * @param string $fileId the file identifier for the desired DIP object
     *
     * @return string the absolute filepath of the found DIP object
     */
    protected function getAccessCopyPath($fileId)
    {
        $uuid = $this->mappings['uuidMapping'][$fileId];

        $glob = implode(
            DIRECTORY_SEPARATOR,
            [$this->filename, 'objects', $uuid.'*']
        );

        $matches = glob($glob, GLOB_NOSORT);

        if (empty($matches)) {
            return;
        }

        return current($matches);
    }

    /**
     * Save the AIP metadata to the database.
     */
    protected function saveAipMetadata()
    {
        // Get AIP UUID from filename
        $aipUUID = $this->getUUID($this->filename);

        // Don't re-add the AIP if it's already in the database
        if (null !== $aip = QubitAip::getByUuid($aipUUID)) {
            $this->aip = $aip;

            return;
        }

        // Create AIP
        $this->aip = new QubitAip();
        $this->aip->uuid = $aipUUID;
        $this->aip->filename = $this->extractAipNameFromFileName($this->filename);
        $this->aip->digitalObjectCount = $this->metsParser->getOriginalFileCount();
        $this->aip->partOf = $this->resource->id;
        $this->aip->sizeOnDisk = $this->metsParser->getAipSizeOnDisk();
        $this->aip->createdAt = $this->metsParser->getAipCreationDate();
        $this->aip->indexOnSave = false;
        $this->aip->save();

        sfContext::getInstance()->getLogger()->info(
            'METSArchivematicaDIP - aipUUID: '.$aipUUID
        );
    }

    /**
     * Parse AIP name to extract filename.
     *
     * @param mixed $filename
     *
     * @return string $filename
     */
    protected function extractAipNameFromFileName($filename)
    {
        $parts = pathinfo($filename);

        return substr($parts['basename'], 0, -37);
    }

    /**
     * Add miscellaneous metadata related to a DIP object.
     *
     * @param QubitInformationObject $io     target information object
     * @param string                 $fileId METS FILEID
     *
     * @return QubitInformationObject with added metadata
     */
    protected function addRelatedMetadata($io, $fileId)
    {
        $objectUUID = $this->mappings['uuidMapping'][$fileId];

        sfContext::getInstance()->getLogger()->info(
            'METSArchivematicaDIP - objectUUID: '.$objectUUID
        );

        // Add any descriptive metadata recorded in the METS file
        $this->addDmdSecData($io, $fileId);

        $options = ['scope' => 'Archivematica AIP'];

        // Store UUIDs
        $io->addProperty('objectUUID', $objectUUID, $options);
        $io->addProperty('aipUUID', $this->aip->uuid, $options);

        // Store relative filepath to AIP object so the file can be requested via
        // the Storage Service API
        $io->addProperty(
            'relativePathWithinAip',
            $this->metsParser->getOriginalPathInAip($fileId),
            $options
        );

        // Metadata-only DIP Upload stores the AIP name in the 'aipName' property.
        // Adding here for consistency.
        $io->addProperty(
            'aipName',
            $this->extractAipNameFromFileName($this->filename),
            $options
        );

        $this->addPreservationSystemMetadata($io, $fileId, $options);

        // Add a relation between this $io and the AIP record
        return $this->addAipRelation($io, $this->aip);
    }

    /**
     * Add metadata related to the preservation system.
     *
     * These PREMIS event timestamps were retrieved from the METS.xml file as strings in UTC format.
     * After internal review it was decided to store them in their original UTC format in the AtoM database
     * and to convert them to the local time set for the AtoM server when displaying these values in AtoM templates.
     *
     * @param QubitInformationObject $io      target information object
     * @param string                 $fileId  METS FILEID
     * @param array                  $options list of options to pass to QubitProperty
     *
     * @return QubitInformationObject with added metadata
     */
    protected function addPreservationSystemMetadata($io, $fileId, $options)
    {
        $io->addProperty(
            'originalFileName',
            $this->metsParser->getOriginalFilename($fileId),
            $options
        );
        $io->addProperty(
            'originalFileSize',
            $this->metsParser->getOriginalFileSize($fileId),
            $options
        );
        $io->addProperty(
            'originalFileIngestedAt',
            $this->metsParser->getOriginalFileIngestionTime($fileId),
            $options
        );
        $io->addProperty(
            'preservationCopyFileName',
            $this->metsParser->getPreservationCopyFilename($fileId),
            $options
        );
        $io->addProperty(
            'preservationCopyFileSize',
            $this->metsParser->getPreservationCopyFileSize($fileId),
            $options
        );
        $io->addProperty(
            'preservationCopyNormalizedAt',
            $this->metsParser->getPreservationCopyCreationTime($fileId),
            $options
        );

        return $io;
    }

    /**
     * Add dmdSec file metadata to an information object.
     *
     * If the METS file includes a dmdSec, add the Dublin Core metadata to this
     * informatoin object
     *
     * @param QubitInformationObject $io     target information object
     * @param string                 $fileId target file id
     *
     * @return QubitInformationObject with added metadata
     */
    protected function addDmdSecData($io, $fileId)
    {
        if (
            isset($this->mappings['dmdMapping'][$fileId])
            && (null !== $dmdId = $this->mappings['dmdMapping'][$fileId])
            && (null !== $dmdSec = $this->metsParser->getDmdSec($dmdId))
        ) {
            $io = $this->metsParser->processDmdSec($dmdSec, $io);
        }

        return $io;
    }

    /**
     * Create a QubitDigitalObject and link it to the passed IO.
     *
     * @param QubitInformationObject $io   parent information object
     * @param string                 $path digital object path
     *
     * @return QubitInformationObject with linked digital object
     */
    protected function addDigitalObject($io, $path)
    {
        if (!empty($path) && is_readable($path)) {
            $digitalObject = new QubitDigitalObject();
            $digitalObject->assets[] = new QubitAsset($path);
            $digitalObject->usageId = QubitTerm::MASTER_ID;

            $io->digitalObjectsRelatedByobjectId[] = $digitalObject;
        }

        return $io;
    }

    /**
     * Create a QubitRelation linking $io to $aip.
     *
     * @param QubitAip $aip the QubitRelation->subject
     * @param mixed    $io
     *
     * @return QubitInformationObject the QubitRelation->object
     */
    protected function addAipRelation($io, $aip)
    {
        $relation = new QubitRelation();
        $relation->subject = $aip;
        $relation->typeId = QubitTerm::AIP_RELATION_ID;

        $io->relationsRelatedByobjectId[] = $relation;

        return $io;
    }

    /**
     * Add METS PREMIS data to the passed information object.
     *
     * Adds avaialable PREMIS object, FITS, MediaInfo, PREMIS Events, and Agents
     * data
     *
     * @param QubitInformationObject $io         the object to which the data is attached
     * @param string                 $objectUUID the METS object UUID
     * @param QubitInformationObject the passed object with added amdSec data
     */
    protected function addPremisData($io, $objectUUID)
    {
        // Add required data from METS file to the database
        try {
            $this->metsParser->addMetsDataToInformationObject($io, $objectUUID);
        } catch (sfException $e) {
            sfContext::getInstance()->getLogger()->err(
                'METSArchivematicaDIP -             '.$e->getMessage()
            );
        }

        return $io;
    }

    /**
     * Add object metadata and files for standard (non-hierarchical) DIP upload.
     *
     * Create a parent information object, then create a child information object
     * with attached digital object for each entry in METS fileGrp (USE: Original)
     */
    protected function addChildrenFromOriginalFileGrp()
    {
        $this->parent = $this->getParentForFileGrp();

        $files = $this->metsParser->getFilesFromOriginalFileGrp();

        if (false === $files || 0 === count($files)) {
            sfContext::getInstance()->getLogger()->err(
                'METSArchivematicaDIP - No files found in original fileGrp'
            );

            return;
        }

        // Build an array of children's metadata
        $children = $this->getChildDataFromFileGrp($files);

        // Create children in alphabetical order
        foreach ($children as $fileId => $data) {
            $this->createInformationObjectFromFileGrp($fileId, $data);
        }
    }

    /**
     * Get a parent information_object for this DIP.
     *
     * If the METS file has a dmdSec then a new, intermediate information_object
     * should be created to hold the DC Simple metadata from the dmdSec, and
     * attached as a child to the target description ($this->resource)
     *
     * If the METS file has NO dmdSec, then the children should be attached to the
     * target description ($this->resource)
     *
     * @return QubitInformationObject the parent object
     */
    protected function getParentForFileGrp()
    {
        // If there is a descriptive metadata section (dmdSec) then use the dmdSec
        // Dublin Core metadata to create a new intermediary information object
        if (null !== $dmdSec = $this->metsParser->getMainDmdSec()) {
            sfContext::getInstance()->getLogger()->info(
                'METSArchivematicaDIP - Main dmdSec found!'
            );

            $parent = new QubitInformationObject();
            $parent->setLevelOfDescriptionByName('file');
            $parent->parentId = $this->resource->id;
            $parent = $this->metsParser->processDmdSec($dmdSec, $parent);

            // Add a relation to the AIP record
            $parent = $this->addAipRelation($parent, $this->aip);

            $parent->save();
        } else {
            // If there is no dmdSec, then use the target description
            // ($this->resource) as the parent
            sfContext::getInstance()->getLogger()->info(
                'METSArchivematicaDIP - Main dmdSec not found!'
            );

            $parent = $this->resource;
        }

        return $parent;
    }

    /**
     * Build an array of digital object metadata from METS fileGrp file elements.
     *
     * @param mixed $files
     *
     * @return array digital object metadata, sorted alphabetically
     */
    protected function getChildDataFromFileGrp($files)
    {
        $children = [];

        foreach ($files as $file) {
            if (!isset($file['ID'])) {
                continue;
            }

            $fileId = (string) $file['ID'];

            // DIP paths
            if (null == $absolutePathWithinDip = $this->getAccessCopyPath($fileId)) {
                sfContext::getInstance()->getLogger()->info(
                    'METSArchivematicaDIP -             Access copy cannot be found in'
                    .' the DIP'
                );

                // Do not create information_objects for files without an access copy,
                // if normalization fails, Archivematica copies the original file into
                // the DIP
                continue;
            }

            $children[$fileId]['title'] = $this->getTitleFromFilename($fileId);
            $children[$fileId]['absolutePathWithinDip'] = $absolutePathWithinDip;
        }

        // Sort children by title, use asort to keep index association
        if (!empty($children)) {
            uasort($children, function ($elem1, $elem2) {
                return strcasecmp($elem1['title'], $elem2['title']);
            });
        }

        return $children;
    }

    /**
     * Derive information_object title from digital object filename.
     *
     * Use the PREMIS originalName if it exists - if not, find the filename in the
     * DIP "objects/" directory with getAccessCopyPath()
     *
     * @param mixed $fileId
     */
    protected function getTitleFromFilename($fileId)
    {
        $originalFilename = $this->metsParser->getOriginalFilename($fileId);

        if (!empty($originalFilename)) {
            $title = $originalFilename;
        } else {
            // Search the "objects" directory for a file with this objectUUID
            $absolutePathWithinDipParts = pathinfo($this->getAccessCopyPath($fileId));

            // Remove objectUUID from filename
            $title = substr($absolutePathWithinDipParts['basename'], 37);
        }

        // Optionally strip the filename's extension
        $stripExtensions = QubitSetting::getByName('stripExtensions');

        if (isset($stripExtensions) && $stripExtensions->value) {
            $fileParts = pathinfo(trim($title));
            $title = $fileParts['filename'];
        }

        return $title;
    }

    /**
     * Create an access system record for a DIP object (from <fileGrp> metadata).
     *
     * Create an information_object DB record for a DIP object, move the DIP
     * access file to the AtoM uploads/ directory, and link the info object to a
     * digital_object record
     *
     * @param mixed $fileId
     * @param mixed $data
     *
     * @return QubitInformationObject a child object
     */
    protected function createInformationObjectFromFileGrp($fileId, $data)
    {
        $objectUUID = $this->mappings['uuidMapping'][$fileId];

        // Create child object
        $io = new QubitInformationObject();

        // Set initial properties
        $io->setPublicationStatus($this->publicationStatus);
        $io->setLevelOfDescriptionByName('item');
        $io->parent = $this->parent;
        $io->title = $data['title'];

        $io = $this->addRelatedMetadata($io, $fileId);
        $io = $this->addDigitalObject($io, $data['absolutePathWithinDip']);

        // Save IO without updating the ES document
        $io->indexOnSave = false;
        $io->save();

        $io = $this->addPremisData($io, $objectUUID);

        // Save IO updating the ES document
        $io->indexOnSave = true;
        $io->save();
    }

    /**
     * Add object metadata and files for hierarchical DIP upload.
     *
     * Read the hierarchical arrangement of the DIP contents from the logical
     * structMap in the METS file.
     *
     * Hierarchical DIP upload creates a complex descriptive arrangement under
     * the target node ($this->resource) that can include intermediary,
     * metadata-only nodes (directories) as well as the primary digital object
     * nodes that describe the digital object, and link to the file on disk.
     *
     * @param SimpleXMLElement       $element
     * @param QubitInformationObject $parent
     */
    protected function recursivelyAddChildrenFromStructMap($element, $parent)
    {
        $this->metsParser->registerNamespaces($element, ['m' => 'mets']);

        foreach ($element->xpath('m:div') as $div) {
            $this->metsParser->registerNamespaces($div, ['m' => 'mets']);

            // If this element has no child file pointer <fptr> elements, then it is
            // directory node and we should recursively add it's children
            if (0 == count($div->xpath('m:fptr'))) {
                $io = $this->getDirectoryFromStuctMapDiv($div, $parent);

                // Pass new QubitInformationObject as parent to recursively add children
                $this->recursivelyAddChildrenFromStructMap($div, $io);
            } else {
                // Otherwise, create an information object representing a DIP object
                $this->addDipObjectFromStructMap($div, $parent);
            }
        }
    }

    /**
     * Create a basic QubitInformationObject from <structMap><div> data.
     *
     * Set parent, title, level of description, and publication status
     *
     * @param SimpleXMLElement       $div    an object representing a <div> element
     * @param QubitInformationObject $parent the parent of the new info object
     *
     * @return QubitInformationObject
     */
    protected function createInformationObjectFromStructMapDiv($div, $parent)
    {
        $io = new QubitInformationObject();
        $io->parentId = $parent->id;
        $io->setPublicationStatus($this->publicationStatus);

        if (null !== $div['LABEL']) {
            $io->title = (string) $div['LABEL'];
        }

        if (null !== $div['TYPE']) {
            $io->levelOfDescriptionId = $this->mappings['lodMapping'][(string) $div['TYPE']];
        } else {
            $io->setLevelOfDescriptionByName('Item');
        }

        return $io;
    }

    /**
     * Get or create a "directory" QubitInformationObject.
     *
     * Directory information objects are purely organizational, have minimal
     * metadata (a title and level of description), and have no attached digital
     * object
     *
     * @param SimpleXMLElement       $element an object representing a <div> element
     * @param QubitInformationObject $parent  the parent for a new directory IO
     *
     * @return QubitInformationObject the directory object
     */
    protected function getDirectoryFromStuctMapDiv($element, $parent)
    {
        // Special case where <div @label> is "objects" - don't create a new IO but
        // set the $parent LOD to <div @type> and attach child <div>s to $parent
        if (isset($element['LABEL']) && 'objects' == (string) $element['LABEL']) {
            $parent->levelOfDescriptionId = $this->mappings['lodMapping'][(string) $element['TYPE']];
            $parent->save();

            return $parent;
        }

        // Otherwise create a new intermediary IO with LABEL as title
        // and TYPE as LOD, and attach any child <div>s to it
        $io = $this->createInformationObjectFromStructMapDiv($element, $parent);
        $io->save();

        return $io;
    }

    /**
     * Add digital object metadata, file metadata, and file path to access system.
     *
     * Only files under use original and inside the objects folder will be added
     *
     * @param SimpleXMLElement       $div    an object representing a <div> element
     * @param QubitInformationObject $parent the parent for a new directory IO
     *
     * @return QubitInformationObject an ORM object representing a DIP object
     */
    protected function addDipObjectFromStructMap($div, $parent)
    {
        $fptr = $div->xpath('m:fptr');
        $fileId = (string) $fptr[0]['FILEID'];

        if (empty($fileId)) {
            return;
        }

        // Get objectUUID
        if (null == $objectUUID = $this->mappings['uuidMapping'][$fileId]) {
            return;
        }

        // Get absolute path to digital object in DIP
        if (null == $absolutePathWithinDip = $this->getAccessCopyPath($fileId)) {
            return;
        }

        $io = $this->createInformationObjectFromStructMapDiv($div, $parent);
        $io = $this->addRelatedMetadata($io, $fileId);
        $io = $this->addDigitalObject($io, $absolutePathWithinDip);

        // Save IO without updating the ES document
        $io->indexOnSave = false;
        $io->save();

        $io = $this->addPremisData($io, $objectUUID);

        // Save IO updating the ES document
        $io->indexOnSave = true;
        $io->save();
    }
}
