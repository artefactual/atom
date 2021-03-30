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
 * A worker to, given the HTTP GET parameters sent to advanced search,
 * replicate the search and export the resulting descriptions to CSV.
 */
class arInformationObjectXmlExportJob extends arInformationObjectExportJob
{
    public const XML_STANDARD = 'ead';

    protected $xmlCachingEnabled = false;

    /**
     * Export clipboard item metadata and (optionally) digital objects to $path.
     *
     * @see arInformationObjectExportJob::doExport()
     *
     * @param string $path temporary export job working directory
     */
    protected function doExport($path)
    {
        exportBulkBaseTask::includeXmlExportClassesAndHelpers();

        $this->xmlCachingEnabled = sfConfig::get('app_cache_xml_on_save', false);

        parent::doExport($path);
    }

    /**
     * Export resource metadata and (optionally) digital object.
     *
     * @param QubitInformationObject $resource object to export
     * @param string                 $path     temporary export job working directory
     */
    protected function exportResource($resource, $path)
    {
        // Don't export resource if this level of description is not allowed
        if (!$this->isAllowedLevelId($resource->levelOfDescriptionId)) {
            return;
        }

        // If XML caching is enabled then check for a cached XML document
        if ($this->xmlCachingEnabled) {
            $cachedXmlPath = QubitInformationObjectXmlCache::resourceExportFilePath(
                $resource,
                self::XML_STANDARD
            );

            if (file_exists($cachedXmlPath)) {
                $xml = file_get_contents($cachedXmlPath);
            }
        }

        // If no cached XML has been fetched then generate XML on the fly
        if (empty($xml)) {
            try {
                // Print warnings/notices here too, as they are often important.
                $errLevel = error_reporting(E_ALL);

                $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput(
                    $resource,
                    self::XML_STANDARD,
                    $this->params
                );
                $xml = Qubit::tidyXml($rawXml);

                error_reporting($errLevel);
            } catch (Exception $e) {
                throw new sfException($this->i18n->__(
                    'Invalid XML generated for object %1%.',
                    ['%1%' => $row['id']]
                ));
            }
        }

        $filename = exportBulkBaseTask::generateSortableFilename(
            $resource,
            'xml',
            self::XML_STANDARD
        );
        $filePath = sprintf('%s/%s', $path, $filename);

        if (false === file_put_contents($filePath, $xml)) {
            throw new sfException($this->i18n->__(
                'Cannot write to path: %1%',
                ['%1%' => $filePath]
            ));
        }

        $this->addDigitalObject($resource, $path, $errors);

        ++$this->itemsExported;
        $this->logExportProgress();
    }
}
