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
 * CSV Sample Values test. Outputs column names and a sample value from first
 * populated row found. Only populated columns are included.
 * 
 * @package    symfony
 * @subpackage task
 * @author     Steve Breker <sbreker@artefactual.com>
 */

class CsvSampleColumnsTest extends CsvBaseTest
{
  protected $values = [];

  const TITLE = 'Sample Values';

  public function __construct()
  {
    parent::__construct();

    $this->setTitle(self::TITLE);
  }

  public function testRow(array $header, array $row)
  {
    $row = parent::testRow($header, $row);

    foreach ($row as $columnName => $value)
    {
      // Create sample values array.
      if (!array_key_exists($columnName, $this->values) && !empty($value))
      {
        $this->values[$columnName] = $value;
      }
    }
  }

  public function getTestResult()
  {
    $this->addTestResult(self::TEST_STATUS, self::RESULT_INFO);

    foreach ($this->values as $columnName => $sampleValue)
    {
      $this->addTestResult(self::TEST_RESULTS, sprintf("%s:  %s", $columnName, $sampleValue));
    }

    return parent::getTestResult();
  }
}
