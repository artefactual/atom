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

require_once __DIR__.'/../vendor/composer/autoload.php';

/**
 * Importer for Physical Object CSV data
 *
 * @package    AccessToMemory
 * @subpackage PhysicalObject
 * @author     David Juhasz <djuhasz@artefactual.com>
 */
class PhysicalObjectCsvImporter
{
  static $columnMap = [
    'legacyId'         => 'legacyId',
    'name'             => 'name',
    'type'             => 'typeId',
    'location'         => 'location',
    'culture'          => 'culture',
    'descriptionSlugs' => 'informationObjectIds',
  ];

  protected $context;
  protected $data;
  protected $dbcon;
  protected $errorLogHandle;
  protected $filename;
  protected $matchedExisting;
  protected $offset              = 0;
  protected $ormClasses;
  protected $physicalObjectTypeTaxonomy;
  protected $reader;
  protected $rowsImported        = 0;
  protected $rowsTotal           = 0;
  protected $typeIdLookupTable;

  // Default options
  protected $options = [
    'defaultCulture'      => 'en',
    'errorLog'            => null,
    'header'              => null,
    'multiValueDelimiter' => '|',
    'noInsert'            => false,
    'onMultiMatch'        => 'skip',
    'progressFrequency'   => 1,
    'sourceName'          => null,
    'updateSearchIndex'   => false,
    'updateOnMatch'       => false,
  ];

  public function __construct(sfContext $context = null, $dbcon = null,
    $options = array())
  {
    if (null === $context)
    {
      $context = new sfContext(ProjectConfiguration::getActive());
    }

    $this->setOrmClasses([
      'informationObject' => QubitInformationObject::class,
      'keymap'            => QubitKeymap::class,
      'physicalObject'    => QubitPhysicalObject::class,
      'relation'          => QubitRelation::class,
    ]);

    $this->physicalObjectTypeTaxonomy = new QubitTaxonomy(
      QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID);

    $this->context = $context;
    $this->dbcon   = $dbcon;

    $this->setOptions($options);
  }

  public function setOrmClasses(Array $classes)
  {
    $this->ormClasses = $classes;
  }

  public function __get($name)
  {
    switch ($name)
    {
      case 'context':
        return $this->$name;

        break;

      case 'dbcon':
        return $this->getDbConnection();

        break;

      case 'typeIdLookupTable':
        return $this->getTypeIdLookupTable();

        break;

      default:
        throw new sfException("Unknown or inaccessible property \"$name\"");
    }
  }

  public function __set($name, $value)
  {
    switch ($name)
    {
      case 'dbcon':
      case 'typeIdLookupTable':
        $this->$name = $value;

        break;

      default:
        throw new sfException("Couldn't set unknown property \"$name\"");
    }
  }

  public function setFilename($filename)
  {
    $this->filename = $this->validateFilename($filename);
  }

  public function getFilename()
  {
    return $this->filename;
  }

  public function validateFilename($filename)
  {
    if (!file_exists($filename))
    {
      throw new sfException("Can not find file $filename");
    }

    if (!is_readable($filename))
    {
      throw new sfException("Can not read $filename");
    }

    return $filename;
  }

  public function setOptions(array $options = null)
  {
    if (empty($options))
    {
      return;
    }

    foreach ($options as $name => $val)
    {
      $this->setOption($name, $val);
    }
  }

  public function getOptions()
  {
    return $this->options;
  }

  public function setOption(String $name, $value)
  {
    switch ($name)
    {
      case 'header':
        $this->setHeader($value);

        break;

      case 'offset':
        $this->setOffset($value);

        break;

      case 'progressFrequency':
        $this->setProgressFrequency($value);

        break;

      // boolean options
      case 'updateOnMatch':
      case 'updateSearchIndex':
      case 'noInsert':
        $this->options[$name] = (bool) $value;

        break;

      default:
        $this->options[$name] = $value;
    }
  }

  public function getOption(String $name)
  {
    if (isset($this->options[$name]))
    {
      return $this->options[$name];
    }
    elseif ('sourceName' == $name)
    {
      return basename($this->filename);
    }

    return null;
  }

  public function setPhysicalObjectTypeTaxonomy(QubitTaxonomy $object)
  {
    $this->physicalObjectTypeTaxonomy = $object;
  }

  public function getPhysicalObjectTypeTaxonomy()
  {
    return $this->physicalObjectTypeTaxonomy;
  }

  public function setOffset(int $value)
  {
    $this->offset = $value;
  }

  public function getOffset()
  {
    return $this->offset;
  }

  public function setHeader(String $str = null)
  {
    if (null === $str)
    {
      $this->options['header'] = null;

      return;
    }

    $columnNames = explode(',', trim($str));

    // Trim whitespace
    $columnNames = array_map('trim', $columnNames);

    // Remove empty values
    $columnNames = array_filter($columnNames, function ($val) {
      return !empty($val);
    });

    if (empty($columnNames))
    {
      $msg = <<<EOM
Invalid header. Please provide a CSV delimited list of column names
e.g. "name,location,type,culture".
EOM;

      throw new sfException($msg);
    }

    // Throw error on unknown column names
    foreach ($columnNames as $name)
    {
      if (!array_key_exists($name, self::$columnMap))
      {
        throw new sfException(sprintf('Column name "%s" in header is invalid',
          $name));
      }
    }

    $this->options['header'] = $columnNames;
  }

