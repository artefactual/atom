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
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'The destination path for export file(s).')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('items-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Indicate progress every n items.'),
      new sfCommandOption('criteria', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export criteria')
    ));
  }

  /**
   * @see exportBulkBaseTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->checkPathIsWritable($arguments['path']);

    sfContext::createInstance($this->configuration);

    $itemsExported = 0;

    $conn = $this->getDatabaseConnection();

    $this->includeXmlExportClassesAndHelpers();

    // Using left join and comparisons to null here to make sure we exclude
    // actor rows associated with repositories and users.
    $query = 'SELECT a.id FROM actor a
              JOIN actor_i18n ai ON a.id = ai.id
              JOIN object o ON a.id = o.id
              WHERE a.id != ? AND o.class_name = ?';

    if ($options['criteria'])
    {
      $query .= ' AND '.$options['criteria'];
    }

    foreach (QubitPdo::fetchAll($query, array(QubitActor::ROOT_ID, 'QubitActor')) as $row)
    {
      // Fetch description then assocated actors
      $resource = QubitActor::getById($row->id);

      $filename = $this->generateSortableFilename($resource, 'xml', 'eac');
      $filePath = sprintf('%s/%s', $arguments['path'], $filename);

      // Only export actor the first time it's encountered in a description
      if (!file_exists($filePath))
      {
        $rawXml = $this->captureResourceExportTemplateOutput($resource, 'eac');
        try
        {
          $xml = Qubit::tidyXml($rawXml);
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
      else
      {
        $this->log("$filePath already exists, skipping...");
      }
    }

    $this->log("\nExport complete ($itemsExported actors exported).");
  }
}
