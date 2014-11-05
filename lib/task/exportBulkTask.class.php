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
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'The destination path for XML export file(s).')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('items-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Indicate progress every n items.'),
      new sfCommandOption('format', null, sfCommandOption::PARAMETER_OPTIONAL, 'XML format ("ead" or "mods")', 'ead'),
      new sfCommandOption('criteria', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export criteria'),
      new sfCommandOption('current-level-only', null, sfCommandOption::PARAMETER_NONE, 'Do not export child descriptions of exported items'),
      new sfCommandOption('single-id', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export an EAD file for a single fonds or collection based on id')
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    // Make sure arguments are valid
    $options['format'] = strtolower($options['format']);
    $this->checkForValidExportFormat($options['format']);

    if (!isset($options['single-id']))
    {
      $this->checkForValidFolder($arguments['path']);
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $appRoot = dirname(__FILE__) .'/../..';

    include($appRoot .'/plugins/sfEadPlugin/lib/sfEadPlugin.class.php');
    include($appRoot .'/vendor/symfony/lib/helper/UrlHelper.php');
    include($appRoot .'/vendor/symfony/lib/helper/I18NHelper.php');
    include($appRoot .'/vendor/FreeBeerIso639Map.php');
    include($appRoot .'/vendor/symfony/lib/helper/EscapingHelper.php');
    include($appRoot .'/lib/helper/QubitHelper.php');

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $sf_context = sfContext::createInstance($configuration);

    $iso639convertor = new fbISO639_Map;
    $eadLevels = array('class', 'collection', 'file', 'fonds', 'item', 'otherlevel', 'recordgrp', 'series', 'subfonds', 'subgrp', 'subseries');
    $pluginName = 'sf'. ucfirst($options['format']) .'Plugin';
    $exportTemplate = sprintf('plugins/%s/modules/%s/templates/indexSuccess.xml.php', $pluginName, $pluginName);

    $itemsExported = 0;

    $rows = $conn->query($this->exportQuerySql($options), PDO::FETCH_ASSOC);

    foreach($rows as $row)
    {
      $resource = QubitInformationObject::getById($row['id']);

      // Determine language(s) used in the export
      $exportLanguage = sfContext::getInstance()->user->getCulture();
      $sourceLanguage = $resource->getSourceCulture();

      if ($options['format'] == 'ead')
      {
        $ead = new sfEadPlugin($resource);
      }
      else
      {
        $mods = new sfModsPlugin($resource);
      }

      // capture XML template output
      ob_start();
      include($exportTemplate);
      $rawOutput = ob_get_contents();
      ob_end_clean();

      // clean up XML
      $xml = simplexml_load_string($rawOutput);
      $dom = new DOMDocument("1.0");
      $dom->preserveWhiteSpace = false;
      $dom->formatOutput = true;
      $dom->loadXML($xml->asXML());
      $output = $dom->saveXML();

      if (isset($options['single-id']))
      {
        // If we're just exporting the one record, the given path
        // is actually the full path+filename.
        $filePath = $arguments['path'];
      }
      else
      {
        // save XML file
        // (padding ID with zeros so filenames can be sorted in creation order for imports)
        $filename = sprintf('%s_%s.xml', $options['format'], str_pad($row['id'], 9, '0', STR_PAD_LEFT));
        $filePath = sprintf('%s/%s', $arguments['path'], $filename);
      }

      file_put_contents($filePath, $output);

      // if progress indicator should be displayed, display it
      if (!isset($options['items-until-update']) || !($itemsExported % $options['items-until-update']))
      {
        print '.';
      }

      $itemsExported++;
    }

    print "\nExport complete (". $itemsExported ." descriptions exported).\n";
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

  protected function checkForValidExportFormat($format)
  {
    $validFormats = array('ead', 'mods');

    if (!in_array($format, $validFormats))
    {
      throw new sfException('Invalid format. Allowed formats: '. join(', ', $validFormats));
    }
  }

  protected function exportQuerySql($options)
  {
    // EAD data nests children, so we only have to get top-level items
    $whereClause = ($options['format'] == 'ead' || $options['current-level-only'])
      ? "parent_id=". QubitInformationObject::ROOT_ID
      : "i.id != 1";

    if ($options['criteria'])
    {
      $whereClause .= ' AND '. $options['criteria'];
    }

    $query = "SELECT * FROM information_object i
      INNER JOIN information_object_i18n i18n ON i.id=i18n.id
      WHERE ". $whereClause;

    if (isset($options['single-id']))
    {
      $query .= ' AND i.id=' . $options['single-id'] . ' LIMIT 1';
    }

    return $query;
  }
}
