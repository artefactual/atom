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
      new sfCommandOption('items-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Indicate progress every n items.'),
      new sfCommandOption('criteria', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export criteria'),
      new sfCommandOption('current-level-only', null, sfCommandOption::PARAMETER_NONE, 'Do not export child descriptions of exported items'),
      new sfCommandOption('site-url', null, sfCommandOption::PARAMETER_REQUIRED, 'The base URL of your AtoM site (example: "http://www.example.com").', false),
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    // Make sure arguments are valid
    $siteBaseUrl = $this->checkAndNormalizeSiteUrl($options['site-url']);
    $this->checkForValidFolder($arguments['folder']);

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $appRoot = dirname(__FILE__) .'/../..';

    include($appRoot .'/plugins/sfEadPlugin/lib/sfEadPlugin.class.php');
    include($appRoot .'/vendor/symfony/lib/helper/UrlHelper.php');
    include($appRoot .'/vendor/symfony/lib/helper/I18NHelper.php');
    include($appRoot .'/vendor/FreeBeerIso639Map.php');
    include($appRoot .'/vendor/symfony/lib/helper/EscapingHelper.php');
    include($appRoot .'/lib/helper/QubitHelper.php');

    $iso639convertor = new fbISO639_Map;
    $eadLevels = array('class', 'collection', 'file', 'fonds', 'item', 'otherlevel', 'recordgrp', 'series', 'subfonds', 'subgrp', 'subseries');

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', false);
    $sf_context = sfContext::createInstance($configuration);

    $itemsExported = 0;

    foreach($conn->query($this->exportQuerySql($options['criteria']), PDO::FETCH_ASSOC) as $row)
    {
      $resource = QubitInformationObject::getById($row['id']);

      // Determine language(s) used in the export
      $exportLanguage = sfContext::getInstance()->user->getCulture();
      $sourceLanguage = $resource->getSourceCulture();

      $ead = new sfEadPlugin($resource);

      ob_start();
      include('plugins/sfEadPlugin/modules/sfEadPlugin/templates/indexSuccess.xml.php');
      $output = ob_get_contents();
      ob_end_clean();

      $filename = 'ead_'. $row['id'] .'.xml';
      $filePath = $arguments['folder'] .'/'. $filename;
      file_put_contents($filePath, $output);

      // if progress indicator should be displayed, display it
      if (!isset($options['items-until-update']) || !($itemsExported % $options['items-until-update']))
      {
        print '.';
      }

      $itemsExported++;
    }
  }

  protected function checkAndNormalizeSiteUrl($url)
  {
    if (!$url)
    {
      return false;
    }

    if (filter_var($url, FILTER_VALIDATE_URL) === False)
    {
      throw new sfException('Invalid URL');
    }

    // Add trailing slash to URL if it's not already there
    $url = (substr($url, -1) != '/') ? $url .'/' : $url;

    return $url;
  }

  protected function checkForValidFolder($folder)
  {
    if (!is_dir($folder))
    {
      throw new sfException('You must specify a valid folder');
    }

    if (!is_writable($folder))
    {
      throw new sfException("Can't write to this folder");
    }
  }

  protected function exportQuerySql($criteria)
  {
    $whereClause = "parent_id=1";

    if ($criteria)
    {
      $whereClause .= ' AND '. $criteria;
    }

    return "SELECT * FROM information_object i INNER JOIN information_object_i18n i18n ON i.id=i18n.id WHERE ". $whereClause;
  }
}
