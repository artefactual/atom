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
 * Export flatfile data
 *
 * @package    symfony
 * @subpackage library
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitFlatfileExport
{
  protected $configurationLoaded = false;  // has the configuuration been loaded?

  public $columnNames     = array();       // ordered header column names
  public $standardColumns = array();       // flatfile columns that are object properties
  public $columnMap       = array();       // flatfile columns that map to object properties
  public $propertyMap     = array();       // flatfile columns that map to Qubit properties

  protected $resource;                     // current resource being exported
  protected $row;                          // current row being prepared for export

  protected $currentFileHandle;            // file handle of current export file
  protected $rowsExported = 0;             // count of how many rows have been exported
  protected $fileIndex = 0;                // current index of export file
  protected $rowsPerFile = 1000;           // how many rows until creating new export file

  protected $separatorChar = '|';          // character to use when imploding arrays
                                           // to a single value


  /*
   * Constructor
   *
   * The destination path can either by a directory (to export data as
   * multiple files) or a single file (for small exports).
   *
   * The archival standard is used to find a standard-specific export
   * configuration and for output file naming.
   *
   * @param string $destinationPath  destionation directory (or single file)
   * @param string $standard  archival standard
   * @param mixed $rowsPerFile  how many rows should be in each exported file
   *
   * @return void
   */
  public function __construct($destinationPath, $standard = null, $rowsPerFile = false)
  {
    $this->path     = $destinationPath;
    $this->standard = $standard;

    if ($rowsPerFile !== false)
    {
      $this->rowsPerFile = $rowsPerFile;
    }

    include_once sfConfig::get('sf_root_dir').'/lib/helper/QubitHelper.php';
  }


  /*
   *
   *  Configuration-related methods
   *  -----------------------------
   */

  /*
   * Load resource-specific export YAML configuration files
   * (a base config file for the resource type and an archival standard
   * specific config file that expands on/modifies the base config)
   *
   * This only needs to be called once per export
   *
   * @param string $resourceClass  class name
   *
   * @return void
   */
  public function loadResourceSpecificConfiguration($resourceClass)
  {
    // Load type-specific base export configuration
    $resourceTypeBaseConfigFile = $resourceClass .'.yml';
    $config = self::loadResourceConfigFile($resourceTypeBaseConfigFile, 'base');

    if ($this->standard)
    {
      // Load archival standard-specific export configuration for type
      // (this can augment and/or override the base configuration)
      $resourceTypeStandardConfigFile = $resourceClass .'-'. $this->standard .'.yml';
      $standardConfig = self::loadResourceConfigFile($resourceTypeStandardConfigFile, 'archival standard');

      // Allow standard-specific export configuration to override base config
      $this->overrideConfigData($config, $standardConfig);
    }

    $this->columnNames     = $config['columnNames'];
    $this->standardColumns = isset($config['direct']) ? $config['direct'] : array();
    $this->columnMap       = isset($config['map']) ? $config['map'] : array();
    $this->propertyMap     = isset($config['property']) ? $config['property'] : array();

    // If column names/order aren't specified, derive them
    if ($this->columnNames === null)
    {
      // Add standard columns
      $this->columnNames = ($this->standardColumns !== null) ? $this->standardColumns : array();

      // Add from column map
      if ($this->columnMap !== null)
      {
        $this->columnNames = array_merge($this->columnNames, array_values($this->columnMap));
      }

      // Add from property map
      if ($this->propertyMap !== null)
      {
        $this->columnNames = array_merge($this->columnNames, array_values($this->propertyMap));
      }
    }

    $this->cacheTaxonomies($config['cacheTaxonomies']);

    // Apply custom configuration logic defined by child classes
    $this->config($config);

    // Initiaize row in preparation for export
    $this->row = array_fill(0, count($this->columnNames), null);
    $this->configurationLoaded = true;
  }

  /*
   * Load configuration file, first looking in the config directory (to
   * allow users to easily override default behavior) then looking in the
   * lib/flatfile/config directory.
   *
   * @param string $file  configuration filename
   * @param string $roleDescription  description of configuration file role
   *
   * @return void
   */
  public static function loadResourceConfigFile($file, $roleDescription)
  {
    // First try a custom version of resource-specific export configuration
    $configFilePath = 'config/'. $file;

    // If custom version doesn't exist, load built-in version
    if (!file_exists($configFilePath))
    {
      $configFileBasePath = 'lib/flatfile/config';
      $configFilePath = $configFileBasePath .'/'. $file;
    }

    $config = sfYaml::load(realpath($configFilePath));

    if (gettype($config) != 'array')
    {
      throw new sfException('Missing/malformed resource '. $roleDescription .' config: '. $configFilePath);
    }

    return $config;
  }

  /*
   * Override config values with values from other config data
   *
   * @param array &$config  configuration data to override
   * @param array @mixin  configuration data to override it with
   *
   * @return void
   */
  function overrideConfigData(&$config, $mixin)
  {
    foreach($mixin as $key => $value)
    {
      if (!is_array($value))
      {
        $config[$key] = $value;
      }
      else
      {
        $this->overrideConfigData($config[$key], $mixin[$key]);
      }
    }
  }

  /*
   * Custom configuration logic
   *
   * (Child classes can override this if necessary)
   *
   * @return void
   */
  protected function config(&$config)
  {
  }


  /*
   *
   *  Taxonomy caching methods
   *  ------------------------
   */

  /*
   * Cache a number of taxonomies as properties of the current class instance
   *
   * @param array $map  keys are property names and values QubitTaxonomy
   *                    constants that represent IDs
   *
   * @return void
   */
  protected function cacheTaxonomies($map)
  {
    $taxonomyCacheMap = array();

    // Prepare taxonomy cache map
    foreach ($map as $property => $taxonomy)
    {
      $taxonomyCacheMap[$property] = constant('QubitTaxonomy::'. $taxonomy);
    }

    if (count($taxonomyCacheMap))
    {
      $this->cacheTaxonomiesAsProperties($taxonomyCacheMap);
    }
  }

  /*
   * Cache a number of taxonomies as properties of the current class instance
   *
   * @param array $map  keys are property names and values taxonomy IDs
   *
   * @return void
   */
  protected function cacheTaxonomiesAsProperties($map)
  {
    foreach($map as $propertyName => $taxonomyId)
    {
      $this->cacheTaxonomyAsProperty($propertyName, $taxonomyId);
    }
  }

  /*
   * Cache a taxonomy in a property of the current class instance
   *
   * @param string $propertyName  name of property to set
   * @param integer $taxonomyId  ID of taxonomy to cache
   *
   * @return void
   */
  protected function cacheTaxonomyAsProperty($propertyName, $taxonomyId)
  {
    $this->{$propertyName} = $this->getTaxonomyTermValues($taxonomyId);
  }

  /*
   * Get taxonomy terms as an array where key is ID and value is term as string
   *
   * @param integer $taxonomyId  ID of taxonomy to fetch
   *
   * @return array  key is term ID and value is term name
   */
  protected function getTaxonomyTermValues($taxonomyId)
  {
    $terms = array();

    foreach (QubitFlatfileImport::getTaxonomyTerms($taxonomyId) as $term)
    {
      $terms[$term->culture][$term->id] = $term->name;
    }

    // QubitFlatfileImport::getTaxonomyTerms has changed to allow a better
    // culture matching on import. On export we're still only using english terms
    return $terms['en'];
  }


  /*
   *
   *  Row processing methods
   *  ----------------------
   */

  /**
   * Set column value in current row if the column's being exported
   *
   * @param string $column  column name
   * @param string $value  value to set current row's column value to
   *
   * @return void
   */
  public function setColumn($column, $value)
  {
    $columnIndex = array_search($column, $this->columnNames);

    // Ignore columns that aren't in the column headers
    if (is_numeric($columnIndex))
    {
      // Set column, processing value beforehand
      $this->row[$columnIndex] = $this->content($value);
    }
  }

  /**
   * Set column value in current row to store notes if the column's being exported
   *
   * @param string $column  column name
   * @param int $noteTypeId  ID of the type of note to store
   *
   * @return void
   */

  public function setColumnToNotes($column, $noteTypeId)
  {
    $noteContent = array();

    foreach ($this->resource->getNotesByType(array('noteTypeId' => $noteTypeId)) as $note)
    {
      $noteContent[] = $note->content;
    }

    if (count($noteContent))
    {
      $this->setColumn($column, $noteContent);
    }
  }

  /**
   * If an array is provided as a value, implode it
   *
   * @param string $value  value
   *
   * @return void
   */
  protected function content($value)
  {
    if (is_array($value))
    {
      // Remove empty strings from the array via array_filter too, to prevent superfluous separators
      return implode($this->separatorChar, array_filter($value, 'strlen'));
    }

    return $value;
  }

  /**
   * Export a resource as a flatfile row
   *
   * @param object $resource  object to export
   *
   * @return void
   */
  public function exportResource(&$resource)
  {
    if (!$this->configurationLoaded)
    {
      $this->loadResourceSpecificConfiguration(get_class($resource));
    }

    $this->resource = $resource;

    // If writing to a directory, generate filename periodically to keep each
    // file's size small-ish, which makes importing the file easier in terms of
    // import time and memory usage.
    if (is_dir($this->path))
    {
      // Increase file index and delete file pointer if reader to start new file
      if (!($this->rowsExported % $this->rowsPerFile))
      {
        $this->fileIndex++;
        unset($this->currentFileHandle);
      }

      // Generate filename
      // Pad fileIndex with zeros so filenames can be sorted in creation order for imports
      $filenamePrepend = ($this->standard !== null) ? $this->standard .'_' : '';
      $filename = sprintf('%s%s.csv', $filenamePrepend, str_pad($this->fileIndex, 10, '0', STR_PAD_LEFT));
      $filePath = $this->path .'/'. $filename;
    }
    else
    {
      $filePath = $this->path;
    }

    // If file doesn't yet exist, write headers
    if (!file_exists($filePath))
    {
      $this->appendRowToCsvFile($filePath, $this->columnNames);
    }

    // Clear Qubit object cache periodically
    if (($this->rowsExported % $this->rowsPerFile) == 0)
    {
      Qubit::clearClassCaches();
    }

    $this->prepareRowFromResource();

    // Write row to file and initialize row
    $this->appendRowToCsvFile($filePath, $this->row);
    $this->row = array_fill(0, count($this->columnNames), null);
    $this->rowsExported++;
  }

  /**
   * Prepare row from resource
   *
   * @return void
   */
  public function prepareRowFromResource()
  {
    // Cycle through columns to populate row array
    foreach ($this->columnNames as $index => $column)
    {
      $value = $this->row[$index];

      // If row value hasn't been set to anything, attempt to get resource property
      if ($value === null)
      {
        if (in_array($column, $this->standardColumns))
        {
          $value = $this->resource->{$column};
        }
        else if (($sourceColumn = array_search($column, $this->columnMap)) !== false)
        {
          $value = $this->resource->{$sourceColumn};
        }
        else if (isset($this->propertyMap[$column]))
        {
          $value = $this->resource->getPropertyByName($this->propertyMap[$column])->__toString();
        }
      }

      // Add column value (imploding if necessary)
      $this->row[$index] = $this->content($value);
    }

    $this->modifyRowBeforeExport();
  }

  /**
   * Append row data to file
   *
   * @param string $filePath  path to file
   * @param array $row  array of each column's values
   *
   * @return void
   */
  protected function appendRowToCsvFile($filePath, $row)
  {
    if (!isset($this->currentFileHandle))
    {
      $this->currentFileHandle = fopen($filePath, 'a');
    }

    fputcsv($this->currentFileHandle, $row);
  }

  /*
   * Modify row data before it's appended to a file
   *
   * (Child classes can override this if necessary)
   *
   * @return void
   */
  protected function modifyRowBeforeExport()
  {
  }
}
