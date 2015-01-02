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
 * Bulk export data to XML
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <mikeg@artefactual.com>
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class eacExportTask extends exportBulkBaseTask
{
  protected $namespace        = 'export';
  protected $name             = 'auth-recs';
  protected $briefDescription = 'Bulk export multiple EAC XML files at once for authority records.';

  /**
   * @see exportBulkBaseTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->checkPathIsWritable($arguments['path']);

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $sf_context = sfContext::createInstance($configuration);

    $itemsExported = 0;

    $conn = $this->getDatabaseConnection();

    // Get descriptions matching optional specification
    $rows = $conn->query($this->informationObjectQuerySql($options), PDO::FETCH_ASSOC);

    foreach ($rows as $row)
    {
      // Fetch description then assocated actors
      $informationObject = QubitInformationObject::getById($row['id']);

      foreach($informationObject->getActors() as $resource)
      {
        $filename = $this->generateSortableFilename($resource->id, 'eac');
        $filePath = sprintf('%s/%s', $arguments['path'], $filename);

        // Only export actor the first time it's encountered in a description
        if (!file_exists($filePath))
        {
          $rawXml = $this->captureResourceExportTemplateOutput($resource, 'eac');

          try
          {
            $xml = $this->tidyXml($rawXml);
          }
          catch (Exception $e)
          {
            $badXmlFilePath = sys_get_temp_dir() .'/'. $filename;
            file_put_contents($badXmlFilePath, $rawXml);

            throw new sfException('Saved invalid generated XML to '. $badXmlFilePath);
          }

          file_put_contents($filePath, $xml);

          $this->indicateProgress($options['items-until-update']);

          $itemsExported++;
        }
      }
    }

    print "\nExport complete (". $itemsExported ." actors exported).\n";
  }
}
