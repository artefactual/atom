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
 * Export flatfile data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitFlatfileExport
{
    public $columnNames = [];       // ordered header column names
    public $standardColumns = [];       // flatfile columns that are object properties
    public $columnMap = [];       // flatfile columns that map to object properties
    public $propertyMap = [];       // flatfile columns that map to Qubit properties
    public $user;          // user doing the export
    protected $configurationLoaded = false;  // has the configuuration been loaded?

    protected $resource;                     // current resource being exported
    protected $row;                          // current row being prepared for export

    protected $currentFileHandle;            // file handle of current export file
    protected $rowsExported = 0;             // count of how many rows have been exported
    protected $fileIndex = 0;                // current index of export file
    protected $rowsPerFile = 1000;           // how many rows until creating new export file

    protected $separatorChar = '|';          // character to use when imploding arrays to a single value
    protected $params;
    protected $nonVisibleElementsIncluded;

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
        $this->path = $destinationPath;
        $this->standard = $standard;

        if (false !== $rowsPerFile) {
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
        $resourceTypeBaseConfigFile = $resourceClass.'.yml';
        $config = self::loadResourceConfigFile($resourceTypeBaseConfigFile, 'base');

        if ($this->standard) {
            // Load archival standard-specific export configuration for type
            // (this can augment and/or override the base configuration)
            $resourceTypeStandardConfigFile = $resourceClass.'-'.$this->standard.'.yml';
            $standardConfig = self::loadResourceConfigFile($resourceTypeStandardConfigFile, 'archival standard');

            // Allow standard-specific export configuration to override base config
            $this->overrideConfigData($config, $standardConfig);
        }

        $this->columnNames = $config['columnNames'];
        $this->standardColumns = isset($config['direct']) ? $config['direct'] : [];
        $this->columnMap = isset($config['map']) ? $config['map'] : [];
        $this->propertyMap = isset($config['property']) ? $config['property'] : [];

        // If column names/order aren't specified, derive them
        if (null === $this->columnNames) {
            // Add standard columns
            $this->columnNames = (null !== $this->standardColumns) ? $this->standardColumns : [];

            // Add from column map
            if (null !== $this->columnMap) {
                $this->columnNames = array_merge($this->columnNames, array_values($this->columnMap));
            }

            // Add from property map
            if (null !== $this->propertyMap) {
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
        $configFilePath = 'config/'.$file;

        // If custom version doesn't exist, load built-in version
        if (!file_exists($configFilePath)) {
            $configFileBasePath = 'lib/flatfile/config';
            $configFilePath = $configFileBasePath.'/'.$file;
        }

        $config = sfYaml::load(realpath($configFilePath));

        if ('array' != gettype($config)) {
            throw new sfException('Missing/malformed resource '.$roleDescription.' config: '.$configFilePath);
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
    public function overrideConfigData(&$config, $mixin)
    {
        foreach ($mixin as $key => $value) {
            if (!is_array($value)) {
                $config[$key] = $value;
            } else {
                // If config array being overridden is a sequential array,
                // replace all elements in the array (e.g. isad 'columnNames').
                // If config array being overridden is an associative array,
                // override elements are merged so do not use this logic
                // (e.g. rad 'map').
                if ($config[$key] === array_values($config[$key])) {
                    $config[$key] = [];
                }

                $this->overrideConfigData($config[$key], $mixin[$key]);
            }
        }
    }

    /*
     *
     *  Row processing methods
     *  ----------------------
     */

    /**
     * Set column value in current row if the column's being exported.
     *
     * @param string $column column name
     * @param string $value  value to set current row's column value to
     */
    public function setColumn($column, $value)
    {
        $columnIndex = array_search($column, $this->columnNames);

        // Ignore columns that aren't in the column headers
        if (is_numeric($columnIndex)) {
            // Set column, processing value beforehand
            $this->row[$columnIndex] = $this->content($value);
        }
    }

    /**
     * Set column value in current row to store notes if the column's being exported.
     *
     * @param string $column     column name
     * @param int    $noteTypeId ID of the type of note to store
     */
    public function setColumnToNotes($column, $noteTypeId)
    {
        $noteContent = [];

        foreach ($this->resource->getNotesByType(['noteTypeId' => $noteTypeId]) as $note) {
            $noteContent[] = $note->content;
        }

        if (count($noteContent)) {
            $this->setColumn($column, $noteContent);
        }
    }

    /**
     * Export a resource as a flatfile row.
     *
     * @param object $resource object to export
     */
    public function exportResource(&$resource)
    {
        if (!$this->configurationLoaded) {
            $this->loadResourceSpecificConfiguration(get_class($resource));
        }

        if (!$this->params['nonVisibleElementsIncluded']) {
            $this->getHiddenVisibleElementCsvHeaders();
        }

        $this->resource = $resource;

        // If writing to a directory, generate filename periodically to keep each
        // file's size small-ish, which makes importing the file easier in terms of
        // import time and memory usage.
        if (is_dir($this->path)) {
            // Increase file index and delete file pointer if reader to start new file
            if (!($this->rowsExported % $this->rowsPerFile)) {
                ++$this->fileIndex;
                unset($this->currentFileHandle);
            }

            // Generate filename
            // Pad fileIndex with zeros so filenames can be sorted in creation order for imports
            $filenamePrepend = (null !== $this->standard) ? $this->standard.'_' : '';
            $filename = sprintf('%s%s.csv', $filenamePrepend, str_pad($this->fileIndex, 10, '0', STR_PAD_LEFT));
            $filePath = $this->path.'/'.$filename;
        } else {
            $filePath = $this->path;

            // Replace file if it already exists, yet we haven't exported any rows
            if (file_exists($filePath) && !$this->rowsExported) {
                unlink($filePath);
            }
        }

        $this->prepareRowFromResource();

        if (!empty($this->nonVisibleElementsIncluded)) {
            // Remove elements from $this->columnNames that are in $this->nonVisibleElementsIncluded
            $this->columnNames = array_diff($this->columnNames, $this->nonVisibleElementsIncluded);
        }

        // If file doesn't yet exist, write headers
        if (!file_exists($filePath)) {
            $this->appendRowToCsvFile($filePath, $this->columnNames);
        }

        // Clear Qubit object cache periodically
        if (($this->rowsExported % $this->rowsPerFile) == 0) {
            Qubit::clearClassCaches();
        }

        // Write row to file and initialize row
        $this->row = array_slice($this->row, 0, count($this->columnNames));
        $this->appendRowToCsvFile($filePath, $this->row);
        $this->row = array_fill(0, count($this->columnNames), null);
        ++$this->rowsExported;
    }

    /**
     * Prepare row from resource.
     */
    public function prepareRowFromResource()
    {
        // Cycle through columns to populate row array
        foreach ($this->columnNames as $index => $column) {
            $value = $this->row[$index];

            // If row value hasn't been set to anything and element is not hidden,
            // attempt to get resource property
            if (null === $value && !in_array($column, $this->nonVisibleElementsIncluded)) {
                if (in_array($column, $this->standardColumns)) {
                    $value = $this->resource->{$column};
                } elseif (($sourceColumn = array_search($column, $this->columnMap)) !== false) {
                    $value = $this->resource->{$sourceColumn};
                } elseif (isset($this->propertyMap[$column])) {
                    $value = $this->resource->getPropertyByName($this->propertyMap[$column])->__toString();
                }

                // Add column value (imploding if necessary)
                $this->row[$index] = $this->content($value);
            } else {
                // Unset hidden elements columns
                unset($this->row[$index]);
            }
        }

        $this->modifyRowBeforeExport();
    }

    /**
     * Get parameters to determine hidden elements.
     *
     * @param array parameters for CSV export
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }

    /**
     * Get list of hidden elements.
     */
    public function getHiddenVisibleElementCsvHeaders()
    {
        $nonVisibleElementsIncluded = [];
        $nonVisibleElements = [];

        if (!$this->params['nonVisibleElementsIncluded']) {
            $template = sfConfig::get('app_default_template_'.strtolower($this->params['objectType']));

            // Get list of elements hidden from settings
            foreach (sfConfig::getAll() as $setting => $value) {
                if (
                    (false !== strpos($setting, 'app_element_visibility_'.$template))
                    && (!strpos($setting, '__source'))
                    && (0 == sfConfig::get($setting))
                ) {
                    array_push($nonVisibleElements, $setting);
                }
            }

            if (!empty($nonVisibleElements)) {
                $mapPath = sfConfig::get('sf_lib_dir').DIRECTORY_SEPARATOR.'job/visibleElementsHeaderMap.yml';
                $headers = sfYaml::load($mapPath);

                // Get xml/csv headers to remove
                foreach ($nonVisibleElements as $e) {
                    $prefix = 'app_element_visibility_';
                    $element = str_replace($prefix, '', $e);

                    if (array_key_exists($element, $headers)) {
                        foreach ($headers[$element]['csv'] as $ele) {
                            array_push($nonVisibleElementsIncluded, $ele);
                        }
                    }
                }
            }
        }

        $this->nonVisibleElementsIncluded = $nonVisibleElementsIncluded;
    }

    /*
     * Custom configuration logic
     *
     * (Child classes can override this if necessary)
     *
     * @return void
     */
    protected function config(&$config) {}

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
        $taxonomyCacheMap = [];

        // Prepare taxonomy cache map
        foreach ($map as $property => $taxonomy) {
            $taxonomyCacheMap[$property] = constant('QubitTaxonomy::'.$taxonomy);
        }

        if (count($taxonomyCacheMap)) {
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
        foreach ($map as $propertyName => $taxonomyId) {
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
        $terms = [];

        foreach (QubitFlatfileImport::getTaxonomyTerms($taxonomyId) as $term) {
            $terms[$term->culture][$term->id] = $term->name;
        }

        // QubitFlatfileImport::getTaxonomyTerms has changed to allow a better
        // culture matching on import. On export we're still only using english terms
        return $terms['en'];
    }

    /**
     * If an array is provided as a value, implode it.
     *
     * @param string $value value
     */
    protected function content($value)
    {
        if (is_array($value)) {
            // Remove empty strings from the array via array_filter too, to prevent superfluous separators
            return implode($this->separatorChar, array_filter($value, 'strlen'));
        }

        return $value;
    }

    /**
     * Append row data to file.
     *
     * @param string $filePath path to file
     * @param array  $row      array of each column's values
     */
    protected function appendRowToCsvFile($filePath, $row)
    {
        if (!isset($this->currentFileHandle)) {
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
        $this->setDigitalObjectValues();
    }

    /**
     * Set digital object URL and checksum values.
     *
     * If the user has "readMaster" permission use master DO values, otherwise
     * use reference DO values
     */
    protected function setDigitalObjectValues()
    {
        $digitalObject = $this->getAllowedDigitalObject();

        if (!empty($digitalObject)) {
            $digitalObjectUri = $digitalObject->getFullPath();

            if (!$digitalObject->derivativesGeneratedFromExternalMaster($digitalObject->usageId)) {
                $digitalObjectUri = rtrim(QubitSetting::getByName('siteBaseUrl'), '/ ').$digitalObjectUri;
            }

            $this->setColumn(
                'digitalObjectURI',
                $digitalObjectUri
            );
            $this->setColumn(
                'digitalObjectChecksum',
                $digitalObject->getChecksum()
            );
        } else {
            $this->setColumn('digitalObjectURI', '');
            $this->setColumn('digitalObjectChecksum', '');
        }
    }

    /**
     * Get the highest quality digital object to which the current user has access.
     *
     * @return null|QubitDigitalObject a digital object, or null
     */
    protected function getAllowedDigitalObject()
    {
        $digitalObject = $this->resource->getDigitalObject();

        if (null === $digitalObject) {
            return null;
        }

        // If user can access the master DO, use the master DO metadata
        if (QubitAcl::check($this->resource, 'readMaster', ['user' => $this->user])) {
            return $digitalObject;
        }

        // If user can access the reference DO, use the reference DO metadata
        if (
            QubitAcl::check(
                $this->resource,
                'readReference',
                ['user' => $this->user]
            )
        ) {
            return $digitalObject->reference;
        }

        return null;
    }
}
