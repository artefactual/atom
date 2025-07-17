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
 * Asynchronous job to export clipboard accession data to a CSV document.
 */
class arAccessionCsvExportJob extends arAccessionExportJob
{
    /**
     * Export search results as CSV, and include related digital objects when
     * requested.
     *
     * @see arAccessionExportJob::doExport()
     *
     * @param string $path of temporary job directory for export files
     */
    protected function doExport($path)
    {
        $this->csvWriter = $this->getCsvWriter($path);

        parent::doExport($path);
    }

    /**
     * Export accession metadata to a CSV file, and export related digital object
     * when requested.
     *
     * @param QubitAccession $resource accession to export
     * @param string         $path     of temporary job directory for export
     * @param array          $options  optional parameters
     */
    protected function exportResource($resource, $path, $options = [])
    {
        $this->csvWriter->exportResource($resource);

        $this->addDigitalObject($resource, $path);

        ++$this->itemsExported;
    }

    /**
     * Configure and return CSV writer.
     *
     * @param string $path of temporary job directory for export
     *
     * @return csvAccessionExport writer object
     */
    protected function getCsvWriter($path)
    {
        $writer = new csvAccessionExport($path, null, 10000);
        $writer->user = $this->user;
        $writer->setOptions($this->params);
        $writer->loadResourceSpecificConfiguration('QubitAccession');

        return $writer;
    }
}
