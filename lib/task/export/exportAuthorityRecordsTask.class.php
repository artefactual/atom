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
 * Export authority records to a CSV file
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class exportAuthorityRecordsTask extends exportBulkBaseTask
{
    protected $namespace        = 'csv';
    protected $name             = 'authority-export';
    protected $briefDescription = 'Export authority record data as CSV file(s)';

    protected $detailedDescription = <<<EOF
Export authority record data as CSV file(s).
EOF;

  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->addCoreArgumentsAndOptions();
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $this->checkPathIsWritable($arguments['path']);

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $this->context = sfContext::createInstance($configuration);

    // Prepare CSV exporter
    $writer = new csvActorExport($arguments['path']);
    $writer->setOptions(array('relations' => true));

    // Export actors and, optionally, related data
    $itemsExported = 0;

    foreach ($this->getActors() as $row)
    {
      $actor = QubitActor::getById($row['id']);
      $this->context->getUser()->setCulture($row['culture']);

      $writer->exportResource($actor);

      $this->indicateProgress($options['items-until-update']);
      $itemsExported++;
    }

    $this->log('');
    $this->logSection('csv', "Export complete ($itemsExported authority records exported).");
  }

  private function getActors()
  {
    $sql = "SELECT ai.id, ai.culture FROM actor_i18n ai INNER JOIN object o ON ai.id=o.id
            WHERE o.class_name='QubitActor' AND ai.id <> ?";

    return QubitPdo::fetchAll($sql, array(QubitActor::ROOT_ID), array('fetchMode' => PDO::FETCH_ASSOC));
  }
}
