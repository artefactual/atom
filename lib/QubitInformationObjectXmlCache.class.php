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
   * @param object  information object instance to export
   *
   * @return null
   */
  public function export($resource)
  {
    // Only cache top-level information object's EAD XML
    if ($resource->parentId == QubitInformationObject::ROOT_ID)
    {
      $this->cacheXmlFormat($resource, 'ead');
    }

    $this->cacheXmlFormat($resource, 'dc');
  }

  /**
   * Export all information objects to EAD (if top-level) and DC.
   *
   * @return null
   */
  public function exportAll($options = array())
  {
    $skip = isset($options['skip']) ? $options['skip'] : 0;

    // Get not-root and published information objects
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::ID, QubitInformationObject::ROOT_ID, Criteria::NOT_EQUAL);
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
    $criteria->addAscendingOrderByColumn(QubitInformationObject::ID); // sort so optional skip will be consistent
    $criteria->setOffset($skip);

    $results = QubitInformationObject::get($criteria);

    $exporting = 0;

    if (count($results))
    {
      $this->logger->info($this->i18n->__('%1% published information objects found.', array('%1%' => count($results))));

      foreach ($results as $io)
      {
        $exporting++;
        $this->logger->info($this->i18n->__('Exporting information object ID %1% (%2% of %3%)', array('%1%' => $io->id, '%2%' => $exporting, '%3%' => count($results))));
        $this->export($io);
      }
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
   *
   * @return null
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
   *
   * @return null
   */
  protected function storeXmlExport($filePath, $resource, $format)
  {
    $cacheResource = new QubitInformationObjectXmlCacheResource($resource);

    // Copy unmodified XML to downloads subdirectory
    copy($filePath, $cacheResource->getFilePath($format));

    // Copy XML with declaration/doctype removed to downloads subdirectory
    $skipLines = ($format == 'ead') ? 2 : 1; // For EAD doctype line stripped in addition to XML declaration
    $this->rewriteFileSkippingLines($filePath, $cacheResource->getFilePath($format, true), $skipLines);
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
   * @return null
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

  /**
   * Get a resource's file path to an XML export of a given format.
   *
   * @param object  information object resource to get file path for
   * @param string  XML format
   * @param boolean  whether or not to get file path for just the XML's contents (no XML header lines)
   *
   * @return string  file path of EAD XML
   */
  public static function resourceExportFilePath($resource, $format, $contentsOnly = false)
  {
    $cacheResource = new QubitInformationObjectXmlCacheResource($resource);
    return $cacheResource->getFilePath($format, $contentsOnly);
  }
}
