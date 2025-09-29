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
 * @author     david juhasz <david@artefactual.com>
 */
class ObjectAddDigitalObjectAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->form
            ->getValidatorSchema()
            ->setOption("allow_extra_fields", true);

        $this->resource = $this->getRoute()->resource;

        // Get repository to test upload limits
        if ($this->resource instanceof QubitInformationObject) {
            $this->repository = $this->resource->getRepository([
                "inherit" => true,
            ]);
        } elseif ($this->resource instanceof QubitActor) {
            $this->repository = $this->resource->getMaintainingRepository();
        }

        // Check that object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->forward404();
        }

        // Assemble resource description
        sfContext::getInstance()
            ->getConfiguration()
            ->loadHelpers(["Qubit"]);

        if ($this->resource instanceof QubitActor) {
            $this->resourceDescription = render_title($this->resource);
        } elseif ($this->resource instanceof QubitInformationObject) {
            $this->resourceDescription = "";

            if (isset($this->resource->identifier)) {
                $this->resourceDescription .=
                    $this->resource->identifier . " - ";
            }

            $this->resourceDescription .= render_title(
                new sfIsadPlugin($this->resource)
            );
        }

        // Check if already exists a digital object
        if (null !== ($digitalObject = $this->resource->getDigitalObject())) {
            //NARSSA/SITA JJP multiple object upload enabled
            // $this->redirect([$digitalObject, 'module' => 'digitalobject', 'action' => 'edit']);
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, "update")) {
            QubitAcl::forwardUnauthorized();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        // Add form fields
        $this->addFields($request);

        // Process form
        if ($request->isMethod("post")) {
            $this->form->bind(
                $request->getPostParameters(),
                $request->getFiles()
            );
            if ($this->form->isValid()) {
                $this->processForm();

                $this->resource->save();

                if ($this->resource instanceof QubitInformationObject) {
                    $this->resource->updateXmlExports();
                }
                $this->redirect([$this->resource, "module" => "object"]);
            }
        }
    }

    /**
     * Upload the asset selected by user and create a digital object with appropriate
     * representations.
     *
     * @return DigitalObjectEditAction this action
     */
    public function processForm()
    {
        $digitalObject = new QubitDigitalObject();

        if (null !== $this->form->getValue("file")) {
            $tempFilePath = $this->form->getValue("file")->getTempName();
            $name = $this->form->getValue("file")->getOriginalName();
            $content = file_get_contents($tempFilePath);

            // Extract comprehensive metadata from the uploaded file
            $metadataCollection = $this->extractComprehensiveMetadata(
                $tempFilePath
            );

            // Set up digital object with assets FIRST
            $digitalObject->assets[] = new QubitAsset($name, $content);
            $digitalObject->usageId = QubitTerm::MASTER_ID;

            // Apply comprehensive metadata to information object (this sets GPS coordinates)
            if (
                $metadataCollection &&
                $this->resource instanceof QubitInformationObject
            ) {
                $this->applyMetadataToInformationObject($metadataCollection);
            }

            // Add digital object to resource
            $this->resource->digitalObjectsRelatedByobjectId[] = $digitalObject;

            // NOW check for GPS coordinates (after metadata is processed)
            error_log(
                "GPS DEBUG: processForm - checking for GPS coordinates after comprehensive metadata"
            );
            error_log(
                "GPS DEBUG: processForm - has gpsLatitude: " .
                    (isset($this->gpsLatitude)
                        ? "YES (" . $this->gpsLatitude . ")"
                        : "NO")
            );
            error_log(
                "GPS DEBUG: processForm - has gpsLongitude: " .
                    (isset($this->gpsLongitude)
                        ? "YES (" . $this->gpsLongitude . ")"
                        : "NO")
            );

            if (isset($this->gpsLatitude) && isset($this->gpsLongitude)) {
                try {
                    error_log(
                        "GPS DEBUG: processForm - about to save digital object"
                    );
                    $digitalObject->save(); // Ensure digital object has an ID
                    error_log(
                        "GPS DEBUG: processForm - digital object saved successfully, ID: " .
                            $digitalObject->id
                    );

                    error_log(
                        "GPS DEBUG: processForm - about to call setGpsCoordinatesOnDigitalObject"
                    );
                    $this->setGpsCoordinatesOnDigitalObject($digitalObject);
                    error_log(
                        "GPS DEBUG: processForm - setGpsCoordinatesOnDigitalObject completed"
                    );
                } catch (Exception $e) {
                    error_log(
                        "GPS DEBUG: processForm - EXCEPTION: " .
                            $e->getMessage()
                    );
                    error_log(
                        "GPS DEBUG: processForm - STACK TRACE: " .
                            $e->getTraceAsString()
                    );
                }
            } else {
                error_log("GPS DEBUG: processForm - no GPS coordinates to set");
            }

            error_log("GPS DEBUG: processForm - GPS section completed");

            //Exif The AHG
            if (
                isset($digitalObject) &&
                $digitalObject instanceof QubitDigitalObject
            ) {
                $this->appendEmbeddedTechMetadata($digitalObject);
            } elseif (
                isset($this->digitalObject) &&
                $this->digitalObject instanceof QubitDigitalObject
            ) {
                $this->appendEmbeddedTechMetadata($this->digitalObject);
            }
        } elseif (null !== $this->form->getValue("url")) {
            // Catch errors trying to download remote resource
            try {
                $digitalObject->importFromURI($this->form->getValue("url"));
                $this->resource->digitalObjectsRelatedByobjectId[] = $digitalObject;
            } catch (sfException $e) {
                // Log download exception
                $this->logMessage($e->getMessage(), "err");
            }
        }
    }

    private function appendEmbeddedTechMetadata($digitalObject)
    {
        try {
            if (
                class_exists("arEmbeddedMetadataParser", /*autoload*/ true) &&
                isset($digitalObject) &&
                $digitalObject instanceof QubitDigitalObject
            ) {
                $absPath = method_exists($digitalObject, "getAbsolutePath")
                    ? $digitalObject->getAbsolutePath()
                    : (string) $digitalObject->getPath();

                if ($absPath && is_readable($absPath)) {
                    $meta = arEmbeddedMetadataParser::extract($absPath);
                    if (is_array($meta)) {
                        $summary = arEmbeddedMetadataParser::formatSummary(
                            $meta
                        );
                        $io = isset($this->resource)
                            ? $this->resource
                            : (isset($this->informationObject)
                                ? $this->informationObject
                                : null);

                        if (
                            $io instanceof QubitInformationObject &&
                            $summary !== ""
                        ) {
                            $existing = (string) $io->physicalCharacteristics;
                            $io->physicalCharacteristics = $existing
                                ? $existing . "\n\n" . $summary
                                : $summary;
                            $io->save();
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            // swallow everything
        }
    }

    /**
     * Apply EXIF metadata to the information object
     */
    private function applyExifToInformationObject($exifData)
    {
        if (
            !$exifData ||
            !($this->resource instanceof QubitInformationObject)
        ) {
            return;
        }

        // Handle creation date
        if (isset($exifData["date_taken"])) {
            $this->addCreationDate($exifData["date_taken"]);
        }

        // Handle creator/artist
        if (isset($exifData["artist"])) {
            $this->addCreator($exifData["artist"]);
        }

        // Add ALL EXIF data to physical characteristics
        if (isset($exifData["all_exif"])) {
            $this->addAllExifData($exifData["all_exif"]);
        }

        error_log(
            "EXIF: Applied EXIF data to information object from master upload"
        );
    }

    /**
     * Add creation date from EXIF
     */
    private function addCreationDate($dateString)
    {
        try {
            $date = DateTime::createFromFormat("Y:m:d H:i:s", $dateString);
            if ($date) {
                // Check if creation date already exists
                $criteria = new Criteria();
                $criteria->add(QubitEvent::OBJECT_ID, $this->resource->id);
                $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);

                $existingEvent = QubitEvent::getOne($criteria);

                if (!$existingEvent) {
                    $event = new QubitEvent();
                    $event->setObjectId($this->resource->id);
                    $event->setTypeId(QubitTerm::CREATION_ID);
                    $event->setDate($date->format("Y-m-d"));
                    $event->save();

                    error_log(
                        "EXIF: Added creation date from master upload: " .
                            $date->format("Y-m-d")
                    );
                }
            }
        } catch (Exception $e) {
            error_log(
                "Failed to parse EXIF date from master upload: " .
                    $e->getMessage()
            );
        }
    }

    /**
     * Add creator from metadata - AtoM 2.9 Compatible
     */
    private function addCreator($creatorName)
    {
        try {
            error_log("METADATA: Attempting to add creator: " . $creatorName);

            // Search for existing actor using i18n table (correct method for AtoM 2.9)
            $criteria = new Criteria();
            $criteria->add(
                QubitActorI18n::AUTHORIZED_FORM_OF_NAME,
                $creatorName
            );
            $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
            $actor = QubitActor::getOne($criteria);

            if (!$actor) {
                error_log("METADATA: Creating new actor: " . $creatorName);
                $actor = new QubitActor();
                $actor->setAuthorizedFormOfName($creatorName);
                $actor->setEntityTypeId(QubitTerm::PERSON_ID);
                $actor->save();
                error_log("METADATA: New actor created with ID: " . $actor->id);
            } else {
                error_log(
                    "METADATA: Found existing actor with ID: " . $actor->id
                );
            }

            // Check if relationship already exists
            $criteria = new Criteria();
            $criteria->add(QubitRelation::SUBJECT_ID, $actor->id);
            $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
            $criteria->add(QubitRelation::TYPE_ID, QubitTerm::CREATION_ID);

            $existingRelation = QubitRelation::getOne($criteria);

            if (!$existingRelation) {
                error_log(
                    "METADATA: Creating relation between actor " .
                        $actor->id .
                        " and object " .
                        $this->resource->id
                );
                $relation = new QubitRelation();
                $relation->setSubjectId($actor->id);
                $relation->setObjectId($this->resource->id);
                $relation->setTypeId(QubitTerm::CREATION_ID);
                $relation->save();

                error_log(
                    "METADATA: Successfully added creator relation: " .
                        $creatorName
                );
            } else {
                error_log(
                    "METADATA: Relation already exists for creator: " .
                        $creatorName
                );
            }

            // CRITICAL: Also create Event for Context area display
            $criteria = new Criteria();
            $criteria->add(QubitEvent::OBJECT_ID, $this->resource->id);
            $criteria->add(QubitEvent::ACTOR_ID, $actor->id);
            $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);

            $existingEvent = QubitEvent::getOne($criteria);

            if (!$existingEvent) {
                error_log("METADATA: Creating creation event for Context area");
                $event = new QubitEvent();
                $event->setObjectId($this->resource->id);
                $event->setActorId($actor->id);
                $event->setTypeId(QubitTerm::CREATION_ID);
                $event->save();

                error_log(
                    "METADATA: Successfully added creator event for Context area: " .
                        $creatorName
                );
            } else {
                error_log(
                    "METADATA: Creation event already exists for creator: " .
                        $creatorName
                );
            }
        } catch (Exception $e) {
            error_log(
                "METADATA: Failed to add creator '" .
                    $creatorName .
                    "': " .
                    $e->getMessage()
            );
            error_log("METADATA: Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Add subject access points from keywords - AtoM 2.9 Compatible
     */
    private function addSubjectAccessPoints($keywords)
    {
        try {
            error_log(
                "METADATA: Attempting to add " .
                    count($keywords) .
                    " subject access points"
            );

            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) {
                    continue;
                }

                error_log("METADATA: Processing keyword: " . $keyword);

                // Check if term already exists
                $criteria = new Criteria();
                $criteria->add(
                    QubitTerm::TAXONOMY_ID,
                    QubitTaxonomy::SUBJECT_ID
                );
                $criteria->add(QubitTermI18n::NAME, $keyword);
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);

                $term = QubitTerm::getOne($criteria);

                if (!$term) {
                    error_log("METADATA: Creating new term: " . $keyword);
                    $term = new QubitTerm();
                    $term->setTaxonomyId(QubitTaxonomy::SUBJECT_ID);
                    $term->setName($keyword);
                    $term->save();
                    error_log(
                        "METADATA: New term created with ID: " . $term->id
                    );
                } else {
                    error_log(
                        "METADATA: Found existing term with ID: " . $term->id
                    );
                }

                // Check if relation already exists
                $criteria = new Criteria();
                $criteria->add(
                    QubitObjectTermRelation::OBJECT_ID,
                    $this->resource->id
                );
                $criteria->add(QubitObjectTermRelation::TERM_ID, $term->id);

                $existingRelation = QubitObjectTermRelation::getOne($criteria);

                if (!$existingRelation) {
                    error_log("METADATA: Creating object-term relation");
                    $relation = new QubitObjectTermRelation();
                    $relation->setObjectId($this->resource->id);
                    $relation->setTermId($term->id);
                    $relation->save();

                    error_log(
                        "METADATA: Successfully added subject access point: " .
                            $keyword
                    );
                } else {
                    error_log(
                        "METADATA: Relation already exists for keyword: " .
                            $keyword
                    );
                }
            }
        } catch (Exception $e) {
            error_log(
                "METADATA: Failed to add subject access points: " .
                    $e->getMessage()
            );
            error_log("METADATA: Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Apply metadata with enhanced title generation - AtoM 2.9 Compatible
     */
    private function applyMetadataToInformationObject($metadataCollection)
    {
        error_log("=== APPLY METADATA CALLED ===");
        error_log(
            "Current resource title: '" . $this->resource->getTitle() . "'"
        );
        error_log(
            "Current resource scope: '" .
                $this->resource->getScopeAndContent() .
                "'"
        );

        if (
            !$metadataCollection ||
            !($this->resource instanceof QubitInformationObject)
        ) {
            error_log(
                "DEBUG: Early return - metadataCollection: " .
                    ($metadataCollection ? "EXISTS" : "NULL") .
                    ", resource instanceof QubitInformationObject: " .
                    ($this->resource instanceof QubitInformationObject
                        ? "YES"
                        : "NO")
            );
            return;
        }

        // Priority order: XMP > IPTC > EXIF for overlapping fields

        // Handle title - enhanced EXIF extraction
        $title = null;
        if (isset($metadataCollection["xmp"]["title"])) {
            $title = $metadataCollection["xmp"]["title"];
            error_log("METADATA: Using XMP title: " . $title);
        } elseif (isset($metadataCollection["iptc"]["headline"])) {
            $title = $metadataCollection["iptc"]["headline"];
            error_log("METADATA: Using IPTC headline as title: " . $title);
        } elseif (isset($metadataCollection["exif"]["image_description"])) {
            $title = $metadataCollection["exif"]["image_description"];
            error_log("METADATA: Using EXIF description as title: " . $title);
        } else {
            // Generate descriptive title from EXIF data
            $title = $this->generateTitleFromExif($metadataCollection);
            if ($title) {
                error_log("METADATA: Generated title from EXIF: " . $title);
            }
        }
        //To Fix PSIS/AHG - Add flag to overwrite

        if ($title) {
            $currentTitle = trim($this->resource->getTitle());
            // Allow overwriting if current title is empty, default, or very short (likely placeholder)
            if (
                empty($currentTitle) ||
                strlen($currentTitle) <= 3 ||
                in_array(strtolower($currentTitle), [
                    "test",
                    "ss",
                    "gg",
                    "temp",
                    "new",
                ])
            ) {
                $this->resource->setTitle($title);
                error_log(
                    "METADATA: Set title from metadata: " .
                        $title .
                        " (replaced: '" .
                        $currentTitle .
                        "')"
                );
            } else {
                error_log(
                    "METADATA: Title not set - resource already has meaningful title: '" .
                        $currentTitle .
                        "'"
                );
            }
        } else {
            error_log("METADATA: No title generated from metadata");
        }

        // Handle description/scope and content - enhanced with EXIF details
        $description = null;
        if (isset($metadataCollection["xmp"]["description"])) {
            $description = $metadataCollection["xmp"]["description"];
            error_log("METADATA: Using XMP description");
        } elseif (isset($metadataCollection["iptc"]["caption"])) {
            $description = $metadataCollection["iptc"]["caption"];
            error_log("METADATA: Using IPTC caption");
        } else {
            // Generate description from EXIF data
            $description = $this->generateDescriptionFromExif(
                $metadataCollection
            );
            if ($description) {
                error_log("METADATA: Generated description from EXIF");
            }
        }

        if ($description) {
            $currentScope = $this->resource->getScopeAndContent();
            if (empty($currentScope)) {
                $this->resource->setScopeAndContent($description);
                error_log("METADATA: Set scope and content from metadata");
            } else {
                error_log(
                    "METADATA: Scope and content not set - already exists"
                );
            }
        }

        // Handle creation date (EXIF preferred for accuracy)
        $dateString = null;
        if (isset($metadataCollection["exif"]["date_taken"])) {
            $dateString = $metadataCollection["exif"]["date_taken"];
        }

        if ($dateString) {
            $this->addCreationDate($dateString);
        }

        // Handle creator/artist - enhanced with device info fallback
        $creator = null;
        if (isset($metadataCollection["xmp"]["creator"])) {
            $creator = $metadataCollection["xmp"]["creator"];
            error_log("METADATA: Using XMP creator: " . $creator);
        } elseif (isset($metadataCollection["iptc"]["creator"])) {
            $creator = $metadataCollection["iptc"]["creator"];
            error_log("METADATA: Using IPTC creator: " . $creator);
        } elseif (isset($metadataCollection["exif"]["artist"])) {
            $creator = $metadataCollection["exif"]["artist"];
            error_log("METADATA: Using EXIF artist: " . $creator);
        } else {
            // Generate creator from device information
            $creator = $this->generateCreatorFromExif($metadataCollection);
            if ($creator) {
                error_log("METADATA: Generated creator from EXIF: " . $creator);
            }
        }

        if ($creator) {
            $this->addCreator($creator);
        } else {
            error_log("METADATA: No creator found in metadata");
        }

        // Handle keywords/subject access points - enhanced with EXIF-derived terms
        $keywords = [];
        if (isset($metadataCollection["xmp"]["keywords"])) {
            $keywords = $metadataCollection["xmp"]["keywords"];
            error_log(
                "METADATA: Using XMP keywords: " . json_encode($keywords)
            );
        } elseif (isset($metadataCollection["iptc"]["keywords"])) {
            $keywords = $metadataCollection["iptc"]["keywords"];
            error_log(
                "METADATA: Using IPTC keywords: " . json_encode($keywords)
            );
        } else {
            // Generate subject terms from EXIF data
            $keywords = $this->generateKeywordsFromExif($metadataCollection);
            if (!empty($keywords)) {
                error_log(
                    "METADATA: Generated keywords from EXIF: " .
                        json_encode($keywords)
                );
            }
        }

        if (!empty($keywords)) {
            $this->addSubjectAccessPoints($keywords);
        } else {
            error_log("METADATA: No keywords found in metadata");
        }

        // Store GPS coordinates for later use (after digital object is created)
        // Add this in applyMetadataToInformationObject() after keywords section
        error_log("GPS DEBUG: Checking for GPS coordinates in metadata");
        error_log(
            "GPS DEBUG: metadataCollection keys: " .
                json_encode(array_keys($metadataCollection))
        );

        if (isset($metadataCollection["exif"])) {
            error_log(
                "GPS DEBUG: EXIF keys: " .
                    json_encode(array_keys($metadataCollection["exif"]))
            );
            if (isset($metadataCollection["exif"]["gps_latitude"])) {
                error_log(
                    "GPS DEBUG: Found GPS latitude: " .
                        $metadataCollection["exif"]["gps_latitude"]
                );
            }
            if (isset($metadataCollection["exif"]["gps_longitude"])) {
                error_log(
                    "GPS DEBUG: Found GPS longitude: " .
                        $metadataCollection["exif"]["gps_longitude"]
                );
            }
        }

        if (
            isset($metadataCollection["exif"]["gps_latitude"]) &&
            isset($metadataCollection["exif"]["gps_longitude"])
        ) {
            $this->gpsLatitude = $metadataCollection["exif"]["gps_latitude"];
            $this->gpsLongitude = $metadataCollection["exif"]["gps_longitude"];
            error_log(
                "GPS DEBUG: Stored GPS coordinates for digital object: Lat=" .
                    $this->gpsLatitude .
                    ", Lon=" .
                    $this->gpsLongitude
            );
        } else {
            error_log("GPS DEBUG: No GPS coordinates found in EXIF metadata");
        }

        // Add comprehensive technical metadata to physical characteristics
        $this->addComprehensiveMetadata($metadataCollection);

        error_log(
            "METADATA: Applied comprehensive metadata to information object from master upload"
        );
    }

    /**
     * Add all EXIF data to physical characteristics
     */
    private function addAllExifData($allExifText)
    {
        try {
            $currentPhysical = $this->resource->getPhysicalCharacteristics();

            // Remove any existing EXIF data first
            if (
                $currentPhysical &&
                strpos($currentPhysical, "EXIF Technical Data:") !== false
            ) {
                $currentPhysical = preg_replace(
                    '/\n\nEXIF Technical Data:.*$/s',
                    "",
                    $currentPhysical
                );
                error_log(
                    "EXIF: Removed existing EXIF data from master upload"
                );
            }

            $newExifData = "\n\nEXIF Technical Data:\n" . $allExifText;

            $this->resource->setPhysicalCharacteristics(
                ($currentPhysical ?: "") . $newExifData
            );

            error_log(
                "EXIF: Successfully added comprehensive EXIF data from master upload"
            );
        } catch (Exception $e) {
            error_log(
                "EXIF: Error adding comprehensive EXIF data from master upload: " .
                    $e->getMessage()
            );
        }
    }

    /**
     * Extract comprehensive metadata (EXIF, IPTC, XMP) from uploaded image file
     */
    private function extractComprehensiveMetadata($filePath)
    {
        if (!file_exists($filePath)) {
            return null;
        }

        // One call gets EXIF + IPTC + XMP via exiftool
        $meta = arEmbeddedMetadataParser::extract($filePath);

        if (!$meta) {
            return null;
        }

        $metadata = [];
        $norm = $meta["_norm"] ?? [];

        // Build metadata array from normalized data
        $exifData = [];

        // Dates
        if (isset($norm["createDate"])) {
            $exifData["date_taken"] = $norm["createDate"];
        }

        // Creator (could be from EXIF Artist, IPTC Byline, or XMP Creator)
        if (isset($norm["creator"])) {
            $exifData["artist"] = $norm["creator"];
        }

        // GPS (from EXIF GPS tags)
        if (isset($meta["GPSLatitude"]) && isset($meta["GPSLongitude"])) {
            $exifData["gps_latitude"] = $meta["GPSLatitude"];
            $exifData["gps_longitude"] = $meta["GPSLongitude"];
        }

        // Camera info
        if (isset($meta["Make"])) {
            $exifData["camera_make"] = $meta["Make"];
        }
        if (isset($meta["Model"])) {
            $exifData["camera_model"] = $meta["Model"];
        }

        // Technical details for title/description generation
        if (isset($meta["FocalLength"])) {
            $exifData["focal_length"] = $meta["FocalLength"];
        }
        if (isset($meta["FNumber"])) {
            $exifData["aperture"] = $meta["FNumber"];
        }
        if (isset($meta["ExposureTime"])) {
            $exifData["shutter_speed"] = $meta["ExposureTime"];
        }
        if (isset($meta["ISO"])) {
            $exifData["iso"] = $meta["ISO"];
        }
        if (isset($meta["ImageWidth"])) {
            $exifData["width"] = $meta["ImageWidth"];
        }
        if (isset($meta["ImageHeight"])) {
            $exifData["height"] = $meta["ImageHeight"];
        }

        // Description (could be from EXIF, IPTC Caption, or XMP Description)
        if (isset($norm["description"])) {
            $exifData["image_description"] = $norm["description"];
        }

        $metadata["exif"] = $exifData;

        // IPTC-specific (headline, caption from IPTC tags)
        $iptcData = [];
        if (isset($norm["title"])) {
            $iptcData["headline"] = $norm["title"];
        }
        if (isset($norm["description"])) {
            $iptcData["caption"] = $norm["description"];
        }
        if (isset($norm["creator"])) {
            $iptcData["creator"] = $norm["creator"];
        }
        if (isset($norm["rights"])) {
            $iptcData["copyright"] = $norm["rights"];
        }

        if (!empty($iptcData)) {
            $metadata["iptc"] = $iptcData;
        }

        // XMP-specific
        $xmpData = [];
        if (isset($norm["title"])) {
            $xmpData["title"] = $norm["title"];
        }
        if (isset($norm["description"])) {
            $xmpData["description"] = $norm["description"];
        }
        if (isset($norm["creator"])) {
            $xmpData["creator"] = $norm["creator"];
        }
        if (isset($norm["rights"])) {
            $xmpData["rights"] = $norm["rights"];
        }

        // Keywords from XMP Subject
        if (isset($meta["Subject"])) {
            $keywords = $meta["Subject"];
            if (is_string($keywords)) {
                $xmpData["keywords"] = array_map(
                    "trim",
                    explode(",", $keywords)
                );
            } elseif (is_array($keywords)) {
                $xmpData["keywords"] = $keywords;
            }
        }

        if (!empty($xmpData)) {
            $metadata["xmp"] = $xmpData;
        }

        return !empty($metadata) ? $metadata : null;
    }

    /**
     * Generate descriptive title from EXIF data
     */
    private function generateTitleFromExif($metadataCollection)
    {
        if (!isset($metadataCollection["exif"])) {
            return null;
        }

        $exif = $metadataCollection["exif"];
        $titleParts = [];

        // Use camera make/model as base
        if (isset($exif["camera_make"]) && isset($exif["camera_model"])) {
            $titleParts[] =
                "Photo taken with " .
                trim($exif["camera_make"] . " " . $exif["camera_model"]);
        }

        // Add date if available
        if (isset($exif["date_taken"])) {
            try {
                $date = DateTime::createFromFormat(
                    "Y:m:d H:i:s",
                    $exif["date_taken"]
                );
                if ($date) {
                    $titleParts[] = "captured on " . $date->format("F j, Y");
                }
            } catch (Exception $e) {
                // Continue without date
            }
        }

        // Add technical details if interesting
        if (isset($exif["focal_length"]) || isset($exif["aperture"])) {
            $techDetails = [];
            if (isset($exif["focal_length"])) {
                $focalLength = (float)$exif["focal_length"];
                $techDetails[] = $focalLength . "mm";
            }
            if (isset($exif["aperture"])) {
                $aperture = (float)$exif["aperture"];
                $techDetails[] = "f/" . $aperture;
            }
            if (!empty($techDetails)) {
                $titleParts[] = "(" . implode(", ", $techDetails) . ")";
            }
        }

        return !empty($titleParts) ? implode(" ", $titleParts) : null;
    }

    /**
     * Generate description from EXIF data
     */
    private function generateDescriptionFromExif($metadataCollection)
    {
        if (!isset($metadataCollection["exif"])) {
            return null;
        }

        $exif = $metadataCollection["exif"];
        $descriptionParts = [];

        // Camera and shooting information
        if (isset($exif["camera_make"]) && isset($exif["camera_model"])) {
            $descriptionParts[] =
                "Photograph captured using " .
                trim($exif["camera_make"] . " " . $exif["camera_model"]) .
                ".";
        }

        // Technical shooting details
        $techDetails = [];
        if (isset($exif["focal_length"])) {
            $focalLength = (float)$exif["focal_length"];
            $techDetails[] = "focal length: " . $focalLength . "mm";
        }
        if (isset($exif["aperture"])) {
            $aperture = (float)$exif["aperture"];
            $techDetails[] = "aperture: f/" . $aperture;
        }
        if (isset($exif["shutter_speed"])) {
            $shutterSpeed = (float)$exif["shutter_speed"];
            if ($shutterSpeed < 1) {
                $techDetails[] =
                    "shutter speed: 1/" . round(1 / $shutterSpeed) . "s";
            } else {
                $techDetails[] = "shutter speed: " . $shutterSpeed . "s";
            }
        }
        if (isset($exif["iso"])) {
            $techDetails[] = "ISO: " . $exif["iso"];
        }

        if (!empty($techDetails)) {
            $descriptionParts[] =
                "Camera settings: " . implode(", ", $techDetails) . ".";
        }

        // Image properties
        if (isset($exif["width"]) && isset($exif["height"])) {
            $descriptionParts[] =
                "Image dimensions: " .
                $exif["width"] .
                " × " .
                $exif["height"] .
                " pixels.";
        }

        // GPS information if available
        if (isset($exif["gps_latitude"]) && isset($exif["gps_longitude"])) {
            $descriptionParts[] =
                "Geographic location: " .
                $exif["gps_latitude"] .
                ", " .
                $exif["gps_longitude"] .
                ".";
        }

        return !empty($descriptionParts)
            ? implode(" ", $descriptionParts)
            : null;
    }

    /**
     * Generate creator from EXIF device information
     */
    private function generateCreatorFromExif($metadataCollection)
    {
        if (!isset($metadataCollection["exif"])) {
            return null;
        }

        $exif = $metadataCollection["exif"];

        // Try to create a meaningful creator name from device info
        if (isset($exif["camera_make"]) && isset($exif["camera_model"])) {
            $deviceName = trim(
                $exif["camera_make"] . " " . $exif["camera_model"]
            );

            // For mobile devices, create a more descriptive name
            if (
                stripos($deviceName, "huawei") !== false ||
                stripos($deviceName, "samsung") !== false ||
                stripos($deviceName, "iphone") !== false ||
                stripos($deviceName, "pixel") !== false
            ) {
                return "Mobile Photographer (" . $deviceName . ")";
            }

            // For traditional cameras
            if (
                stripos($deviceName, "canon") !== false ||
                stripos($deviceName, "nikon") !== false ||
                stripos($deviceName, "sony") !== false ||
                stripos($deviceName, "olympus") !== false
            ) {
                return "Photographer (" . $deviceName . ")";
            }

            // Generic fallback
            return "Photographer (" . $deviceName . ")";
        }

        return null;
    }

    /**
     * Generate keywords/subject terms from EXIF data
     */
    private function generateKeywordsFromExif($metadataCollection)
    {
        if (!isset($metadataCollection["exif"])) {
            return [];
        }

        $exif = $metadataCollection["exif"];
        $keywords = [];

        // Add camera brand as keyword
        if (isset($exif["camera_make"])) {
            $make = strtolower(trim($exif["camera_make"]));
            if (
                in_array($make, [
                    "canon",
                    "nikon",
                    "sony",
                    "olympus",
                    "fujifilm",
                    "pentax",
                    "panasonic",
                    "leica",
                ])
            ) {
                $keywords[] = ucfirst($make) . " Photography";
            } elseif (
                in_array($make, [
                    "huawei",
                    "samsung",
                    "apple",
                    "google",
                    "xiaomi",
                    "oneplus",
                ])
            ) {
                $keywords[] = "Mobile Photography";
                $keywords[] = ucfirst($make) . " Device";
            }
        }

        // Add photography type based on focal length
        if (isset($exif["focal_length"])) {
            $focalLength = (float)$exif["focal_length"];
            if ($focalLength <= 35) {
                $keywords[] = "Wide Angle Photography";
            } elseif ($focalLength >= 85) {
                $keywords[] = "Telephoto Photography";
            } elseif ($focalLength >= 200) {
                $keywords[] = "Long Telephoto Photography";
            }
        }

        // Add macro photography if close focus detected
        if (isset($exif["focal_length"]) && isset($exif["aperture"])) {
            $focalLength = (float)$exif["focal_length"];
            $aperture = (float)$exif["aperture"];

            // Macro indicators: high magnification settings
            if ($focalLength > 50 && $aperture >= 5.6) {
                $keywords[] = "Macro Photography";
            }
        }

        // Add technical photography terms
        if (isset($exif["iso"])) {
            $iso = intval($exif["iso"]);
            if ($iso >= 1600) {
                $keywords[] = "High ISO Photography";
            } elseif ($iso <= 200) {
                $keywords[] = "Low ISO Photography";
            }
        }

        // Add time-based keywords
        if (isset($exif["date_taken"])) {
            try {
                $date = DateTime::createFromFormat(
                    "Y:m:d H:i:s",
                    $exif["date_taken"]
                );
                if ($date) {
                    $hour = intval($date->format("H"));
                    if ($hour >= 5 && $hour < 12) {
                        $keywords[] = "Morning Photography";
                    } elseif ($hour >= 12 && $hour < 17) {
                        $keywords[] = "Afternoon Photography";
                    } elseif ($hour >= 17 && $hour < 20) {
                        $keywords[] = "Evening Photography";
                    } else {
                        $keywords[] = "Night Photography";
                    }

                    // Add year
                    $keywords[] = $date->format("Y") . " Photography";
                }
            } catch (Exception $e) {
                // Continue without date-based keywords
            }
        }

        // Add location-based keywords if GPS available
        if (isset($exif["gps_latitude"]) && isset($exif["gps_longitude"])) {
            $keywords[] = "Geotagged Photography";
            $keywords[] = "Location Photography";
        }

        // Add digital photography
        $keywords[] = "Digital Photography";

        return array_unique($keywords);
    }

    /**
     * Add comprehensive metadata to physical characteristics
     */
    private function addComprehensiveMetadata($metadataCollection)
    {
        try {
            $currentPhysical = $this->resource->getPhysicalCharacteristics();

            // Remove any existing metadata sections first
            if (
                $currentPhysical &&
                strpos($currentPhysical, "Technical Metadata:") !== false
            ) {
                $currentPhysical = preg_replace(
                    '/\n\nTechnical Metadata:.*$/s',
                    "",
                    $currentPhysical
                );
                error_log(
                    "METADATA: Removed existing metadata from master upload"
                );
            }

            $metadataSections = [];

            // Add EXIF data
            if (isset($metadataCollection["exif"]["all_exif"])) {
                $metadataSections[] =
                    "EXIF Data:\n" . $metadataCollection["exif"]["all_exif"];
            }

            // Add IPTC data
            if (isset($metadataCollection["iptc"]["all_iptc"])) {
                $metadataSections[] =
                    "IPTC Data:\n" . $metadataCollection["iptc"]["all_iptc"];
            }

            // Add XMP data
            if (isset($metadataCollection["xmp"]["all_xmp"])) {
                $metadataSections[] =
                    "XMP Data:\n" . $metadataCollection["xmp"]["all_xmp"];
            }

            if (!empty($metadataSections)) {
                $newMetadata =
                    "\n\nTechnical Metadata:\n\n" .
                    implode("\n\n", $metadataSections);

                // Fix encoding issues with special characters
                $cleanedMetadata = mb_convert_encoding(
                    $newMetadata,
                    "UTF-8",
                    "UTF-8"
                );
                $cleanedMetadata = preg_replace(
                    '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/',
                    "",
                    $cleanedMetadata
                );
                $this->resource->setPhysicalCharacteristics(
                    ($currentPhysical ?: "") . $cleanedMetadata
                );

                error_log(
                    "METADATA: Successfully added comprehensive metadata from master upload"
                );
            }
        } catch (Exception $e) {
            error_log(
                "METADATA: Error adding comprehensive metadata from master upload: " .
                    $e->getMessage()
            );
        }
    }

    /**
     * Set GPS coordinates on digital object after it's created
     */
    private function setGpsCoordinatesOnDigitalObject($digitalObject)
    {
        try {
            error_log("GPS DEBUG: setGpsCoordinatesOnDigitalObject called");
            error_log(
                "GPS DEBUG: Digital object ID: " .
                    ($digitalObject ? $digitalObject->id : "NULL")
            );
            error_log(
                "GPS DEBUG: Has gpsLatitude: " .
                    (isset($this->gpsLatitude)
                        ? "YES (" . $this->gpsLatitude . ")"
                        : "NO")
            );
            error_log(
                "GPS DEBUG: Has gpsLongitude: " .
                    (isset($this->gpsLongitude)
                        ? "YES (" . $this->gpsLongitude . ")"
                        : "NO")
            );

            if (!isset($this->gpsLatitude) || !isset($this->gpsLongitude)) {
                error_log("GPS DEBUG: Missing GPS coordinates, exiting");
                return;
            }

            if (!$digitalObject || !$digitalObject->id) {
                error_log(
                    "GPS DEBUG: Digital object is null or has no ID, exiting"
                );
                return;
            }

            error_log("GPS DEBUG: Attempting to set latitude property");
            // Set latitude property
            $latProperty = $digitalObject->getPropertyByName("latitude");
            error_log(
                "GPS DEBUG: Existing latitude property: " .
                    ($latProperty && $latProperty->objectId ? "EXISTS" : "NEW")
            );

            if (empty($latProperty->objectId)) {
                $latProperty = new QubitProperty();
                $latProperty->objectId = $digitalObject->id;
                $latProperty->editable = true;
                $latProperty->name = "latitude";
                error_log("GPS DEBUG: Created new latitude property");
            }
            $latProperty->value = $this->gpsLatitude;
            $latProperty->save();
            error_log(
                "GPS DEBUG: Saved latitude property: " . $this->gpsLatitude
            );

            error_log("GPS DEBUG: Attempting to set longitude property");
            // Set longitude property
            $lonProperty = $digitalObject->getPropertyByName("longitude");
            error_log(
                "GPS DEBUG: Existing longitude property: " .
                    ($lonProperty && $lonProperty->objectId ? "EXISTS" : "NEW")
            );

            if (empty($lonProperty->objectId)) {
                $lonProperty = new QubitProperty();
                $lonProperty->objectId = $digitalObject->id;
                $lonProperty->editable = true;
                $lonProperty->name = "longitude";
                error_log("GPS DEBUG: Created new longitude property");
            }
            $lonProperty->value = $this->gpsLongitude;
            $lonProperty->save();
            error_log(
                "GPS DEBUG: Saved longitude property: " . $this->gpsLongitude
            );

            error_log(
                "GPS DEBUG: Successfully set GPS coordinates on digital object - Lat: " .
                    $this->gpsLatitude .
                    ", Lon: " .
                    $this->gpsLongitude
            );
        } catch (Exception $e) {
            error_log(
                "GPS DEBUG: Exception in setGpsCoordinatesOnDigitalObject: " .
                    $e->getMessage()
            );
            error_log("GPS DEBUG: Stack trace: " . $e->getTraceAsString());
        }
    }
}
