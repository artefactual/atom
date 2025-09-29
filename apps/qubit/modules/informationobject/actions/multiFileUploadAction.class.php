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

class InformationObjectMultiFileUploadAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();

        $this->resource = $this->getRoute()->resource;

        // Check that object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'update') && !$this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) {
            QubitAcl::forwardUnauthorized();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        // Get max upload size limits
        $this->maxFileSize = QubitDigitalObject::getMaxUploadSize();
        $this->maxPostSize = QubitDigitalObject::getMaxPostSize();

        // Paths for uploader javascript
        $this->uploadResponsePath = "{$this->context->routing->generate(null, ['module' => 'digitalobject', 'action' => 'upload'])}?".http_build_query([session_name() => session_id()]);
        $this->uploadTmpDir = "{$this->request->getRelativeUrlRoot()}/uploads/tmp";

        // Build form
        $this->form->setValidator('files', new QubitValidatorCountable(['required' => true]));

        $this->form->setValidator('title', new sfValidatorString());
        $this->form->setWidget('title', new sfWidgetFormInput());
        $this->form->setDefault('title', 'image %dd%');

        $this->form->setValidator('levelOfDescription', new sfValidatorString());

        $choices = [];
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item) {
            $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item;
        }

        $this->form->setWidget('levelOfDescription', new sfWidgetFormSelect(['choices' => $choices]));

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters(), $request->getFiles());
            if ($this->form->isValid()) {
                $this->processForm();
            }
        }
    }

    public function processForm()
    {
        $tmpPath = sfConfig::get('sf_upload_dir').'/tmp';

        // Upload files
        $i = 0;
        $informationObjectSlugList = [];

        foreach ($this->form->getValue('files') as $file) {
            if (0 == strlen($file['infoObjectTitle'] || 0 == strlen($file['tmpName']))) {
                continue;
            }

            ++$i;

            // Create an information object for this digital object
            $informationObject = new QubitInformationObject();
            $informationObject->parentId = $this->resource->id;

            if (0 < strlen($title = $file['infoObjectTitle'])) {
                $informationObject->title = $title;
            }

            if (null !== $levelOfDescription = $this->form->getValue('levelOfDescription')) {
                $params = $this->context->routing->parse(Qubit::pathInfo($levelOfDescription));
                $informationObject->levelOfDescription = $params['_sf_route']->resource;
            }

            $informationObject->setStatus(['typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID, 'statusId' => sfConfig::get('app_defaultPubStatus')]);

            // Save description
            $informationObject->save();

            if (file_exists("{$tmpPath}/{$file['tmpName']}")) {
                // Extract EXIF metadata before creating digital object
                $exifData = $this->extractExifMetadata("{$tmpPath}/{$file['tmpName']}");
                
                // Upload asset and create digital object
                $digitalObject = new QubitDigitalObject();
                $digitalObject->object = $informationObject;
                $digitalObject->usageId = QubitTerm::MASTER_ID;
                $digitalObject->assets[] = new QubitAsset($file['name'], file_get_contents("{$tmpPath}/{$file['tmpName']}"));

                $digitalObject->save();
                
                // Apply EXIF metadata to information object
                if ($exifData) {
                    $this->applyExifToInformationObject($exifData, $informationObject);
                }
            }

            $informationObjectSlugList[] = $informationObject->slug;

            // Clean up temp files
            if (file_exists("{$tmpPath}/{$file['tmpName']}")) {
                unlink("{$tmpPath}/{$file['tmpName']}");
            }
        }

        $this->redirect([$this->resource, 'module' => 'informationobject', 'action' => 'multiFileUpdate', 'items' => implode(',', $informationObjectSlugList)]);
    }

private function extractExifMetadata($filePath)
{
    if (!file_exists($filePath)) {
        return null;
    }

    // Use the helper class
    $meta = arEmbeddedMetadataParser::extract($filePath);
    
    if (!$meta) {
        return null;
    }

    // Extract specific fields for backward compatibility
    $extractedData = [
        'all_exif' => arEmbeddedMetadataParser::formatSummary($meta)
    ];

    // Get normalized data
    $norm = $meta['_norm'] ?? [];
    
    if (isset($norm['createDate'])) {
        $extractedData['date_taken'] = $norm['createDate'];
    }
    
    if (isset($norm['creator'])) {
        $extractedData['artist'] = $norm['creator'];
    }

    error_log("EXIF: Extracted data from multi-file upload: " . json_encode(array_keys($extractedData)));
    return $extractedData;
}

    /**
     * Apply EXIF metadata to the information object
     */
    private function applyExifToInformationObject($exifData, $informationObject)
    {
        if (!$exifData || !($informationObject instanceof QubitInformationObject)) {
            return;
        }

        // Handle creation date
        if (isset($exifData['date_taken'])) {
            $this->addCreationDate($exifData['date_taken'], $informationObject);
        }

        // Handle creator/artist
        if (isset($exifData['artist'])) {
            $this->addCreator($exifData['artist'], $informationObject);
        }

        // Add ALL EXIF data to physical characteristics
        if (isset($exifData['all_exif'])) {
            $this->addAllExifData($exifData['all_exif'], $informationObject);
        }

        $informationObject->save();
        error_log("EXIF: Applied EXIF data to information object from multi-file upload");
    }

private function addAllExifData($allExifText, $informationObject)
{
    try {
        $currentPhysical = $informationObject->getPhysicalCharacteristics();
        
        // Remove any existing EXIF data first
        if ($currentPhysical && strpos($currentPhysical, 'Technical Metadata:') !== false) {
            $currentPhysical = preg_replace('/\n\nTechnical Metadata:.*$/s', '', $currentPhysical);
            error_log("EXIF: Removed existing EXIF data from multi-file upload");
        }
        
        $newExifData = "\n\nTechnical Metadata:\n" . $allExifText;
        
        $informationObject->setPhysicalCharacteristics(($currentPhysical ?: '') . $newExifData);
        
        error_log("EXIF: Successfully added comprehensive EXIF data from multi-file upload");
        
    } catch (Exception $e) {
        error_log("EXIF: Error adding comprehensive EXIF data from multi-file upload: " . $e->getMessage());
    }
}

    /**
     * Add creation date from EXIF
     */
    private function addCreationDate($dateString, $informationObject)
    {
        try {
            $date = DateTime::createFromFormat('Y:m:d H:i:s', $dateString);
            if ($date) {
                // Check if creation date already exists
                $criteria = new Criteria();
                $criteria->add(QubitEvent::OBJECT_ID, $informationObject->id);
                $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);
                
                $existingEvent = QubitEvent::getOne($criteria);
                
                if (!$existingEvent) {
                    $event = new QubitEvent();
                    $event->setObjectId($informationObject->id);
                    $event->setTypeId(QubitTerm::CREATION_ID);
                    $event->setDate($date->format('Y-m-d'));
                    $event->save();
                    
                    error_log("EXIF: Added creation date from multi-file upload: " . $date->format('Y-m-d'));
                }
            }
        } catch (Exception $e) {
            error_log("Failed to parse EXIF date from multi-file upload: " . $e->getMessage());
        }
    }

    /**
     * Add creator from EXIF artist field
     */
    private function addCreator($artistName, $informationObject)
    {
        try {
            // Check if creator already exists
            $criteria = new Criteria();
            $criteria->add(QubitActor::AUTHORIZED_FORM_OF_NAME, $artistName);
            $actor = QubitActor::getOne($criteria);

            if (!$actor) {
                $actor = new QubitActor();
                $actor->setAuthorizedFormOfName($artistName);
                $actor->setEntityTypeId(QubitTerm::PERSON_ID);
                $actor->save();
            }

            // Check if relationship already exists
            $criteria = new Criteria();
            $criteria->add(QubitRelation::SUBJECT_ID, $actor->id);
            $criteria->add(QubitRelation::OBJECT_ID, $informationObject->id);
            $criteria->add(QubitRelation::TYPE_ID, QubitTerm::CREATION_ID);
            
            $existingRelation = QubitRelation::getOne($criteria);
            
            if (!$existingRelation) {
                $relation = new QubitRelation();
                $relation->setSubjectId($actor->id);
                $relation->setObjectId($informationObject->id);
                $relation->setTypeId(QubitTerm::CREATION_ID);
                $relation->save();
                
                error_log("EXIF: Added creator from multi-file upload: " . $artistName);
            }
        } catch (Exception $e) {
            error_log("Failed to add EXIF creator from multi-file upload: " . $e->getMessage());
        }
    }
}
