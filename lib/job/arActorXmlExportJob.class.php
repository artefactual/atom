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
 * Asynchronous job to export clipboard actor data to XML documents, plus
 * related digital objects when requested.
 */
class arActorXmlExportJob extends arActorExportJob
{
    public const XML_STANDARD = 'eac';

    /**
     * Export search results as XML.
     *
     * @param string  Path of file to write XML data to
     * @param mixed $path
     */
    protected function doExport($path)
    {
        exportBulkBaseTask::includeXmlExportClassesAndHelpers();

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
        try {
            // Print warnings/notices here too, as they are often important.
            $errLevel = error_reporting(E_ALL);

            $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput(
                $resource,
                self::XML_STANDARD
            );
            $xml = Qubit::tidyXml($rawXml);

            error_reporting($errLevel);
        } catch (Exception $e) {
            throw new sfException($this->i18n->__(
                'Invalid XML generated for object %1%.',
                ['%1%' => $row['id']]
            ));
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

        $this->addDigitalObject($resource, $path);

        ++$this->itemsExported;
    }
}
