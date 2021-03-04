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
 * Check csv data
 *
 * @package    symfony
 * @subpackage task
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvImportValidator
{
  protected $context;
  protected $dbcon;
  protected $filename;
  protected $csvTests = null;
  protected $header;
  protected $rows = array();
  protected $showDisplayProgress = false;

  const UTF8_BOM = "\xEF\xBB\xBF";
  const UTF16_LITTLE_ENDIAN_BOM = "\xFF\xFE";
  const UTF16_BIG_ENDIAN_BOM = "\xFE\xFF";
  const UTF32_LITTLE_ENDIAN_BOM = "\xFF\xFE\x00\x00";
  const UTF32_BIG_ENDIAN_BOM = "\x00\x00\xFE\xFF";

  static $bomTypeMap = [
    'utf8Bom' => self::UTF8_BOM,
    'utf16LittleEndianBom' => self::UTF16_LITTLE_ENDIAN_BOM,
    'utf16BigEndianBom' => self::UTF16_BIG_ENDIAN_BOM,
    'utf32LittleEndianBom' => self::UTF32_LITTLE_ENDIAN_BOM,
    'utf32BigEndianBom' => self::UTF32_BIG_ENDIAN_BOM,
  ];

  // Default options
  protected $validatorOptions = [
    'importType' => 'QubitInformationObject',
    'verbose'    => false,
  ];

  protected $defaultCsvTypeMap = [
    'QubitInformationObject',
  ];

  public function __construct(sfContext $context = null,
    $dbcon = null,
    $options = array())
  {
    if (null === $context)
    {
      $context = new sfContext(ProjectConfiguration::getActive());
    }

    $this->setContext($context);
    $this->dbcon = $dbcon;
    $this->setOptions($options);

    $this->setCsvTests(
      [
        'fileEncoding'        => CsvFileEncodingTest::class,
        'columnCountTest'     => CsvColumnCountTest::class,
        'emptyRowTest'        => CsvEmptyRowTest::class,
        'sampleColumnValues'  => CsvSampleColumnsTest::class,
      ]
    );
  }

  private function handleByteOrderMark($fh)
  {
    foreach (self::$bomTypeMap as $key => $value)
    {
      if (false === $data = fread($fh, strlen($value)))
      {
        throw new sfException('Failed to read from CSV file in handleByteOrderMark.');
      }

      if (0 === strncmp($data, $value, strlen($value)))
      {
        return; // Just eat the BOM and move on from this file position
      }

      // No BOM, rewind the file handle position
      if (false === rewind($fh))
      {
        throw new sfException('Rewinding file position failed in handleByteOrderMark.');
      }
    }
  }

  public function loadCsvData($fh)
  {
    $this->handleByteOrderMark($fh);

    $this->header = fgetcsv($fh, 60000);

    if ($this->header === false)
    {
      throw new sfException('Could not read initial row. File could be empty.');
    }

    while ($item = fgetcsv($fh, 60000))
    {
      $this->rows[] = $item;
    }
  }

  protected function getLongestRow() : int
  {
    $rowsMaxCount = count(max($this->rows));
    $headerCount = count($this->header);

    if ($rowsMaxCount > $headerCount)
    {
      return $rowsMaxCount;
    }
    else
    {
      return count($this->header);
    }
  }

  public function validate()
  {
    if (false === $fh = fopen($this->filename, 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    $this->loadCsvData($fh);

    foreach ($this->csvTests as $test)
    {
      $test->setFilename($this->filename);
      $test->setColumnCount($this->getLongestRow());
    }

    foreach ($this->rows as $row)
    {
      if ($this->showDisplayProgress)
      {
        print $this->renderProgressDescription();
      }
      
      foreach ($this->csvTests as $test)
      {
        $test->testRow($this->header, $row);
      }
    }

    if ($this->showDisplayProgress)
    {
      print $this->renderProgressDescription(true);
    }
  }

  public function setShowDisplayProgress(bool $value)
  {
    $this->showDisplayProgress = $value;
  }

  public function getContext()
  {
    return $this->context;
  }

  public function setContext($context)
  {
    $this->context = $context;
  }  

  // Collect all the results from all CSV Validations.
  // Return array of results sorted by filename.
  public function getResults()
  {
    $results = array();

    foreach ($this->getCsvTests() as $test)
    {
      $results[$test->getFileName()][] = $test->getTestResult();
    }

    return $results;
  }

  public function setCsvTests(array $classes)
  {
    unset($this->csvTests);

    foreach($classes as $key => $class)
    {
      $this->csvTests[] = new $class();  
    }
  }

  public function getCsvTests()
  {
    return $this->csvTests;
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

  public function setImportType(string $value)
  {
    if (in_array($value, $this->defaultCsvTypeMap))
    {
      $this->validatorOptions['importType'] = $value;
    }
    else
    {
      throw new UnexpectedValueException(sprintf('Invalid option "%s".', $name));
    }
  }

  public function setVerbose(bool $value)
  {
    $this->validatorOptions['verbose'] = $value;
  }

  public function setOption(string $name, $value)
  {
    switch ($name)
    {
      case 'importType':
        $this->setImportType($value);

        break;

      case 'verbose':
        $this->setVerbose($value);

        break;

      default:
        throw new UnexpectedValueException(sprintf('Invalid option "%s".', $name));
    }
  }

  public function getOption(String $name)
  {
    if (array_key_exists($name, $this->validatorOptions))
    {
      return $this->validatorOptions[$name];
    }
    else
    {
      throw new UnexpectedValueException(sprintf('Invalid option "%s".', $name));
    }
  }

  public function getOptions()
  {
    return $this->validatorOptions;
  }

  public function getDbCon()
  {
    if (null === $this->dbcon)
    {
      $this->dbcon = Propel::getConnection();
    }

    return $this->dbcon;
  }

  public function setFilename(string $filenameString)
  {
    self::validateFileName($filenameString);

    $this->filename = $filenameString;
  }

  public function getRowCount()
  {
    return (count($this->rows));
  }

  public static function validateFilename($filename)
  {
    if (empty($filename))
    {
      throw new sfException('Please specify a valid filename.');
    }

    if (!file_exists($filename))
    {
      throw new sfException(sprintf('Can not find file %s', $filename));
    }

    if (!is_readable($filename))
    {
      throw new sfException(sprintf('Can not read %s', $filename));
    }

    return $filename;
  }

  public function renderProgressDescription(bool $complete = false)
  {
    $output = '.';

    if ($complete)
    {
      return "\nAnalysis complete.\n";
    }

    return $output;
  }
}