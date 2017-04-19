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
abstract class exportBulkBaseTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    parent::__construct($dispatcher, $formatter);
  }

  protected function addCoreArgumentsAndOptions()
  {
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'The destination path for export file(s).')
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('items-until-update', null, sfCommandOption::PARAMETER_OPTIONAL, 'Indicate progress every n items.')
    ));
  }

  protected function addCommonArgumentsAndOptions()
  {
    $this->addCoreArgumentsAndOptions();

    $this->addOptions(array(
      new sfCommandOption('criteria', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export criteria'),
      new sfCommandOption('current-level-only', null, sfCommandOption::PARAMETER_NONE, 'Do not export child descriptions of exported items'),
      new sfCommandOption('single-slug', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export a single fonds or collection based on slug'),
      new sfCommandOption('public', null, sfCommandOption::PARAMETER_NONE, 'Do not export draft physical locations or child descriptions')
    ));
  }

  protected function checkPathIsWritable($path)
  {
    if (!is_dir($path))
    {
      throw new sfException('You must specify a valid path');
    }

    if (!is_writable($path))
    {
      throw new sfException("Can't write to this path");
    }
  }

  protected function normalizeExportFormat($format, $validFormats)
  {
    $format = strtolower($format);

    if (!in_array($format, $validFormats))
    {
      throw new sfException('Invalid format. Allowed formats: '. join(', ', $validFormats));
    }

    return $format;
  }

  protected function getDatabaseConnection()
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    return $databaseManager->getDatabase('propel')->getConnection();
  }

  public static function includeXmlExportClassesAndHelpers()
  {
    $appRoot = sfConfig::get('sf_root_dir');

    $includes = array(
      '/plugins/sfEadPlugin/lib/sfEadPlugin.class.php',
      '/plugins/sfModsPlugin/lib/sfModsPlugin.class.php',
      '/plugins/sfDcPlugin/lib/sfDcPlugin.class.php',
      '/plugins/sfIsaarPlugin/lib/sfIsaarPlugin.class.php',
      '/plugins/sfEacPlugin/lib/sfEacPlugin.class.php',
      '/vendor/symfony/lib/helper/UrlHelper.php',
      '/vendor/symfony/lib/helper/I18NHelper.php',
      '/vendor/FreeBeerIso639Map.php',
      '/vendor/symfony/lib/helper/EscapingHelper.php',
      '/lib/helper/QubitHelper.php'
    );

    foreach ($includes as $include)
    {
      include_once $appRoot.$include;
    }
  }

  public static function captureResourceExportTemplateOutput($resource, $format, $options = array())
  {
    $pluginName = 'sf'. ucfirst($format) .'Plugin';
    $template = sprintf('plugins/%s/modules/%s/templates/indexSuccess.xml.php', $pluginName, $pluginName);

    switch($format)
    {
      case 'ead':
        $eadLevels = array('class', 'collection', 'file', 'fonds', 'item', 'otherlevel', 'recordgrp', 'series', 'subfonds', 'subgrp', 'subseries');
        $ead = new sfEadPlugin($resource);
        break;

      case 'mods':
        $mods = new sfModsPlugin($resource);
        break;

      case 'eac':
        $eac = new sfEacPlugin($resource);
        break;

      case 'dc':
        $dc = new sfDcPlugin($resource);
        // Hack to get around issue with get_component helper run from worker (qubitConfiguration->getControllerDirs returns wrong dirs)
        $template = 'plugins/sfDcPlugin/modules/sfDcPlugin/templates/_dc.xml.php';
        break;

      default:
        throw Exception('Unknown format.');
    }

    $iso639convertor = new fbISO639_Map;

    // Determine language(s) used in the export
    $exportLanguage = sfContext::getInstance()->user->getCulture();
    $sourceLanguage = $resource->getSourceCulture();

    ob_start();
    include($template);
    $output = ob_get_contents();
    if ($format == 'dc')
    {
      // Hack to get around issue with get_component helper run from worker (qubitConfiguration->getControllerDirs returns wrong dirs)
      $output = '<?xml version="1.0" encoding="'.sfConfig::get('sf_charset', 'UTF-8')."\" ?>\n". $output;
    }
    ob_end_clean();

    return $output;
  }

  /**
   * Generate a suitable file name for export files.
   *
   * @param $resource  The information object we're exporting
   * @param $extension  The file extension (e.g. csv, xml)
   * @param $formatAbbreviation  The type of export format (e.g. ead, eac, mods)
   *
   * @return string  The generated filename based on the format, info object id, slug, and extension
   */
  public static function generateSortableFilename($resource, $extension, $formatAbbreviation)
  {
    $MAX_SLUG_CHARS = 200;

    // Pad ID with zeros so filenames can be sorted in creation order for imports
    return sprintf('%s_%s_%s.%s', $formatAbbreviation, str_pad($resource->id, 10, '0', STR_PAD_LEFT),
                   substr($resource->slug, 0, $MAX_SLUG_CHARS), $extension);
  }

  protected function indicateProgress($itemsUntilUpdate)
  {
    // If progress indicator should be displayed, display it
    if (!isset($itemsUntilUpdate) || !($itemsExported % $itemsUntilUpdate))
    {
      print '.';
    }
  }

  public static function informationObjectQuerySql($options)
  {
    if (isset($options['single-slug']))
    {
      // Fetch description for specific slug and its children
      $query = 'SELECT i.lft, i.rgt, i.id FROM information_object i INNER JOIN slug s ON i.id=s.object_id WHERE s.slug = ?';
      $slug = QubitPdo::fetchOne($query, array($options['single-slug']));

      if (false === $slug)
      {
        throw new sfException('Slug '.$options['single-slug'].' not found.');
      }

      $whereClause = 'i.lft >= '. $slug->lft .' AND i.rgt <='. $slug->rgt;
    }
    else
    {
      if ($options['criteria'])
      {
        // Use custom criteria
        $whereClause = $options['criteria'];
      }
      else
      {
        // Fetch top-level descriptions if EAD (EAD data nests children) or if only exporting top-level
        $whereClause = ($options['format'] == 'ead' || $options['current-level-only'])
          ? "parent_id = "
          : "i.id != ";
        $whereClause .= QubitInformationObject::ROOT_ID;
      }
    }

    // Assemble full query
    $query = "SELECT * FROM information_object i
      INNER JOIN information_object_i18n i18n ON i.id=i18n.id
      WHERE ". $whereClause;

    // Order by place in hierarchy so parents are exported before children
    $query .= ' ORDER BY i.lft';

    // EAD data nests children, so if exporting specific slug we just need top-level item
    if ($options['format'] == 'ead' && isset($options['single-slug']))
    {
      $query .= ' LIMIT 1';
    }

    return $query;
  }
}
