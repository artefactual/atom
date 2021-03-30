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
 * Export information object(s) as EAD and/or DC XML.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitInformationObjectXmlCache
{
    protected $logger;

    public function __construct($options = [])
    {
        $this->logger = isset($options['logger']) ? $options['logger'] : new sfNoLogger(new sfEventDispatcher());
        $this->i18n = sfContext::getInstance()->i18n;
        $this->createExportDestinationDirs();
    }

    /**
     * Export information object to EAD (if top-level) and/or DC.
     *
     * @param object  information object instance to export
     * @param string  format of XML ('dc' or 'ead')
     * @param mixed      $resource
     * @param null|mixed $format
     */
    public function export($resource, $format = null)
    {
        // Only cache top-level information object's EAD XML
        if (QubitInformationObject::ROOT_ID == $resource->parentId && 'dc' !== $format) {
            $this->cacheXmlFormat($resource, 'ead');
        }

        if ('ead' !== $format) {
            $this->cacheXmlFormat($resource, 'dc');
        }
    }

    /**
     * Export all information objects to EAD (if top-level) and/or DC.
     *
     * @param mixed $options
     */
    public function exportAll($options = [])
    {
        $skip = isset($options['skip']) ? $options['skip'] : 0;
        $limit = isset($options['limit']) ? $options['limit'] : 0;

        // Use format if specified and valid; otherwise, default to null
        $valid_formats = ['dc', 'ead'];
        $format = isset($options['format']) && in_array($options['format'], $valid_formats) ? $options['format'] : null;

        // Get not-root and published information objects (sorted by ID so optional
        // skip/limit will be consistent)
        $sql = 'SELECT i.id FROM '.QubitInformationObject::TABLE_NAME." i \r
            INNER JOIN status s ON i.id=s.object_id \r
            WHERE i.id != :root_id \r
            AND s.status_id=:status_id AND s.type_id=:type_id \r
            ORDER BY i.id";

        $params = [
            ':status_id' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID,
            ':type_id' => QubitTerm::STATUS_TYPE_PUBLICATION_ID,
            ':root_id' => QubitInformationObject::ROOT_ID,
        ];

        $exporting = 0;

        // Apply skip/limit and fetch results
        Propel::getDB()->applyLimit($sql, $skip, $limit);
        $results = QubitPdo::fetchAll($sql, $params);

        if (count($results)) {
            $this->logger->info($this->i18n->__('%1% published information objects found.', ['%1%' => count($results)]));

            // Export each information object
            foreach ($results as $row) {
                $io = QubitInformationObject::getById($row->id);

                ++$exporting;
                $this->logger->info($this->i18n->__('Exporting information object ID %1% (%2% of %3%)', ['%1%' => $io->id, '%2%' => $exporting, '%3%' => count($results)]));
                $this->export($io, $format);

                // Keep caches clear to prevent memory use from ballooning
                Qubit::clearClassCaches();
            }
        }
    }

    /**
     * Get a resource's file path to an XML export of a given format.
     *
     * @param object  information object resource to get file path for
     * @param string  XML format
     * @param bool  whether or not to get file path for just the XML's contents (no XML header lines)
     * @param mixed $resource
     * @param mixed $format
     * @param mixed $contentsOnly
     *
     * @return string file path of EAD XML
     */
    public static function resourceExportFilePath($resource, $format, $contentsOnly = false)
    {
        $cacheResource = new QubitInformationObjectXmlCacheResource($resource);

        return $cacheResource->getFilePath($format, $contentsOnly);
    }

    /**
     * Check if downloads/exports, and format-specific subdirectories, have been created and, if not,
     * create them.
     */
    protected function createExportDestinationDirs()
    {
        $exportsPath = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'downloads'.DIRECTORY_SEPARATOR.'exports';

        $eadExportPath = $exportsPath.DIRECTORY_SEPARATOR.'ead';
        if (!is_dir($eadExportPath)) {
            mkdir($eadExportPath, 0755, true);
        }

        $dcExportPath = $exportsPath.DIRECTORY_SEPARATOR.'dc';
        if (!is_dir($dcExportPath)) {
            mkdir($dcExportPath, 0755, true);
        }
    }

    /**
     * Export information object to EAD (if top-level) and DC.
     *
     * Generate a format of XML for a resource, first storing it as a temp file
     * then storing it in a location accessible to web users.
     *
     * @param object  information object to cache
     * @param string  format of XML ("dc" or "ead")
     * @param mixed $resource
     * @param mixed $format
     */
    protected function cacheXmlFormat($resource, $format)
    {
        $tempFile = tmpfile();
        $cacheResource = new QubitInformationObjectXmlCacheResource($resource);
        fwrite($tempFile, $cacheResource->generateXmlRepresentation($format));
        $metadata = stream_get_meta_data($tempFile);
        $this->storeXmlExport($metadata['uri'], $resource, $format);
        fclose($tempFile);
    }

    /**
     * Store XML representations of information object.
     *
     * Two XML files are created, copied from a source file to files within the
     * downloads directory. One copy contains the complete XML while the other
     * removes the XML declaration line (and, for EAD, the doctype line).
     *
     * The copy with the line(s) removed is created so it can be included within
     * OAI-PMH results.
     *
     * @param string  path to temporary file containing XML
     * @param object  information object instance to cache
     * @param string  format of XML ("dc" or "ead")
     * @param mixed $filePath
     * @param mixed $resource
     * @param mixed $format
     */
    protected function storeXmlExport($filePath, $resource, $format)
    {
        $cacheResource = new QubitInformationObjectXmlCacheResource($resource);

        // Copy unmodified XML to downloads subdirectory
        copy($filePath, $cacheResource->getFilePath($format));

        // Copy XML with declaration/doctype removed to downloads subdirectory
        $skipLines = ('ead' == $format) ? 2 : 1; // For EAD doctype line stripped in addition to XML declaration
        $this->rewriteFileSkippingLines($filePath, $cacheResource->getFilePath($format, true), $skipLines);
    }

    /**
     * Rewrite file skipping lines.
     *
     * This is used to rewrite XML files, removing the XML declaration and doctype.
     *
     * @param string  source file
     * @param string  destination file
     * @param int  number of lines to skip
     * @param mixed $source
     * @param mixed $destination
     * @param mixed $skipLines
     */
    protected function rewriteFileSkippingLines($source, $destination, $skipLines = 0)
    {
        $sfp = fopen($source, 'r');
        $dfp = fopen($destination, 'w');

        // Skip lines
        for ($i = 1; $i <= $skipLines; ++$i) {
            fgets($sfp);
        }

        $next = fgets($sfp);

        while (false !== $next) {
            $line = $next;
            fwrite($dfp, $line);

            $next = fgets($sfp);
        }

        fclose($sfp);
        fclose($dfp);
    }
}
