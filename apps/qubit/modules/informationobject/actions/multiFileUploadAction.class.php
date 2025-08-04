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

    /**
     * Extract EXIF metadata from uploaded image file
     */
    private function extractExifMetadata($filePath)
    {
        // Check if EXIF extension is loaded
        if (!extension_loaded('exif')) {
            return null;
        }

        // Check if file exists and is a supported image type
        if (!file_exists($filePath) || !$this->isSupportedImageType($filePath)) {
            return null;
        }

        try {
            $exif = exif_read_data($filePath, 'ANY_TAG', true);
            
            if (!$exif) {
                return null;
            }

            // Extract ALL relevant EXIF data
            $extractedData = [
                'all_exif' => $this->formatAllExifData($exif)
            ];

            // Still extract specific fields for other uses
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $extractedData['date_taken'] = $exif['EXIF']['DateTimeOriginal'];
            } elseif (isset($exif['EXIF']['DateTime'])) {
                $extractedData['date_taken'] = $exif['EXIF']['DateTime'];
            }

            if (isset($exif['IFD0']['Artist'])) {
                $extractedData['artist'] = trim($exif['IFD0']['Artist']);
            }

            error_log("EXIF: Extracted data from multi-file upload: " . json_encode(array_keys($extractedData)));
            return $extractedData;

        } catch (Exception $e) {
            error_log("EXIF extraction failed for multi-file upload {$filePath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Format all EXIF data into a readable string
     */
    private function formatAllExifData($exif)
    {
        $formatted = [];
        
        // Camera and basic info
        if (isset($exif['IFD0']['Make'])) $formatted[] = "Camera Make: " . $exif['IFD0']['Make'];
        if (isset($exif['IFD0']['Model'])) $formatted[] = "Camera Model: " . $exif['IFD0']['Model'];
        if (isset($exif['IFD0']['Software'])) $formatted[] = "Software: " . $exif['IFD0']['Software'];
        if (isset($exif['IFD0']['DateTime'])) $formatted[] = "File DateTime: " . $exif['IFD0']['DateTime'];
        
        // Image technical details
        if (isset($exif['EXIF']['DateTimeOriginal'])) $formatted[] = "Date Taken: " . $exif['EXIF']['DateTimeOriginal'];
        if (isset($exif['EXIF']['DateTimeDigitized'])) $formatted[] = "Date Digitized: " . $exif['EXIF']['DateTimeDigitized'];
        
        // Camera settings
        if (isset($exif['EXIF']['ExposureTime'])) $formatted[] = "Exposure Time: " . $exif['EXIF']['ExposureTime'] . " sec";
        if (isset($exif['EXIF']['FNumber'])) $formatted[] = "F-Number: f/" . $exif['EXIF']['FNumber'];
        if (isset($exif['EXIF']['ISOSpeedRatings'])) $formatted[] = "ISO: " . $exif['EXIF']['ISOSpeedRatings'];
        if (isset($exif['EXIF']['FocalLength'])) $formatted[] = "Focal Length: " . $exif['EXIF']['FocalLength'] . "mm";
        if (isset($exif['EXIF']['FocalLengthIn35mmFilm'])) $formatted[] = "35mm Equivalent: " . $exif['EXIF']['FocalLengthIn35mmFilm'] . "mm";
        
        // Additional camera settings
        if (isset($exif['EXIF']['ExposureProgram'])) {
            $exposurePrograms = [
                0 => 'Not defined', 1 => 'Manual', 2 => 'Normal program', 3 => 'Aperture priority',
                4 => 'Shutter priority', 5 => 'Creative program', 6 => 'Action program', 7 => 'Portrait mode',
                8 => 'Landscape mode'
            ];
            $programText = $exposurePrograms[$exif['EXIF']['ExposureProgram']] ?? $exif['EXIF']['ExposureProgram'];
            $formatted[] = "Exposure Program: " . $programText;
        }
        
        if (isset($exif['EXIF']['MeteringMode'])) {
            $meteringModes = [
                0 => 'Unknown', 1 => 'Average', 2 => 'Center-weighted average', 3 => 'Spot',
                4 => 'Multi-spot', 5 => 'Pattern', 6 => 'Partial', 255 => 'Other'
            ];
            $modeText = $meteringModes[$exif['EXIF']['MeteringMode']] ?? $exif['EXIF']['MeteringMode'];
            $formatted[] = "Metering Mode: " . $modeText;
        }
        
        if (isset($exif['EXIF']['Flash'])) $formatted[] = "Flash: " . ($exif['EXIF']['Flash'] ? 'Fired' : 'No flash');
        if (isset($exif['EXIF']['WhiteBalance'])) $formatted[] = "White Balance: " . ($exif['EXIF']['WhiteBalance'] == 0 ? 'Auto' : 'Manual');
        
        // Image properties
        if (isset($exif['COMPUTED']['Width'], $exif['COMPUTED']['Height'])) {
            $formatted[] = "Image Size: " . $exif['COMPUTED']['Width'] . " x " . $exif['COMPUTED']['Height'] . " pixels";
        }
        if (isset($exif['IFD0']['XResolution'], $exif['IFD0']['YResolution'])) {
            $formatted[] = "Resolution: " . $exif['IFD0']['XResolution'] . " x " . $exif['IFD0']['YResolution'] . " DPI";
        }
        
        // GPS information
        if (isset($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLongitude'])) {
            $lat = $this->convertGpsCoordinate($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
            $lon = $this->convertGpsCoordinate($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
            $formatted[] = "GPS Coordinates: " . $lat . ", " . $lon;
        }
        if (isset($exif['GPS']['GPSAltitude'])) {
            $altitude = $this->evaluateFraction($exif['GPS']['GPSAltitude']);
            $formatted[] = "GPS Altitude: " . $altitude . "m";
        }
        
        // Additional metadata
        if (isset($exif['IFD0']['Artist'])) $formatted[] = "Artist: " . $exif['IFD0']['Artist'];
        if (isset($exif['IFD0']['Copyright'])) $formatted[] = "Copyright: " . $exif['IFD0']['Copyright'];
        if (isset($exif['IFD0']['ImageDescription'])) $formatted[] = "Description: " . $exif['IFD0']['ImageDescription'];
        
        // File information
        if (isset($exif['FILE']['FileSize'])) $formatted[] = "File Size: " . round($exif['FILE']['FileSize'] / 1024, 1) . " KB";
        if (isset($exif['FILE']['MimeType'])) $formatted[] = "MIME Type: " . $exif['FILE']['MimeType'];
        
        return implode("\n", $formatted);
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

    /**
     * Add all EXIF data to physical characteristics
     */
    private function addAllExifData($allExifText, $informationObject)
    {
        try {
            $currentPhysical = $informationObject->getPhysicalCharacteristics();
            
            // Remove any existing EXIF data first
            if ($currentPhysical && strpos($currentPhysical, 'EXIF Technical Data:') !== false) {
                $currentPhysical = preg_replace('/\n\nEXIF Technical Data:.*$/s', '', $currentPhysical);
                error_log("EXIF: Removed existing EXIF data from multi-file upload");
            }
            
            $newExifData = "\n\nEXIF Technical Data:\n" . $allExifText;
            
            $informationObject->setPhysicalCharacteristics(($currentPhysical ?: '') . $newExifData);
            
            error_log("EXIF: Successfully added comprehensive EXIF data from multi-file upload");
            
        } catch (Exception $e) {
            error_log("EXIF: Error adding comprehensive EXIF data from multi-file upload: " . $e->getMessage());
        }
    }

    /**
     * Check if file is a supported image type for EXIF extraction
     */
    private function isSupportedImageType($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }
        
        $imageType = @exif_imagetype($filePath);
        $supportedTypes = [IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM];
        
        return in_array($imageType, $supportedTypes);
    }

    /**
     * Convert GPS coordinate from EXIF format to decimal degrees
     */
    private function convertGpsCoordinate($coordinate, $hemisphere)
    {
        if (!is_array($coordinate) || count($coordinate) < 3) {
            return null;
        }

        $degrees = count($coordinate) > 0 ? $this->evaluateFraction($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? $this->evaluateFraction($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? $this->evaluateFraction($coordinate[2]) : 0;

        $flip = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
        
        $decimal = $flip * ($degrees + $minutes / 60 + $seconds / 3600);
        
        return round($decimal, 6);
    }

    /**
     * Evaluate fraction strings from EXIF data
     */
    private function evaluateFraction($fraction)
    {
        if (is_numeric($fraction)) {
            return (float)$fraction;
        }

        $parts = explode('/', (string)$fraction);
        if (count($parts) == 2 && $parts[1] != 0) {
            return $parts[0] / $parts[1];
        }

        return (float)$fraction;
    }
}