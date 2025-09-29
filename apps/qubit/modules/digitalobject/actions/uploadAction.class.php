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
        ProjectConfiguration::getActive()->loadHelpers("Qubit");

        $uploadLimt = -1;
        $diskUsage = 0;
        $uploadFiles = [];
        $warning = null;

        $this->object = QubitObject::getBySlug($request->parentSlug);

        if (!isset($this->object)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->object, "update")) {
            throw new sfException();
        }

        // Check if uploads are allowed
        if (!QubitDigitalObject::isUploadAllowed()) {
            QubitAcl::forwardToSecureAction();
        }

        $repo = $this->object->getRepository(["inherit" => true]);

        if (isset($repo)) {
            $uploadLimit = $repo->uploadLimit;
            if (0 < $uploadLimit) {
                $uploadLimit *= pow(10, 9); // Convert to bytes
            }

            $diskUsage = $repo->getDiskUsage();
        }

        foreach ($_FILES as $file) {
            if (
                null != $repo &&
                0 <= $uploadLimit &&
                $uploadLimit < $diskUsage + $file["size"]
            ) {
                $uploadFiles = [
                    "error" => $this->context->i18n->__(
                        "%1% upload limit of %2% GB exceeded for %3%",
                        [
                            "%1%" => sfConfig::get(
                                "app_ui_label_digitalobject"
                            ),
                            "%2%" => $repo->uploadLimit,
                            "%4%" => $this->context->routing->generate(null, [
                                $repo,
                                "module" => "repository",
                            ]),
                            "%3%" => $repo->__toString(),
                        ]
                    ),
                ];

                continue;
            }

            try {
                $file = Qubit::moveUploadFile($file);
            } catch (Exception $e) {
                $uploadFile = ["error" => $e->getMessage()];

                continue;
            }

            // Temp file characteristics
            $tmpFilePath = $file["tmp_name"];
            $tmpFileName = basename($tmpFilePath);
            $tmpFileMimeType = QubitDigitalObject::deriveMimeType($tmpFileName);

            // Extract EXIF metadata if available
            $exifData = $this->extractExifMetadata($tmpFilePath);

            $uploadFiles = [
                "name" => $file["name"],
                "md5sum" => md5_file($tmpFilePath),
                "size" => hr_filesize($file["size"]),
                "tmpName" => $tmpFileName,
                "warning" => $warning,
                "exifData" => $exifData, // Add EXIF data to response
            ];

            // Keep running total of disk usage
            $diskUsage += $file["size"];
        }

        // Pass file data back to caller for processing on form submit
        $this->response->setHttpHeader(
            "Content-Type",
            "application/json; charset=utf-8"
        );

        return $this->renderText(json_encode($uploadFiles));
    }

    /**
     * Extract EXIF metadata from uploaded image file
     */
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

        $norm = $meta["_norm"] ?? [];
        $extractedData = [];

        // Extract relevant data for JSON response
        if (isset($norm["creator"])) {
            $extractedData["artist"] = $norm["creator"];
        }

        if (isset($norm["createDate"])) {
            $extractedData["date_taken"] = $norm["createDate"];
        }

        if (isset($norm["description"])) {
            $extractedData["description"] = $norm["description"];
        }

        if (isset($norm["rights"])) {
            $extractedData["copyright"] = $norm["rights"];
        }

        // Camera info from raw metadata
        if (isset($meta["Make"])) {
            $extractedData["camera_make"] = $meta["Make"];
        }
        if (isset($meta["Model"])) {
            $extractedData["camera_model"] = $meta["Model"];
        }

        // GPS
        if (isset($meta["GPSLatitude"])) {
            $extractedData["gps_latitude"] = $meta["GPSLatitude"];
        }
        if (isset($meta["GPSLongitude"])) {
            $extractedData["gps_longitude"] = $meta["GPSLongitude"];
        }

        // Technical details
        if (isset($meta["FocalLength"])) {
            $extractedData["focal_length"] = $meta["FocalLength"];
        }
        if (isset($meta["FNumber"])) {
            $extractedData["aperture"] = $meta["FNumber"];
        }
        if (isset($meta["ExposureTime"])) {
            $extractedData["shutter_speed"] = $meta["ExposureTime"];
        }
        if (isset($meta["ISO"])) {
            $extractedData["iso"] = $meta["ISO"];
        }

        // Dimensions
        if (isset($meta["ImageWidth"])) {
            $extractedData["width"] = $meta["ImageWidth"];
        }
        if (isset($meta["ImageHeight"])) {
            $extractedData["height"] = $meta["ImageHeight"];
        }

        return $extractedData;
    }
}