  public function getHeader()
  {
    if (isset($this->options['header']))
    {
      return $this->options['header'];
    }
    else if (null !== $this->reader)
    {
      return $this->reader->getHeader();
    }
  }

  public function getRow($offset)
  {
    $stmt = (new \League\Csv\Statement)->offset($offset);

    return $this->getRecords($stmt)->fetchOne();
  }

  public function countRowsImported()
  {
    return $this->rowsImported;
  }

  public function countRowsTotal()
  {
    return $this->rowsTotal;
  }

  public function doImport($filename = null)
  {
    if (null !== $filename)
    {
      $this->setFilename($filename);
    }

    if (null === $this->filename)
    {
      $msg = <<<EOL
Please use setFilename(\$filename) or doImport(\$filename) to specify the CSV
file you wish to import.
EOL;
      throw new sfException($msg);
    }

    $this->reader = $this->readCsvFile($this->filename);
    $stmt = (new \League\Csv\Statement)->offset($this->offset);
    $records = $this->getRecords($stmt);

    foreach ($records as $record)
    {
      $this->offset++;

      try
      {
        $data = $this->processRow($record);
        $this->savePhysicalobject($data);
      }
      catch (UnexpectedValueException $e)
      {
        $this->logError(sprintf('Warning! skipped row [%u/%u]: %s',
          $this->offset, $this->rowsTotal, $e->getMessage()));

        continue;
      }

      $this->rowsImported++;
      $this->progressUpdate($data);
    }
  }

  public function processRow($data)
  {
    if (0 == strlen($data['name']) && 0 == strlen($data['location']))
    {
      throw new UnexpectedValueException('No name or location defined');
    }

    $culture = $this->getRecordCulture($data['culture']);

    foreach (self::$columnMap as $oldkey => $newkey)
    {
      $prow[$newkey] = $this->processColumn($oldkey, $data[$oldkey], $culture);
    }

    return $prow;
  }

  public function getRecordCulture($culture = null)
  {
    $culture = trim($culture);

    if (!empty($culture))
    {
      return strtolower($culture);
    }

    if (!empty($this->options['defaultCulture']))
    {
      return strtolower($this->options['defaultCulture']);
    }

    if (!empty(sfConfig::get('default_culture')))
    {
      return strtolower(sfConfig::get('default_culture'));
    }

    throw new UnexpectedValueException('Couldn\'t determine row culture');
  }

  public function savePhysicalobject($data)
  {
    // Setting the propel::defaultCulture is necessary for non-English rows
    // to prevent creating an empty i18n row with culture 'en'
    sfPropel::setDefaultCulture($data['culture']);

    $new = false;

    if (null === $physobj = $this->searchForMatchingName($data))
    {
      if ($this->getOption('noInsert'))
      {
        throw new UnexpectedValueException(sprintf(
          'Couldn\'t match name "%s"', $data['name']
        ));
      }

      // Create a new db object, if no match is found
      $physobj = new $this->ormClasses['physicalObject'];
      $physobj->name = $data['name'];

      $new = true;
    }

    $physobj->typeId      = $data['typeId'];
    $physobj->location    = $data['location'];
    $physobj->indexOnSave = $this->getOption('updateSearchIndex');

    $physobj->save($this->dbcon);

    $this->createKeymapEntry($physobj, $data);

    if ($new)
    {
      $physobj->addInfobjRelations($data['informationObjectIds']);
    }
    else
    {
      $physobj->updateInfobjRelations($data['informationObjectIds']);
    }
  }

  /**
   * Create keymap entry for object
   *
   * @param string $sourceName  Name of source data
   * @param int $sourceId  ID from source data
   * @param object $object  Object to create entry for
   *
   * @return void
   */
  public function createKeymapEntry($object, $csvdata)
  {
    $keymap = new $this->ormClasses['keymap'];
    $keymap->sourceName = $this->getOption('sourceName');

    if (!empty($csvdata['legacyId']))
    {
      $keymap->sourceId = $csvdata['legacyId'];
    }

    // Determine target name using object class
    $keymap->targetName = sfInflector::underscore(
      str_replace('Qubit', '', get_class($object)));
    $keymap->targetId   = $object->id;

    $keymap->save();
  }

  public function searchForMatchingName($data)
  {
    $this->matchedExisting = 0;

    if (!$this->getOption('updateOnMatch'))
    {
      return null;
    }

    $matches = $this->ormClasses['physicalObject']::getByName(
      $data['name'],
      array('culture' => $data['culture'])
    );

    if (0 == count($matches))
    {
      return null;
    }
    else if (1 == count($matches))
    {
      $this->matchedExisting = 1;

      return $matches->current();
    }
    else
    {
      return $this->handleMultipleMatches($data['name'], $matches);
    }
  }

