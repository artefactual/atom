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
  protected function configure()
  {
    $this->addCommonArgumentsAndOptions();
    $this->addOptions(array(
      new sfCommandOption('format', null, sfCommandOption::PARAMETER_OPTIONAL, 'XML format ("ead" or "mods")', 'ead')
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $options['format'] = $this->normalizeExportFormat(
      $options['format'],
      array('ead', 'mods')
    );

    if (!isset($options['single-slug']))
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

    $this->includeXmlExportClassesAndHelpers();

    foreach ($rows as $row)
    {
      $resource = QubitInformationObject::getById($row['id']);

      // Don't export draft descriptions with public option
      if (isset($options['public']) && $options['public']
        && $resource->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_DRAFT_ID)
      {
        continue;
      }

      try
      {
        // Print warnings/notices here too, as they are often important.
        $errLevel = error_reporting(E_ALL);

        $rawXml = $this->captureResourceExportTemplateOutput($resource, $options['format'], $options);
        $xml = Qubit::tidyXml($rawXml);

        error_reporting($errLevel);
      }
      catch (Exception $e)
      {
        throw new sfException('Invalid XML generated for object '. $row['id'] .'.');
      }

      if (isset($options['single-slug']) && $options['format'] == 'ead')
      {
        if (is_dir($arguments['path']))
        {
          throw new sfException('When using the single-slug option with EAD, path should be a file.');
        }

        // If we're just exporting a single hierarchy of descriptions as EAD,
        // the given path is actually the full path and filename
        $filePath = $arguments['path'];
      }
      else
      {
        $filename = $this->generateSortableFilename($resource, 'xml', $options['format']);
        $filePath = sprintf('%s/%s', $arguments['path'], $filename);
      }

      if (false === file_put_contents($filePath, $xml))
      {
        throw new sfException("Cannot write to path: $filePath");
      }

      $this->indicateProgress($options['items-until-update']);

      if ($itemsExported++ % 1000 == 0)
      {
        Qubit::clearClassCaches();
      }
    }

    print "\nExport complete (". $itemsExported ." descriptions exported).\n";
  }
}
