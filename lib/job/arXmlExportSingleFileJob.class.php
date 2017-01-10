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
 * A worker to export a description as XML.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arXmlExportSingleFileJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $params = array();

  public function runJob($parameters)
  {
    $this->params = $parameters;

    if (!isset($this->params['objectId']))
    {
      $this->error($this->i18n->__('No object ID provided.'));

      return false;
    }

    if (!is_numeric($this->params['objectId']))
    {
      $this->error($this->i18n->__('Object ID must be numberic.'));

      return false;
    }

    if (!isset($this->params['format']))
    {
      $this->error($this->i18n->__('No format specified.'));

      return false;
    }

    $this->info($this->i18n->__('Starting %1 export of information object %2.', array('%1' => strtoupper($this->params['format']), '%2' => $this->params['objectId'])));
    self::createExportDestinationDirs();
    $this->exportResource();

    // Mark job as complete
    $this->info($this->i18n->__('Export complete.'));
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  /**
   * Check if downloads/exports, and format-specific subdirectories, have been created and, if not,
   * create them.
   *
   * @return null
   */
  public static function createExportDestinationDirs()
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
   * Export XML representation of information object as file
   *
   * @param object  information object to be export
   *
   * @return null
   */
  protected function exportResource()
  {
    $resource = QubitInformationObject::getById($this->params['objectId']);

    if (null === $resource)
    {
      throw new sfException($this->i18n->__('Information object %1% does not eist', array('%1%' => $this->params['objectId'])));
    }

    // Initially export to a temp file
    $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_'. $this->job->id;

    try
    {
      exportBulkBaseTask::includeXmlExportClassesAndHelpers();

      // Print warnings/notices here too, as they are often important.
      $errLevel = error_reporting(E_ALL);

      $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput($resource, $this->params['format'], $this->params);
      $xml = Qubit::tidyXml($rawXml);

      error_reporting($errLevel);
    }
    catch (Exception $e)
    {
      throw new sfException($this->i18n->__('Invalid XML generated for information object %1%.', array('%1%' => $this->params['objectId'])));
    }

    if (false === file_put_contents($tempFilePath, $xml))
    {
      throw new sfException($this->i18n->__('Cannot write to path: %1%', array('%1%' => $tempFilePath)));
    }

    // Copy tempory file to destination location then remove temp file
    self::storeExport($tempFilePath, $this->params['objectId'], $this->params['format']);

    unlink($tempFilePath);
  }

  public static function storeExport($filePath, $objectId, $format)
  {
    copy($filePath, self::getPath($objectId, $format));
    $skipLines = ($format == 'ead') ? 2 : 1;
    self::rewriteFileSkippingLines($filePath, self::getPath($objectId, $format, true), $skipLines);
  }

  public static function getPath($objectId, $format, $contentsOnly = false)
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

  public static function getPathForDownload($objectId, $format)
  {
    $path = self::getPath($objectId, $format);

    if (file_exists(sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $path))
    {
      return $path;
    }

    return null;
  }

  public static function rewriteFileSkippingLines($source, $destination, $skipLines = 0)
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
