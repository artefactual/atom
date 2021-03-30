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
 *
 * @see arInformationObjectExportJob
 */
class arInformationObjectCsvExportJob extends arInformationObjectExportJob
{
    protected $csvWriter;

    /**
     * Export clipboard item metadata to a CSV file, and related digital objects.
     *
     * @see arInformationObjectExportJob::doExport()
     *
     * @param string $path Path of file to write CSV data to
     */
    protected function doExport($path)
    {
        $this->csvWriter = $this->getCsvWriter($path);

        parent::doExport($path);
    }

    /**
     * Export resource metadata and digital object (if requested).
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

        $this->exportDataAndDigitalObject($resource, $path);

        // Export descendants if option was selected
        if (!$this->params['current-level-only']) {
            foreach ($resource->getDescendantsForExport($options) as $item) {
                $this->exportDataAndDigitalObject($item, $path);
            }
        }
    }

    /**
     * Export resource metadata and associated digital object.
     *
     * @param QubitInformationObject $resource object to export
     * @param string                 $path     temporary export job working directory
     */
    protected function exportDataAndDigitalObject($resource, $path)
    {
        // Append resource metadata to CSV file
        $this->csvWriter->exportResource($resource);

        $this->addDigitalObject($resource, $path);

        ++$this->itemsExported;
        $this->logExportProgress();
    }

    protected function getCsvWriter($path)
    {
        // Exporter will create a new file each 10,000 rows
        $writer = new csvInformationObjectExport(
            $path,
            self::getCurrentArchivalStandard(),
            10000
        );

        $writer->user = $this->user;

        // Store export options for use in csvInformationObjectExport
        $writer->setOptions($this->params);

        // Force loading of information object configuration, then modify writer
        // configuration
        $writer->loadResourceSpecificConfiguration('QubitInformationObject');
        array_unshift($writer->columnNames, 'referenceCode');
        array_unshift($writer->standardColumns, 'referenceCode');

        return $writer;
    }
}
