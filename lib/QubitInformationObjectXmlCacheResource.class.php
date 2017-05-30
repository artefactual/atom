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
 * Represent information object(s) as EAD and/or DC XML.
 *
 * @package    AccesstoMemory
 * @subpackage library
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitInformationObjectXmlCacheResource
{
  protected $resource;

  public function __construct($resource)
  {
    $this->resource = $resource;
  }

  /**
   * Generate XML representation of information object.
   *
   * @param string  format of XML ("dc" or "ead")
   *
   * @return string  XML representation
   */
  public function generateXmlRepresentation($format)
  {
    exportBulkBaseTask::includeXmlExportClassesAndHelpers();
    $options = ($format == 'ead') ? array('public' => true) : array();
    $rawXml = exportBulkBaseTask::captureResourceExportTemplateOutput($this->resource, $format, $options);
    return Qubit::tidyXml($rawXml);
  }

  /**
   * Get file path of an information object's XML representation.
   *
   * @param string  format of XML ("dc" or "ead")
   * @param boolean  where or not to store just the contents (no XML header lines)
   *
   * @return string  path to XML representation
   */
  public function getFilePath($format, $contentsOnly = false)
  {
    $filename = md5($this->resource->id);
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
   * @param string  format of XML ("dc" or "ead")
   *
   * @return string  URL of XML representation
   */
  public function getPathForDownload($format)
  {
    $path = self::getFilePath($this->resource->id, $format);

    if (file_exists(sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . $path))
    {
      return $path;
    }

    return null;
  }
}
