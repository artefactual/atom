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
  protected $multiValueDelimiter = '|';
  protected $offset              = 0;
  protected $options             = [];
  protected $ormClasses;
  protected $physicalObjectTypeTaxonomy;
  protected $progressFrequency   = 1;
  protected $reader;
  protected $rowsImported        = 0;
  protected $rowsTotal           = 0;
  protected $typeIdLookupTable;
  protected $updateSearchIndex   = false;


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
      case 'multiValueDelimiter':
        return $this->$name;

        break;

      case 'dbcon':
        return $this->getDbConnection();

        break;

      case 'physicalObjectTypeTaxonomy':
        return $this->getPhysicalObjectTypeTaxonomy();

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
      case 'multiValueDelimiter':
      case 'physicalObjectTypeTaxonomy':
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
    if (null === $options || 0 == count($options))
    {
      $this->options = [];

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

      case 'updateSearchIndex':
        $this->setUpdateSearchIndex($value);

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
  }

  public function setUpdateSearchIndex($value)
  {
    $this->updateSearchIndex = (bool) $value;
  }

  public function getUpdateSearchIndex()
  {
    return $this->updateSearchIndex;
  }

  public function setOffset(int $value)
  {
    $this->offset = $value;
  }

  public function getOffset()
  {
    return $this->offset;
  }

  public function setHeader(String $str)
  {
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
        throw new sfException(sprintf('Column name "%s" in header is invalid.',
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

  public function setProgressFrequency(int $freq)
  {
    // Note: $progressFrequency == 0 turns off logging
    $this->progressFrequency = ($freq > 0) ? $freq : 0;
  }

  public function getProgressFrequency()
  {
    return $this->progressFrequency;
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
        $this->logError(sprintf('Warning! Skipped row [%u/%u]: %s',
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

  public function getPhysicalObjectTypeTaxonomy()
  {
    if (null === $this->physicalObjectTypeTaxonomy)
    {
      // @codeCoverageIgnoreStart
      $this->physicalObjectTypeTaxonomy = QubitTaxonomy::getById(
        QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID,
        array('connection' => $this->getDbConnection())
      );
      // @codeCoverageIgnoreEnd
    }

    return $this->physicalObjectTypeTaxonomy;
  }

  public function savePhysicalobject($data)
  {
    // Setting the propel::defaultCulture is necessary for non-English rows
    // to prevent creating an empty i18n row with culture 'en'
    sfPropel::setDefaultCulture($data['culture']);

    $physobj = new $this->ormClasses['physicalObject'];
    $physobj->name     = $data['name'];
    $physobj->typeId   = $data['typeId'];
    $physobj->location = $data['location'];

    $physobj->save($this->dbcon);

    $this->createKeymapEntry($physobj, $data);

    // Write physical object to info object relations
    foreach ($data['informationObjectIds'] as $objectId)
    {
      $relation = new $this->ormClasses['relation'];
      $relation->objectId  = $objectId;
      $relation->subjectId = $physobj->id;
      $relation->typeId    = QubitTerm::HAS_PHYSICAL_OBJECT_ID;

      // Update search index?
      $relation->indexOnSave = $this->updateSearchIndex;

      $relation->save($this->dbcon);
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

  protected function log($msg)
  {
    // Just echo to STDOUT for now
    echo $msg.PHP_EOL;
  }

  protected function logError($msg)
  {
    fwrite($this->getErrorLogHandle(), $msg.PHP_EOL);
  }

  protected function progressUpdate($data)
  {
    $freq = $this->getProgressFrequency();

    if (1 == $freq)
    {
      $this->log(sprintf('Imported row [%u/%u]: name "%s"',
        $this->offset, $this->rowsTotal, $data['name']));
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
          'Warning row [%u/%u]: Couldn\'t find a description with slug "%s".',
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

    $values = explode($this->multiValueDelimiter, $str);
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
        ->getTermIdLookupTable($this->getDbConnection());

      if (null === $this->typeIdLookupTable)
      {
        throw new sfException(
          'Couldn\'t load Physical object type terms from database');
      }
    }

    return $this->typeIdLookupTable;
  }
}
