<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Bulk export data to XML
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class eadExportTask extends sfBaseTask
{
  protected $namespace        = 'export';
  protected $name             = 'bulk';
  protected $briefDescription = 'Bulk export multiple XML files at once';

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('folder', sfCommandArgument::REQUIRED, 'The destination folder for XML export files.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'), 
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('rows-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Output total rows imported every n rows.'),
      new sfCommandOption('skip-rows', null, sfCommandOption::PARAMETER_OPTIONAL, 'Skip n rows before importing.'),
      new sfCommandOption('criteria', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export criteria', '1=1')
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    if (!is_dir($arguments['folder']))
    {
      throw new sfException('You must specify a valid folder');
    }

    if (!is_writable($arguments['folder']))
    {
      throw new sfException("Can't write to this folder");
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $sql = "SELECT id FROM information_object";

    foreach($conn->query($sql, PDO::FETCH_ASSOC) as $row)
    {
      $resource = QubitInformationObject::getOne($row['id']);
      include('../../../plugins/sfEadPlugin/modules/sfEadPlugin/templates/indexSuccess.xml.php');
    }
  }
}
