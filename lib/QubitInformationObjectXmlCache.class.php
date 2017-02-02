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
 * @package    AccesstoMemory
 * @subpackage library
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitInformationObjectXmlCache
{
  protected $logger;

  public function __construct($options = array())
  {
    $this->logger = isset($options['logger']) ? $options['logger'] : new sfNoLogger(new sfEventDispatcher);
    $this->i18n = sfContext::getInstance()->i18n;
    $this->createExportDestinationDirs();
  }

  /**
   * Check if downloads/exports, and format-specific subdirectories, have been created and, if not,
   * create them.
   *
   * @return null
   */
  protected function createExportDestinationDirs()
  {
    $exportsPath = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'downloads' . DIRECTORY_SEPARATOR .'exports';

    $eadExportPath = $exportsPath . DIRECTORY_SEPARATOR .'ead';
    if (!is_dir($eadExportPath))
    {
      mkdir($eadExportPath, 0755, true);
    }

    $dcExportPath = $exportsPath . DIRECTORY_SEPARATOR .'dc';
    if (!is_dir($dcExportPath))
    {
      mkdir($dcExportPath, 0755, true);
    }
  }

  /**
   * Export information object to EAD (if top-level) and DC.
   *
   * @param object  information object to be export
   *
   * @return null
   */
  public function export($resource)
  {
    // Only cache top-level information object's EAD XML
    if ($resource->parentId == QubitInformationObject::ROOT_ID)
    {
      $this->cacheXmlRepresentation($resource, 'ead');
    }

    $this->cacheXmlRepresentation($resource, 'dc');
  }

  /**
   * Export all information objects to EAD (if top-level) and DC.
   *
   * @return null
   */
  public function exportAll()
  {
    foreach (QubitInformationObject::getAll() as $io)
    {
      $published = $io->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID;

      // Only cache if not root and published
      if ($io->id != QubitInformationObject::ROOT_ID && $published)
      {
        $this->logger->info($this->i18n->__('Exporting information object ID %1%', array('%1%' => $io->id)));
        $this->export($io);
      }
    }
  }

  /**
   * Export information object to EAD (if top-level) and DC.
   *
   * @param object  information object to cache
   * @param string  format of XML ("dc" or "ead")
   *
   * @return null
   */
  protected function cacheXmlRepresentation($resource, $format)
  {
    $tempFile = tmpfile();
    fwrite($tempFile, $this->generateXmlRepresentation($resource, $format));
    $metadata = stream_get_meta_data($tempFile);
    $this->storeXmlExport($metadata['uri'], $resource->id, $format);
    fclose($tempFile);
  }

  /**
   * Generate XML representation of information object.
   *
   * @param object  information object to cache
   * @param string  format of XML ("dc" or "ead")
   *
   * @return string  XML representation
   */
  protected function generateXmlRepresentation($resource, $format)
  {
    exportBulkBaseTask::includeXmlExportClassesAndHelpers();
    $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput($resource, $format);
    return Qubit::tidyXml($rawXml);
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
   * @param integer  ID of information object to cache
   * @param string  format of XML ("dc" or "ead")
   *
   * @return null
   */
  protected function storeXmlExport($filePath, $objectId, $format)
  {
    // Copy unmodified XML to downloads subdirectory
    copy($filePath, self::getFilePath($objectId, $format));

    // Copy XML with declaration/doctype removed to downloads subdirectory
    $skipLines = ($format == 'ead') ? 2 : 1; // For EAD doctype line stripped in addition to XML declaration
    $this->rewriteFileSkippingLines($filePath, self::getFilePath($objectId, $format, true), $skipLines);
  }

  /**
   * Get file path of an information object's XML representation.
   *
   * @param integer  ID of information object to cache
   * @param string  format of XML ("dc" or "ead")
   * @param boolean  where or not to store just the contents (no XML header lines)
   *
   * @return string  path to XML representation
   */
  public static function getFilePath($objectId, $format, $contentsOnly = false)
  {
    $filename = md5($objectId);
    if ($contentsOnly)
    {
      $filename .= '_contents';
    }
    $filename .= '.'. strtolower($format) .'.xml';

    $exportsPath = 'downloads' . DIRECTORY_SEPARATOR . 'exports';
    return $exportsPath . DIRECTORY_SEPARATOR . strtolower($format) . DIRECTORY_SEPARATOR . $filename;
  }

  /**
   * Get URL of an information object's XML representation.
   *
   * @param integer  ID of information object to cache
   * @param string  format of XML ("dc" or "ead")
   *
   * @return string  URL of XML representation
   */
  public static function getPathForDownload($objectId, $format)
  {
    $path = self::getFilePath($objectId, $format);

    if (file_exists(sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $path))
    {
      return $path;
    }

    return null;
  }

  /**
   * Rewrite file skipping lines.
   *
   * This is used to rewrite XML files, removing the XML declaration and doctype.
   *
   * @param string  source file
   * @param string  destination file
   * @param integer  number of lines to skip
   *
   * @return string  URL of XML representation
   */
  protected function rewriteFileSkippingLines($source, $destination, $skipLines = 0)
  {
    $sfp = fopen($source, 'r');
    $dfp = fopen($destination, 'w');

    // Skip lines
    for ($i = 1; $i <= $skipLines; $i++)
    {
      fgets($sfp);
    }

    $next = fgets($sfp);

    while ($next !== false)
    {
      $line = $next;
      fwrite($dfp, $line);

      $next = fgets($sfp);
    }

    fclose($sfp);
    fclose($dfp);
  }
}
