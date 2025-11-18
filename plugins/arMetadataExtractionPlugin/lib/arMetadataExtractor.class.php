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
 * Metadata extraction service for digital objects
 *
 * @package    arMetadataExtractionPlugin
 * @subpackage lib
 * @author     Johan Pieterse The Archive and Heritage Group <johan@theahg.co.za>
 */

class arMetadataExtractor
{
    protected $settings = [];
    protected $logger;

    public function __construct(?string $techMetadataTargetField = null)
    {
        $this->loadSettings();

        // Allow override via constructor param
        if (null !== $techMetadataTargetField) {
            $this->settings['technical_metadata_target_field'] = $techMetadataTargetField;
        }

        try {
            if (sfContext::hasInstance()) {
                $this->logger = sfContext::getInstance()->getLogger();
            }
        } catch (Exception $e) {
            $this->logger = null;
        }
    }

    /**
     * Safe logging method that handles null logger.
     */
    protected function log($message, $level = 'info')
    {
        if (!$this->logger) {
            return;
        }

        switch ($level) {
            case 'error':
            case 'err':
                $this->logger->err($message);
                break;
            case 'warning':
            case 'warn':
                $this->logger->warning($message);
                break;
            case 'debug':
                $this->logger->debug($message);
                break;
            default:
                $this->logger->info($message);
        }
    }

    protected function loadSettings()
    {
        $defaults = [
            'metadata_extraction_enabled'       => true,
            'extract_exif'                      => true,
            'extract_iptc'                      => true,
            'extract_xmp'                       => true,
            'overwrite_title'                   => false,
            'overwrite_description'             => false,
            'auto_generate_keywords'            => true,
            'extract_gps_coordinates'           => true,
            'add_technical_metadata'            => true,
            // NEW default for target field
            'technical_metadata_target_field'   => 'physicalCharacteristics',
        ];

        $this->settings = $defaults;

        try {
            if (class_exists('QubitSetting') && sfContext::hasInstance()) {
                foreach ($defaults as $name => $defaultValue) {
                    try {
                        $setting = QubitSetting::getByName($name);
                        if ($setting) {
                            $this->settings[$name] = $setting->getValue([
                                'sourceCulture' => true,
                            ]);
                        }
                    } catch (Exception $e) {
                        $this->settings[$name] = $defaultValue;
                    }
                }
            }
        } catch (Exception $e) {
            // use defaults
        }
    }

    public function processDigitalObject(QubitDigitalObject $digitalObject)
    {
        if (empty($this->settings['metadata_extraction_enabled'])) {
            return false;
        }

        $filePath = $digitalObject->getAbsolutePath();

        if (!$filePath || !file_exists($filePath)) {
            $this->log('Metadata extraction: File not found at '.$filePath, 'warning');
            return false;
        }

        $metadata = $this->extractMetadata($filePath);

        if (!$metadata) {
            return false;
        }

        $informationObject = $digitalObject->getInformationObject();

        if (!$informationObject) {
            $this->log('Metadata extraction: No information object linked to digital object', 'warning');
            return false;
        }

        $this->applyMetadata($informationObject, $digitalObject, $metadata);

        return true;
    }

    protected function extractMetadata($filePath)
    {
        if (!class_exists('arEmbeddedMetadataParser')) {
            $this->log('arEmbeddedMetadataParser class not found', 'error');
            return null;
        }

        try {
            $rawMetadata = arEmbeddedMetadataParser::extract($filePath);

            if (!$rawMetadata) {
                return null;
            }

            return $this->normalizeMetadata($rawMetadata);
        } catch (Exception $e) {
            $this->log('Metadata extraction failed: '.$e->getMessage(), 'error');
            return null;
        }
    }

    protected function normalizeMetadata($rawMetadata)
    {
        $metadata = [
            'title'       => null,
            'description' => null,
            'creator'     => null,
            'date'        => null,
            'keywords'    => [],
            'gps'         => null,
            'technical'   => [],
            'rights'      => null,
            // we keep raw payload if needed later
            '_raw'        => $rawMetadata,
        ];

        $norm = $rawMetadata['_norm'] ?? [];

        $metadata['title'] = $norm['title']
            ?? $rawMetadata['ObjectName']
            ?? $rawMetadata['ImageDescription']
            ?? null;

        $metadata['description'] = $norm['description']
            ?? $rawMetadata['Caption-Abstract']
            ?? null;

        $metadata['creator'] = $norm['creator']
            ?? $rawMetadata['By-line']
            ?? $rawMetadata['Artist']
            ?? null;

        if (isset($norm['createDate'])) {
            $metadata['date'] = $this->parseDate($norm['createDate']);
        } elseif (isset($rawMetadata['DateTimeOriginal'])) {
            $metadata['date'] = $this->parseDate($rawMetadata['DateTimeOriginal']);
        }

        if (isset($rawMetadata['Subject'])) {
            $metadata['keywords'] = is_array($rawMetadata['Subject'])
                ? $rawMetadata['Subject']
                : array_map('trim', explode(',', $rawMetadata['Subject']));
        } elseif (isset($rawMetadata['Keywords'])) {
            $metadata['keywords'] = is_array($rawMetadata['Keywords'])
                ? $rawMetadata['Keywords']
                : array_map('trim', explode(',', $rawMetadata['Keywords']));
        }

        if (isset($rawMetadata['GPSLatitude'], $rawMetadata['GPSLongitude'])) {
            $metadata['gps'] = [
                'latitude'  => $rawMetadata['GPSLatitude'],
                'longitude' => $rawMetadata['GPSLongitude'],
            ];
        }

        $technicalFields = [
            'Make', 'Model', 'FocalLength', 'FNumber',
            'ExposureTime', 'ISO', 'ImageWidth', 'ImageHeight',
            'ColorSpace', 'WhiteBalance', 'Flash', 'LensModel',
        ];

        foreach ($technicalFields as $field) {
            if (isset($rawMetadata[$field])) {
                $metadata['technical'][$field] = $rawMetadata[$field];
            }
        }

        $metadata['rights'] = $norm['rights'] ?? $rawMetadata['Copyright'] ?? null;

        return $metadata;
    }

