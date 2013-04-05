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

class digitalObjectExtractTextTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace = 'digitalobject';
    $this->name = 'extract-text';
    $this->briefDescription = 'Extracts text from PDFs for search indexing';
  }

  protected function execute($arguments = array(), $options = array())
  {
    $timer = new QubitTimer;

    sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $this->logSection('digital object', 'Extracting text for the digital objects...');

    // Get all master digital objects
    $query = 'SELECT id FROM digital_object WHERE parent_id IS NULL AND mime_type = \'application/pdf\'';

    // Do work
    foreach (QubitPdo::fetchAll($query) as $item)
    {
      $do = QubitDigitalObject::getById($item->id);

      if (null == $do)
      {
        continue;
      }

      $this->logSection('digital object', sprintf('Extracting text for %s... (%ss)',
        $do->name, $timer->elapsed()));

      $do->extractText($conn);
    }

    $this->logSection('digital object', 'Done extracting text for the digital objects!');
  }
}
