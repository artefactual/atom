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
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class exportBulkTask extends exportBulkBaseTask
{
  protected $namespace        = 'export';
  protected $name             = 'bulk';
  protected $briefDescription = 'Bulk export multiple XML files at once';

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $options['format'] = $this->normalizeExportFormat($options['format']);

    if (!isset($options['single-id']))
    {
      $this->checkPathIsWritable($arguments['path']);
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $sf_context = sfContext::createInstance($configuration);

    // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
    sfConfig::add(QubitSetting::getSettingsArray());

    $itemsExported = 0;

    $conn = $this->getDatabaseConnection();
    $rows = $conn->query($this->informationObjectQuerySql($options), PDO::FETCH_ASSOC);

    $this->includeClassesAndHelpers();

    foreach ($rows as $row)
    {
      $resource = QubitInformationObject::getById($row['id']);

      try
      {
        $rawXml = $this->captureResourceExportTemplateOutput($resource, $options['format']);
        $xml = $this->tidyXml($rawXml);
      }
      catch (Exception $e)
      {
        throw new sfException('Invalid XML generated for object '. $row['id'] .'.');
      }

      if (isset($options['single-id']))
      {
        // If we're just exporting the one record, the given path
        // is actually the full path+filename.
        $filePath = $arguments['path'];
      }
      else
      {
        $filename = $this->generateSortableFilename($row['id'], $options['format']);
        $filePath = sprintf('%s/%s', $arguments['path'], $filename);
      }

      file_put_contents($filePath, $xml);

      $this->indicateProgress($options['items-until-update']);

      $itemsExported++;
    }

    print "\nExport complete (". $itemsExported ." descriptions exported).\n";
  }
}
