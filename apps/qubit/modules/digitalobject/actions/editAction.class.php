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

/**
 * Digital Object edit component.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectEditAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        $this->resource = $this->getRoute()->resource;

        // Check that resource exists
        if (!isset($this->resource)) {
            $this->forward404();
        }

        $this->object = $this->resource->object;

        // Check user authorization
        if (
            !QubitAcl::check($this->object, 'update')
            && !$this->getUser()->hasGroup(QubitAcl


::EDITOR_ID)
        ) {
            QubitAcl::forwardUnauthorized();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        // Get representations
        $this->representations = [
            QubitTerm::REFERENCE_ID => $this->resource->getChildByUsageId(QubitTerm::REFERENCE_ID),
            QubitTerm::THUMBNAIL_ID => $this->resource->getChildByUsageId(QubitTerm::THUMBNAIL_ID),
        ];

        // Get video track files
        $this->videoTracks = [
            QubitTerm::CHAPTERS_ID => $this->resource->getChildByUsageId(QubitTerm::CHAPTERS_ID),
            QubitTerm::SUBTITLES_ID => $this->resource->getChildByUsageId(QubitTerm::SUBTITLES_ID),
        ];

        $this->addFormFields();

        // Process forms
        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters(), $request->getFiles());
            if ($this->form->isValid()) {
                $this->processForm();

                $this->resource->save();

                if ($this->object instanceof QubitInformationObject) {
                    $this->object->updateXmlExports();
                }

                $this->redirect([$this->object, 'module' => 'informationobject']);
            }
        }
    }

    /**
     * Update digital object properties, or upload new digital object derivatives.
     *
     * @return DigitalObjectEditAction this action
     */
    public function processForm()
    {
		error_log("=== PROCESSFORM CALLED ===");
        // Set property 'displayAsCompound'
        $this->resource->setDisplayAsCompoundObject($this->form->getValue('displayAsCompound'));

        $this->resource->setDigitalObjectAltText($this->form->getValue('digitalObjectAltText'));

        // Update media type
        $this->resource->mediaTypeId = $this->form->getValue('mediaType');

        // Upload new representations
        $uploadedFiles = [];
        foreach ($this->representations as $usageId => $representation) {
            if (null !== $uploadedFile = $this->form->getValue("repFile_{$usageId}")) {
                $uploadedFiles[$usageId] = $uploadedFile;
            }
        }

        foreach ($uploadedFiles as $usageId => $uploadFile) {
            $content = file_get_contents($uploadFile->getTempName());

            // Extract EXIF metadata before processing the image
            $exifData = $this->extractExifMetadata($uploadFile->getTempName());

            if (QubitDigitalObject::isImageFile($uploadFile->getOriginalName())) {
                $tmpFile = Qubit::saveTemporaryFile($uploadFile->getOriginalName(), $content);

                if (QubitTerm::REFERENCE_ID == $usageId) {
                    $maxwidth = (sfConfig::get('app_reference_image_maxwidth')) ? sfConfig::get('app_reference_image_maxwidth') : 480;
                    $maxheight = null;
                } elseif (QubitTerm::THUMBNAIL_ID == $usageId) {
                    list($maxwidth, $maxheight) = QubitDigitalObject::getImageMaxDimensions(QubitTerm::THUMBNAIL_ID);
                }

                $content = QubitDigitalObject::resizeImage($tmpFile, $maxwidth, $maxheight);

                @unlink($tmpFile);
            }

            $representation = new QubitDigitalObject();
            $representation->usageId = $usageId;
            $representation->assets[] = new QubitAsset($uploadFile->getOriginalName(), $content);
            $representation->parentId = $this->resource->id;
            $representation->createDerivatives = false;

            $representation->save();

            // Apply EXIF metadata to the information object (only for reference images)
            if (QubitTerm::REFERENCE_ID == $usageId && $exifData && $this->object instanceof QubitInformationObject) {
                $this->applyExifToInformationObject($exifData);
            }
        }

        // Upload new video track files
        $uploadedTracks = [];
        foreach ($this->videoTracks as $usageId => $videoTrack) {
            if (null !== $uploadedTrack = $this->form->getValue("trackFile_{$usageId}")) {
                $lang = $this->form->getValue("lang_{$usageId}");
                $uploadedTracks[$usageId] = ['track' => $uploadedTrack, 'language' => $lang];
            }
        }

        foreach ($uploadedTracks as $usageId => $uploadTrack) {
            $content = file_get_contents($uploadTrack['track']->getTempName());

            $track = new QubitDigitalObject();
            $track->usageId = $usageId;
            $track->assets[] = new QubitAsset($uploadTrack['track']->getOriginalName(), $content);
            $track->parentId = $this->resource->id;
            $track->createDerivatives = false;
            $track->language = $uploadTrack['language'];

            $track->save();
        }

        // Generate new reference
        if (null != $this->form->getValue('generateDerivative_'.QubitTerm::REFERENCE_ID)) {
            $this->resource->createReferenceImage();
        }

        // Generate new thumb
        if (null != $this->form->getValue('generateDerivative_'.QubitTerm::THUMBNAIL_ID)) {
            $this->resource->createThumbnail();
        }

        // Store latitude and longitude as properties
        foreach (['latitude', 'longitude'] as $geoPropertyField) {
            // Create or update property
            $geoProperty = $this->resource->getPropertyByName($geoPropertyField);

            // Intialize property if new
            if (empty($geoProperty->objectId)) {
                $geoProperty = new QubitProperty();
                $geoProperty->objectId = $this->resource->id;
                $geoProperty->editable = true;
                $geoProperty->name = $geoPropertyField;
            }

            // Set value and save
            $geoProperty->value = $this->form->getValue($geoPropertyField);
            $geoProperty->save();
        }
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

			return $extractedData;

		} catch (Exception $e) {
			error_log("EXIF extraction failed for {$filePath}: " . $e->getMessage());
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
		if (isset($exif['EXIF']['ExposureMode'])) {
			$exposureModes = [0 => 'Auto', 1 => 'Manual', 2 => 'Auto bracket'];
			$modeText = $exposureModes[$exif['EXIF']['ExposureMode']] ?? $exif['EXIF']['ExposureMode'];
			$formatted[] = "Exposure Mode: " . $modeText;
		}
		
		// Image properties
		if (isset($exif['COMPUTED']['Width'], $exif['COMPUTED']['Height'])) {
			$formatted[] = "Image Size: " . $exif['COMPUTED']['Width'] . " x " . $exif['COMPUTED']['Height'] . " pixels";
		}
		if (isset($exif['IFD0']['XResolution'], $exif['IFD0']['YResolution'])) {
			$formatted[] = "Resolution: " . $exif['IFD0']['XResolution'] . " x " . $exif['IFD0']['YResolution'] . " DPI";
		}
		if (isset($exif['EXIF']['ColorSpace'])) {
			$colorSpace = $exif['EXIF']['ColorSpace'] == 1 ? 'sRGB' : 'Uncalibrated';
			$formatted[] = "Color Space: " . $colorSpace;
		}
		
		// GPS information
		if (isset($exif['GPS'])) {
			if (isset($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLongitude'])) {
				$lat = $this->convertGpsCoordinate($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
				$lon = $this->convertGpsCoordinate($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
				$formatted[] = "GPS Coordinates: " . $lat . ", " . $lon;
			}
			if (isset($exif['GPS']['GPSAltitude'])) {
				$altitude = $this->evaluateFraction($exif['GPS']['GPSAltitude']);
				$formatted[] = "GPS Altitude: " . $altitude . "m";
			}
			if (isset($exif['GPS']['GPSDateStamp'], $exif['GPS']['GPSTimeStamp'])) {
				$formatted[] = "GPS Date/Time: " . $exif['GPS']['GPSDateStamp'] . " " . implode(':', $exif['GPS']['GPSTimeStamp']);
			}
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
	private function applyExifToInformationObject($exifData)
	{
		if (!$exifData || !($this->object instanceof QubitInformationObject)) {
			return;
		}

		// Handle creation date
		if (isset($exifData['date_taken'])) {
			$this->addCreationDate($exifData['date_taken']);
		}

		// Handle creator/artist
		if (isset($exifData['artist'])) {
			$this->addCreator($exifData['artist']);
		}

		// Add ALL EXIF data to physical characteristics
		if (isset($exifData['all_exif'])) {
			$this->addAllExifData($exifData['all_exif']);
		}

		// Save the information object
		$this->object->save();
	}

    /**
     * Add creation date from EXIF
     */
	private function addCreationDate($dateString)
	{
		try {
			$date = DateTime::createFromFormat('Y:m:d H:i:s', $dateString);
			if ($date) {
				// Check if creation date already exists - use correct constant
				$criteria = new Criteria();
				$criteria->add(QubitEvent::OBJECT_ID, $this->object->id);  // Changed from INFORMATION_OBJECT_ID
				$criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);
				
				$existingEvent = QubitEvent::getOne($criteria);
				
				if (!$existingEvent) {
					$event = new QubitEvent();
					$event->setObjectId($this->object->id);  // Changed from setInformationObjectId
					$event->setTypeId(QubitTerm::CREATION_ID);
					$event->setDate($date->format('Y-m-d'));
					$event->save();
					
					error_log("EXIF: Added creation date: " . $date->format('Y-m-d'));
				} else {
					error_log("EXIF: Creation date already exists, skipping");
				}
			}
		} catch (Exception $e) {
			error_log("Failed to parse EXIF date: " . $e->getMessage());
		}
	} 

    /**
     * Add creator from EXIF artist field
     */
    private function addCreator($artistName)
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
            $criteria->add(QubitRelation::OBJECT_ID, $this->object->id);
            $criteria->add(QubitRelation::TYPE_ID, QubitTerm::CREATION_ID);
            
            $existingRelation = QubitRelation::getOne($criteria);
            
            if (!$existingRelation) {
                $relation = new QubitRelation();
                $relation->setSubjectId($actor->id);
                $relation->setObjectId($this->object->id);
                $relation->setTypeId(QubitTerm::CREATION_ID);
                $relation->save();
            }
        } catch (Exception $e) {
            error_log("Failed to add EXIF creator: " . $e->getMessage());
        }
    }

    /**
     * Add content to scope and content field
     */
    private function addToScopeAndContent($content, $label)
    {
        $currentScope = $this->object->getScopeAndContent();
        $newContent = "\n\n{$label}: {$content}";
        
        // Only add if not already present
        if (strpos($currentScope, $newContent) === false) {
            $this->object->setScopeAndContent($currentScope . $newContent);
        }
    }

	/**
	 * Add technical metadata to physical characteristics
	 */
	private function addTechnicalMetadata($exifData)
	{
		error_log("EXIF: Starting addTechnicalMetadata");
		
		$technicalInfo = [];
		
		if (isset($exifData['camera_make'])) {
			$technicalInfo[] = "Camera: " . $exifData['camera_make'];
		}
		if (isset($exifData['camera_model'])) {
			$technicalInfo[] = "Model: " . $exifData['camera_model'];
		}
		if (isset($exifData['technical_data'])) {
			foreach ($exifData['technical_data'] as $key => $value) {
				$technicalInfo[] = ucfirst(str_replace('_', ' ', $key)) . ": " . $value;
			}
		}

		if (!empty($technicalInfo)) {
			try {
				$currentPhysical = $this->object->getPhysicalCharacteristics();
				$newTechnical = "\n\nEXIF Technical Data:\n" . implode("; ", $technicalInfo);
				
				// Only add if not already present
				if (strpos($currentPhysical ?: '', $newTechnical) === false) {
					$this->object->setPhysicalCharacteristics(($currentPhysical ?: '') . $newTechnical);
					
					// Force save immediately
					$this->object->save();
					error_log("EXIF: Successfully saved technical data to physical characteristics");
					
					// Verify it was saved
					$savedPhysical = $this->object->getPhysicalCharacteristics();
					error_log("EXIF: Verified saved data: " . substr($savedPhysical, -200)); // Log last 200 chars
				} else {
					error_log("EXIF: Technical data already present");
				}
			} catch (Exception $e) {
				error_log("EXIF: Error saving technical data: " . $e->getMessage());
			}
		} else {
			error_log("EXIF: No technical info to save");
		}
	}

	/**
	 * Add all EXIF data to physical characteristics
	 */
	private function addAllExifData($allExifText)
	{
		try {
			$currentPhysical = $this->object->getPhysicalCharacteristics();
			
			// Remove any existing EXIF data first
			if ($currentPhysical && strpos($currentPhysical, 'EXIF Technical Data:') !== false) {
				// Remove everything from "EXIF Technical Data:" to the end or next section
				$currentPhysical = preg_replace('/\n\nEXIF Technical Data:.*$/s', '', $currentPhysical);
				error_log("EXIF: Removed existing EXIF data");
			}
			
			$newExifData = "\n\nEXIF Technical Data:\n" . $allExifText;
			
			$this->object->setPhysicalCharacteristics(($currentPhysical ?: '') . $newExifData);
			$this->object->save(); // Force save immediately
			
			error_log("EXIF: Successfully added comprehensive EXIF data");
			
		} catch (Exception $e) {
			error_log("EXIF: Error adding comprehensive EXIF data: " . $e->getMessage());
		}
	}

    /**
     * Add GPS coordinates as properties
     */
    private function addGpsCoordinates($latitude, $longitude)
    {
        // Set latitude property
        $latProperty = $this->resource->getPropertyByName('latitude');
        if (empty($latProperty->objectId)) {
            $latProperty = new QubitProperty();
            $latProperty->objectId = $this->resource->id;
            $latProperty->editable = true;
            $latProperty->name = 'latitude';
        }
        $latProperty->value = $latitude;
        $latProperty->save();

        // Set longitude property
        $lonProperty = $this->resource->getPropertyByName('longitude');
        if (empty($lonProperty->objectId)) {
            $lonProperty = new QubitProperty();
            $lonProperty->objectId = $this->resource->id;
            $lonProperty->editable = true;
            $lonProperty->name = 'longitude';
        }
        $lonProperty->value = $longitude;
        $lonProperty->save();
    }

    /**
     * Add copyright information
     */
    private function addCopyrightInfo($copyright)
    {
        $this->addToScopeAndContent($copyright, 'Copyright');
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

    protected function addFormFields()
    {
        // Media type field
        $choices = [];
        $criteria = new Criteria();
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);
        foreach (QubitTerm::get($criteria) as $item) {
            $choices[$item->id] = $item->getName(['cultureFallback' => true]);
        }

        asort($choices); // Sort media types by name

        $this->form->setValidator('mediaType', new sfValidatorChoice(['choices' => array_keys($choices)]));
        $this->form->setWidget('mediaType', new sfWidgetFormSelect(['choices' => $choices]));
        $this->form->setDefault('mediaType', $this->resource->mediaTypeId);

        // Only display "compound digital object" toggle if we have a child with a
        // digital object
        $this->showCompoundObjectToggle = false;
        if ($this->object instanceof QubitInformationObject) {
            foreach ($this->object->getChildren() as $item) {
                if (null !== $item->getDigitalObject()) {
                    $this->showCompoundObjectToggle = true;

                    break;
                }
            }
        }

        if ($this->showCompoundObjectToggle) {
            $this->form->setValidator('displayAsCompound', new sfValidatorBoolean());
            $this->form->setWidget('displayAsCompound', new sfWidgetFormSelectRadio(
                ['choices' => [
                    '1' => $this->context->i18n->__('Yes'),
                    '0' => $this->context->i18n->__('No'),
                ]]
            ));

            // Set "displayAsCompound" value from QubitProperty
            $criteria = new Criteria();
            $criteria->add(QubitProperty::OBJECT_ID, $this->resource->id);
            $criteria->add(QubitProperty::NAME, 'displayAsCompound');

            if (null != $compoundProperty = QubitProperty::getOne($criteria)) {
                $this->form->setDefault('displayAsCompound', $compoundProperty->getValue(['sourceCulture' => true]));
            }
        }

        $this->form->setValidator('digitalObjectAltText', new sfValidatorString());
        $this->form->setWidget('digitalObjectAltText', new sfWidgetFormTextarea());
        if (null !== $this->digitalObjectAltText = $this->resource->getDigitalObjectAltText()) {
            $this->form->setDefault('digitalObjectAltText', $this->digitalObjectAltText);
        }

        $maxUploadSize = QubitDigitalObject::getMaxUploadSize();

        ProjectConfiguration::getActive()->loadHelpers('Qubit');

        // If reference representation doesn't exist, include upload widget
        foreach ($this->representations as $usageId => $representation) {
            if (null === $representation) {
                $repName = "repFile_{$usageId}";
                $derName = "generateDerivative_{$usageId}";

                $this->form->setValidator($repName, new sfValidatorFile());
                $this->form->setWidget($repName, new sfWidgetFormInputFile());

                if (-1 < $maxUploadSize) {
                    $this->form->getWidgetSchema()->{$repName}->setHelp($this->context->i18n->__('Max. size ~%1%', ['%1%' => hr_filesize($maxUploadSize)]));
                } else {
                    $this->form->getWidgetSchema()->{$repName}->setHelp('');
                }

                // Add "auto-generate" checkbox
                $this->form->setValidator($derName, new sfValidatorBoolean());
                $this->form->setWidget($derName, new sfWidgetFormInputCheckbox([], ['value' => 1]));
            }
        }

        // If video track doesn't exist, include upload widget
        // But always include subtitle upload widget
        foreach ($this->videoTracks as $usageId => $videoTrack) {
            if (QubitTerm::SUBTITLES_ID != $usageId) {
                if (null === $videoTrack) {
                    $trackName = "trackFile_{$usageId}";

                    $this->form->setValidator($trackName, new sfValidatorAnd([
                        new QubitValidatorMimeType(['mime_types' => ['text/vtt', 'application/x-subrip']]),
                        new sfValidatorFile(),
                    ]));
                    $this->form->setWidget($trackName, new sfWidgetFormInputFile());

                    if (-1 < $maxUploadSize) {
                        $this->form->getWidgetSchema()->{$trackName}->setHelp($this->context->i18n->__('Max. size ~%1%', ['%1%' => hr_filesize($maxUploadSize)]));
                    } else {
                        $this->form->getWidgetSchema()->{$trackName}->setHelp('');
                    }
                }
            } else {
                $trackName = "trackFile_{$usageId}";
                $langName = "lang_{$usageId}";

                $this->form->setValidator($trackName, new sfValidatorAnd([
                    new QubitValidatorMimeType(['mime_types' => ['text/vtt', 'application/x-subrip']]),
                    new sfValidatorFile(),
                ]));
                $this->form->setWidget($trackName, new sfWidgetFormInputFile());

                $this->form->setValidator($langName, new sfValidatorI18nChoiceLanguage());
                $this->form->setWidget($langName, new sfWidgetFormI18nChoiceLanguage());

                if (-1 < $maxUploadSize) {
                    $this->form->getWidgetSchema()->{$trackName}->setHelp($this->context->i18n->__('Max. size ~%1%', ['%1%' => hr_filesize($maxUploadSize)]));
                } else {
                    $this->form->getWidgetSchema()->{$trackName}->setHelp('');
                }
            }
        }

        // Add latitude and longitude fields
        foreach (['latitude', 'longitude'] as $geoPropertyField) {
            $this->form->setValidator($geoPropertyField, new sfValidatorNumber());
            $this->form->setWidget($geoPropertyField, new sfWidgetFormInput());

            $fieldProperty = $this->resource->getPropertyByName($geoPropertyField);
            if (isset($fieldProperty->value)) {
                $this->form->setDefault($geoPropertyField, $fieldProperty->value);
            }
        }
    }
		
}