    // parseDate() unchanged...

    protected function applyMetadata($informationObject, $digitalObject, $metadata)
    {
        // Title
        if (
            $metadata['title']
            && $this->shouldUpdateField($informationObject->getTitle(), 'title')
        ) {
            $informationObject->setTitle($metadata['title']);
            $this->log('Updated title from metadata: '.$metadata['title']);
        }

        // Description / Scope and content
        if (
            $metadata['description']
            && $this->shouldUpdateField($informationObject->getScopeAndContent(), 'description')
        ) {
            $informationObject->setScopeAndContent($metadata['description']);
            $this->log('Updated scope and content from metadata');
        }

        // Creator
        if ($metadata['creator']) {
            $this->addCreator($informationObject, $metadata['creator']);
        }

        // Creation date
        if ($metadata['date']) {
            $this->addCreationDate($informationObject, $metadata['date']);
        }

        // Keywords
        if (!empty($metadata['keywords'])) {
            $this->addSubjectAccessPoints($informationObject, $metadata['keywords']);
        }

        // GPS
        if (
            $metadata['gps']
            && !empty($this->settings['extract_gps_coordinates'])
        ) {
            $this->setGpsCoordinates($digitalObject, $metadata['gps']);
        }

        // Technical metadata (summary)
        if (
            !empty($metadata['technical'])
            && !empty($this->settings['add_technical_metadata'])
        ) {
            $this->addTechnicalMetadata($informationObject, $metadata['technical']);
        }

        // Rights
        if ($metadata['rights']) {
            $this->addRightsStatement($informationObject, $metadata['rights']);
        }

        // Auto keywords
        if (!empty($this->settings['auto_generate_keywords'])) {
            $generatedKeywords = $this->generateKeywords($metadata['technical']);
            if (!empty($generatedKeywords)) {
                $this->addSubjectAccessPoints($informationObject, $generatedKeywords);
            }
        }

        $informationObject->save();
        $this->log('Metadata extraction completed for object ID: '.$informationObject->id);
    }

    // shouldUpdateField(), addCreator(), addCreationDate(),
    // addSubjectAccessPoints(), setGpsCoordinates(), addRightsStatement(),
    // generateKeywords() remain as you had them.

    /**
     * Add technical metadata summary to a configurable field on the IO.
     */
    protected function addTechnicalMetadata($informationObject, $technical)
    {
        $sections = [];

        if (isset($technical['Make'], $technical['Model'])) {
            $sections[] = 'Camera: '.$technical['Make'].' '.$technical['Model'];
        }

        if (isset($technical['LensModel'])) {
            $sections[] = 'Lens: '.$technical['LensModel'];
        }

        $settings = [];

        if (isset($technical['FocalLength'])) {
            $settings[] = 'Focal Length: '.$technical['FocalLength'];
        }
        if (isset($technical['FNumber'])) {
            $settings[] = 'Aperture: f/'.$technical['FNumber'];
        }
        if (isset($technical['ExposureTime'])) {
            $settings[] = 'Shutter Speed: '.$technical['ExposureTime'];
        }
        if (isset($technical['ISO'])) {
            $settings[] = 'ISO: '.$technical['ISO'];
        }

        if (!empty($settings)) {
            $sections[] = 'Settings: '.implode(', ', $settings);
        }

        if (isset($technical['ImageWidth'], $technical['ImageHeight'])) {
            $sections[] = 'Dimensions: '.$technical['ImageWidth'].' × '.$technical['ImageHeight'].' pixels';
        }

        if (empty($sections)) {
            return;
        }

        $summary = "Technical Metadata:\n".implode("\n", $sections);

        // Determine target field
        $targetField = $this->settings['technical_metadata_target_field'] ?? 'physicalCharacteristics';

        // Resolve getter/setter dynamically, with fallback
        $getter = 'get'.ucfirst($targetField);
        $setter = 'set'.ucfirst($targetField);
        if (!method_exists($informationObject, $getter) || !method_exists($informationObject, $setter)) {
            $getter = 'getPhysicalCharacteristics';
            $setter = 'setPhysicalCharacteristics';
        }

        $current = (string) $informationObject->$getter();

        if ($current) {
            // Remove existing Technical Metadata section if present
            $current = preg_replace('/\n?Technical Metadata:.*$/s', '', $current);
            $current = rtrim($current);
            $newValue = $current."\n\n".$summary;
        } else {
            $newValue = $summary;
        }

        $informationObject->$setter($newValue);

        $this->log(sprintf(
            'Added technical metadata to %s for IO ID %d',
            $targetField,
            $informationObject->id
        ));
    }
}
