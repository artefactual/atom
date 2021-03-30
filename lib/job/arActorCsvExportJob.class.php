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
 * Asynchronous job to export clipboard actor data to a CSV document, plus
 * related digital objects when requested.
 */
class arActorCsvExportJob extends arActorExportJob
{
    /**
     * Export search results as CSV, and include related digital objects when
     * requested.
     *
     * @see arActorExportJob::doExport()
     *
     * @param string $path of temporary job directory for export files
     */
    protected function doExport($path)
    {
        $this->csvWriter = $this->getCsvWriter($path);

        parent::doExport($path);
    }

    /**
     * Export actor metadata to an XML file, and export related digital object
     * when requested.
     *
     * @param QubitActor $resource actor to export
     * @param string     $path     of temporary job directory for export
     * @param array      $options  optional parameters
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
     * @return csvActorExport writer object
     */
    protected function getCsvWriter($path)
    {
        $writer = new csvActorExport($path, null, 10000);
        $writer->user = $this->user;
        $writer->setOptions($this->params);
        $writer->loadResourceSpecificConfiguration('QubitActor');

        return $writer;
    }
}
