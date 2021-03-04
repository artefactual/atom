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
 * CSV UTF8 encoding validation.
 *
 * @package    symfony
 * @subpackage task
 * @author     Steve Breker <sbreker@artefactual.com>
 */

class CsvFileEncodingTest extends CsvBaseTest
{
  protected $utf8BomPresent = null;
  protected $utf8Compatible = true;

  const TITLE = 'UTF-8 File Encoding Check';

  public function __construct()
  {
    parent::__construct();

    $this->setTitle(self::TITLE);
  }

  public function testRow(array $header, array $row)
  {
    parent::testRow($header, $row);
    $row = $this->combineRow($header, $row);

    if (null === $this->utf8BomPresent)
    {
      CsvImportValidator::validateFileName($this->filename);
      $this->utf8BomPresent = $this->detectBom();
    }

    // If row is not UTF-8 compatible.
    if (!$this->isRowUtf8EncodingCompatible($row))
    {
      $this->utf8Compatible = false;

      // Add row that triggered this to the output.
      $this->addTestResult(self::TEST_DETAIL, implode(',', $row));
    }
  }

  protected function finalizeTestResults()
  {
    if ($this->utf8Compatible)
    {
      $this->addTestResult(self::TEST_RESULTS, 'File encoding is UTF-8 compatible.');
      $this->addTestResult(self::TEST_STATUS, self::RESULT_INFO);
    }
    else 
    {
      $this->addTestResult(self::TEST_RESULTS, 'File encoding does not appear to be UTF-8 compatible.');
      $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
    }

    if (null !== $this->utf8BomPresent && false !== $this->utf8BomPresent)
    {
      switch ($this->utf8BomPresent)
      {
        case 'utf8Bom':
          $this->addTestResult(self::TEST_RESULTS, 'This file includes a UTF-8 BOM.');
          $this->addTestResult(self::TEST_STATUS, self::RESULT_INFO);

          break;

        default:
          $this->addTestResult(self::TEST_RESULTS, 'This file includes a unicode BOM, but it is not UTF-8.');
          $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
      }
    }
  }

  public function getTestResult()
  {
    $this->finalizeTestResults();

    return parent::getTestResult();
  }

  public function detectBom()
  {
    if (false === $fh = fopen($this->filename, 'rb'))
    {
      throw new sfException('You must specify a valid filename');
    }

    foreach (CsvImportValidator::$bomTypeMap as $key => $value)
    {
      if (false === $data = fread($fh, strlen($value)))
      {
        throw new sfException('Failed to read from CSV file in csvFileEncodingTest.');
      }

      if (0 === strncmp($data, $value, strlen($value)))
      {
        // BOM detected. Return the type.
        return $key;
      }

      if (false === rewind($fh))
      {
        throw new sfException('Rewinding file position failed in handleByteOrderMark.');
      }
    }

    return false;
  }

  public function isRowUtf8EncodingCompatible($row)
  {
    // Test row contents for UTF-8 incompatible encodings.
    $result = mb_detect_encoding(implode("", $row), 'UTF-8', true);
    
    return $result;
  }
}