  public function handleMultipleMatches($name, $matches)
  {
    $this->matchedExisting = count($matches);

    if ('skip' == $this->getOption('onMultiMatch'))
    {
      throw new UnexpectedValueException(sprintf(
        'name "%s" matched %u existing records', $name, $this->matchedExisting
      ));
    }

    if ('first' == $this->getOption('onMultiMatch'))
    {
      // Return first match
      return $matches->current();
    }
  }

  protected function log($msg)
  {
    // Just echo to STDOUT for now
    echo $msg.PHP_EOL;
  }

  protected function logError($msg)
  {
    fwrite($this->getErrorLogHandle(), $msg.PHP_EOL);
  }

  protected function setProgressFrequency(int $freq)
  {
    // Note: $progressFrequency == 0 turns off logging
    $this->options['progressFrequency'] = ($freq > 0) ? $freq : 0;
  }

  protected function progressUpdate($data)
  {
    $freq = $this->getOption('progressFrequency');

    if (1 == $freq)
    {
      if (0 == $this->matchedExisting)
      {
        $msg = 'Row [%u/%u]: name "%s" imported';
      }
      else {
        $msg = 'Row [%u/%u]: Matched and updated name "%s"';
      }

      $this->log(sprintf($msg, $this->offset, $this->rowsTotal, $data['name']));
    }
    else if ($freq > 1 && 0 == $this->rowsImported % $freq)
    {
      $this->log(sprintf('Imported %u of %u rows...',
        $this->rowsImported, $this->rowsTotal));
    }
  }

  protected function getDbConnection()
  {
    if (null === $this->dbcon)
    {
      $this->dbcon = Propel::getConnection();
    }

    return $this->dbcon;
  }

  protected function getErrorLogHandle()
  {
    if (null === $filename = $this->getOption('errorLog'))
    {
      return STDERR;
    }

    if (!isset($this->errorLogHandle))
    {
      $this->errorLogHandle = fopen($filename, 'w');
    }

    return $this->errorLogHandle;
  }

  protected function readCsvFile($filename)
  {
    $reader = \League\Csv\Reader::createFromPath($filename, 'r');

    if (!isset($this->options['header']))
    {
      // Use first row of CSV file as header
      $reader->setHeaderOffset(0);
    }

    $this->rowsTotal = count($reader);

    return $reader;
  }

  protected function getRecords($stmt)
  {
    if (isset($this->options['header']))
    {
      $records = $stmt->process($this->reader, $this->options['header']);
    }
    else
    {
      $records = $stmt->process($this->reader);
    }

    return $records;
  }

  protected function processColumn($key, $val, $culture)
  {
    switch ($key)
    {
      case 'culture':
        $val = $culture;

        break;

      case 'type':
        $val = $this->lookupTypeId($val, $culture);

        break;

      case 'descriptionSlugs':
        $val = $this->processDescriptionSlugs($val);

        break;

      default:
        $val = trim($val);
    }

    // I'm not using !empty() for this conditional because I want to return an
    // empty array when $val = array()
    if ('' !== $val)
    {
      return $val;
    }
  }

  protected function processDescriptionSlugs(string $str = null)
  {
    $ids = [];

    if (null === $str)
    {
      return $ids;
    }

    foreach ($this->processMultiValueColumn($str) as $val)
    {
      $class = $this->ormClasses['informationObject'];
      $infobj = $class::getBySlug($val);

      if (null === $infobj)
      {
        $this->logError(sprintf(
          'Notice on row [%u/%u]: Ignored unknown description slug "%s"',
          $this->offset, $this->rowsTotal, $val)
        );

        continue;
      }

      $ids[] = $infobj->id;
    }

    return $ids;
  }

  protected function processMultiValueColumn(String $str)
  {
    if ('' === trim($str))
    {
      return [];
    }

    $values = explode($this->getOption('multiValueDelimiter'), $str);
    $values = array_map('trim', $values);

    // Remove empty strings from array
    $values = array_filter($values, function ($val) {
      return null !== $val && '' !== $val;
    });

    return $values;
  }

  protected function lookupTypeId($name, $culture)
  {
    // Allow typeId to be null
    if ('' === trim($name))
    {
      return;
    }

    $lookupTable = $this->getTypeIdLookupTable();
    $name = trim(strtolower($name));
    $culture = trim(strtolower($culture));

    if (null === $typeId = $lookupTable[$culture][$name])
    {
      $msg = <<<EOL
Couldn't find physical object type "$name" for culture "$culture"
EOL;
      throw new UnexpectedValueException($msg);
    }

    return $typeId;
  }

  protected function getTypeIdLookupTable()
  {
    if (null === $this->typeIdLookupTable)
    {
      $this->typeIdLookupTable = $this
        ->getPhysicalObjectTypeTaxonomy()
        ->getTermNameToIdLookupTable($this->getDbConnection());

      if (null === $this->typeIdLookupTable)
      {
        throw new sfException(
          'Couldn\'t load Physical object type terms from database');
      }
    }

    return $this->typeIdLookupTable;
  }
}
