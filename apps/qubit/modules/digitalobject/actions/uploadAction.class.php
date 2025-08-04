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

class DigitalObjectUploadAction extends sfAction
{
    public function execute($request)
    {
        ProjectConfiguration::getActive()->loadHelpers('Qubit');

        $uploadLimt = -1;
        $diskUsage = 0;
        $uploadFiles = [];
        $warning = null;

        $this->object = QubitObject::getBySlug($request->parentSlug);

        if (!isset($this->object)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->object, 'update')) {
            throw new sfException();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        $repo = $this->object->getRepository(['inherit' => true]);

        if (isset($repo)) {
            $uploadLimit = $repo->uploadLimit;
            if (0 < $uploadLimit) {
                $uploadLimit *= pow(10, 9); // Convert to bytes
            }

            $diskUsage = $repo->getDiskUsage();
        }

        foreach ($_FILES as $file) {
            if (null != $repo && 0 <= $uploadLimit && $uploadLimit < $diskUsage + $file['size']) {
                $uploadFiles = ['error' => $this->context->i18n->__(
                    '%1% upload limit of %2% GB exceeded for %3%',
                    [
                        '%1%' => sfConfig::get('app_ui_label_digitalobject'),
                        '%2%' => $repo->uploadLimit,
                        '%4%' => $this->context->routing->generate(null, [$repo, 'module' => 'repository']),
                        '%3%' => $repo->__toString(),
                    ]
                )];

                continue;
            }

            try {
                $file = Qubit::moveUploadFile($file);
            } catch (Exception $e) {
                $uploadFile = ['error' => $e->getMessage()];

                continue;
            }

            // Temp file characteristics
            $tmpFilePath = $file['tmp_name'];
            $tmpFileName = basename($tmpFilePath);
            $tmpFileMimeType = QubitDigitalObject::deriveMimeType($tmpFileName);

            // Extract EXIF metadata if available
            $exifData = $this->extractExifMetadata($tmpFilePath);

            $uploadFiles = [
                'name' => $file['name'],
                'md5sum' => md5_file($tmpFilePath),
                'size' => hr_filesize($file['size']),
                'tmpName' => $tmpFileName,
                'warning' => $warning,
                'exifData' => $exifData, // Add EXIF data to response
            ];

            // Keep running total of disk usage
            $diskUsage += $file['size'];
        }

        // Pass file data back to caller for processing on form submit
        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode($uploadFiles));
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

        // Check if file exists and is an image
        if (!file_exists($filePath) || !$this->isImageFile($filePath)) {
            return null;
        }

        try {
            $exif = exif_read_data($filePath, 'ANY_TAG', true);
            
            if (!$exif) {
                return null;
            }

            // Extract relevant EXIF data
            $extractedData = [];

            // Camera information
            if (isset($exif['IFD0']['Make'])) {
                $extractedData['camera_make'] = $exif['IFD0']['Make'];
            }
            if (isset($exif['IFD0']['Model'])) {
                $extractedData['camera_model'] = $exif['IFD0']['Model'];
            }

            // Date information
            if (isset($exif['EXIF']['DateTimeOriginal'])) {
                $extractedData['date_taken'] = $exif['EXIF']['DateTimeOriginal'];
            } elseif (isset($exif['EXIF']['DateTime'])) {
                $extractedData['date_taken'] = $exif['EXIF']['DateTime'];
            }

            // Creator/Artist
            if (isset($exif['IFD0']['Artist'])) {
                $extractedData['artist'] = $exif['IFD0']['Artist'];
            }

            // Description
            if (isset($exif['IFD0']['ImageDescription'])) {
                $extractedData['description'] = $exif['IFD0']['ImageDescription'];
            }

            // Copyright
            if (isset($exif['IFD0']['Copyright'])) {
                $extractedData['copyright'] = $exif['IFD0']['Copyright'];
            }

            // Camera settings
            if (isset($exif['EXIF']['FocalLength'])) {
                $extractedData['focal_length'] = $exif['EXIF']['FocalLength'];
            }
            if (isset($exif['EXIF']['FNumber'])) {
                $extractedData['aperture'] = $exif['EXIF']['FNumber'];
            }
            if (isset($exif['EXIF']['ExposureTime'])) {
                $extractedData['shutter_speed'] = $exif['EXIF']['ExposureTime'];
            }
            if (isset($exif['EXIF']['ISOSpeedRatings'])) {
                $extractedData['iso'] = $exif['EXIF']['ISOSpeedRatings'];
            }

            // GPS coordinates
            if (isset($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLongitude'])) {
                $lat = $this->convertGpsCoordinate($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
                $lon = $this->convertGpsCoordinate($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
                $extractedData['gps_latitude'] = $lat;
                $extractedData['gps_longitude'] = $lon;
            }

            // Image dimensions
            if (isset($exif['COMPUTED']['Width'], $exif['COMPUTED']['Height'])) {
                $extractedData['width'] = $exif['COMPUTED']['Width'];
                $extractedData['height'] = $exif['COMPUTED']['Height'];
            }

            return $extractedData;

        } catch (Exception $e) {
            // Log error but don't break the upload process
            error_log("EXIF extraction failed for {$filePath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if file is a supported image type for EXIF
     */
    private function isImageFile($filePath)
    {
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