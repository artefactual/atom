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
class eacExportTask extends sfBaseTask
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
      new sfCommandArgument('folder', sfCommandArgument::REQUIRED, 'The destination folder for XML export files.')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'), 
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('rows-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Output total rows imported every n rows.'),
      new sfCommandOption('skip-rows', null, sfCommandOption::PARAMETER_OPTIONAL, 'Skip n rows before importing.'),
      new sfCommandOption('criteria', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export criteria')
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

    sfContext::createInstance($this->configuration);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $appRoot = dirname(__FILE__) .'/../..';
    include($appRoot .'/plugins/sfEadPlugin/lib/sfEacPlugin.class.php');
    include($appRoot .'/vendor/symfony/lib/helper/UrlHelper.php');
    include($appRoot .'/vendor/symfony/lib/helper/I18NHelper.php');
    include($appRoot .'/vendor/FreeBeerIso639Map.php');
    include($appRoot .'/vendor/symfony/lib/helper/EscapingHelper.php');
    include($appRoot .'/vendor/symfony/lib/helper/TagHelper.php');
    include($appRoot .'/lib/helper/QubitHelper.php');

    $sql = "SELECT id FROM actor WHERE entity_type_id IN (" .
            QubitTerm::CORPORATE_BODY_ID . ", " . QubitTerm::PERSON_ID . 
            ", " . QubitTerm::FAMILY_ID . ")";

    $actors = $conn->query($sql, PDO::FETCH_ASSOC);

    foreach ($actors as $row)
    {
      $resource = QubitActor::getById($row['id']);

      $eac = new sfEacPlugin($resource);

      ob_start();
      include('plugins/sfEacPlugin/modules/sfEacPlugin/templates/indexSuccess.xml.php');
      $output = ob_get_contents();
      ob_end_clean();

      $filename = 'eac_'. $resource->id .'.xml';
      $filePath = $arguments['folder'] .'/'. $filename;
      file_put_contents($filePath, $output);

      print '.';
    }
  }
}
