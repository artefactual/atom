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
 * CSV empty row test. Test for rows which are:
 *  -  completely empty
 *  -  have CSV fields but all are entirely empty
 * 
 * @package    symfony
 * @subpackage task
 * @author     Steve Breker <sbreker@artefactual.com>
 */

class CsvEmptyRowTest extends CsvBaseTest
{
  protected $headerIsBlank = null;
  protected $blankRowSummary = [];

  const TITLE = 'CSV Empty Row Check';

  public function __construct()
  {
    parent::__construct();

    $this->setTitle(self::TITLE);
  }

  public function testRow(array $header, array $row)
  {
    parent::testRow($header, $row);

    // Test if header is blank
    if (!isset($this->headerIsBlank))
    {
      $this->headerIsBlank = strlen(trim(implode($header))) == 0;
    }
    
    // Test if row is blank. Record line numbers of blank rows.
    if (strlen(trim(implode($row))) == 0)
    {
      $this->blankRowSummary[] = $this->rowNumber;
    }
  }

  public function getTestResult()
  {
    if ($this->headerIsBlank)
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
      $this->addTestResult(self::TEST_RESULTS, sprintf("CSV Header is blank."));
    }

    if (0 < count($this->blankRowSummary))
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
      $this->addTestResult(self::TEST_RESULTS, sprintf("CSV blank row count: %s", count($this->blankRowSummary)));
      $this->addTestResult(self::TEST_DETAIL, sprintf("Blank row numbers: %s", implode(', ', $this->blankRowSummary)));
    }
    else
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_INFO);
      $this->addTestResult(self::TEST_RESULTS, sprintf("CSV does not have any blank rows."));
    }

    return parent::getTestResult();
  }
}
