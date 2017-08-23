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
class csvExportInformationObjectsTask extends exportBulkBaseTask
{
  protected $namespace        = 'csv';
  protected $name             = 'export';
  protected $briefDescription = 'Export descriptions as CSV file(s)';

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addCommonArgumentsAndOptions();
    $this->addOptions(array(
      new sfCommandOption('standard', null, sfCommandOption::PARAMETER_OPTIONAL, 'Description format ("isad" or "rad")', 'isad')
    ));
    $this->addOptions(array(
      new sfCommandOption('rows-per-file', null, sfCommandOption::PARAMETER_OPTIONAL, 'Rows per file (disregarded if writing to a file, not a directory)', false)
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    // Make sure standard is lower case
    $options['standard'] = $this->normalizeExportFormat(
      $options['standard'],
      array('isad', 'rad')
    );

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $sf_context = sfContext::createInstance($configuration);

    // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
    sfConfig::add(QubitSetting::getSettingsArray());

    $itemsExported = 0;

    $conn = $this->getDatabaseConnection();
    $rows = $conn->query($this->informationObjectQuerySql($options), PDO::FETCH_ASSOC);

    print 'Exporting as '. strtoupper($options['standard']) .".\n";

    // Instantiate CSV writer
    $writer = new csvInformationObjectExport(
      $arguments['path'],
      $options['standard'],
      $options['rows-per-file']
    );

    $writer->setOptions($options);

    foreach ($rows as $row)
    {
      $sf_context->getUser()->setCulture($row['culture']);
      $resource = QubitInformationObject::getById($row['id']);

      // Don't export draft descriptions with public option
      if (isset($options['public']) && $options['public']
        && $resource->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_DRAFT_ID)
      {
        continue;
      }

      $writer->exportResource($resource);

      $this->indicateProgress($options['items-until-update']);

      $itemsExported++;
    }

    print "\nExport complete (". $itemsExported ." descriptions exported).\n";
  }
}
