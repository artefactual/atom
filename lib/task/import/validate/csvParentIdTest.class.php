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
 * CSV parentId test. Check if parentId matches within file, or keymap table.
 * Output error status and any rows where parentId is not found.
 * 
 * @package    symfony
 * @subpackage task
 * @author     Steve Breker <sbreker@artefactual.com>
 */

class CsvParentIdTest extends CsvBaseTest
{
  protected $legacyIdList = [];
  protected $orphanRowsFound = false;
  protected $parentIdColumnPresent = null;
  protected $unmatchedCount = 0;
  protected $rowsWithParentId = 0;

  const TITLE = 'ParentId value check';

  public function __construct()
  {
    parent::__construct();

    $this->setTitle(self::TITLE);
    $this->reset();
  }

  public function reset()
  {
    $this->orphanRowsFound = false;
    $this->parentIdColumnPresent = null;
    $this->unmatchedCount = 0;
    $this->rowsWithParentId = 0;
    
    parent::reset();
  }

  public function testRow(array $header, array $row)
  {
    parent::testRow($header, $row);
    $row = $this->combineRow($header, $row);

    // is parentid col in file?
    if (!isset($this->parentIdColumnPresent))
    {
      $this->parentIdColumnPresent = (array_key_exists('parentId', $row) ? true : false);
    }

    // is legacyid col in file?
    if (!isset($this->legacyIdColumnPresent))
    {
      $this->legacyIdColumnPresent = (array_key_exists('legacyId', $row) ? true : false);
    }

    if ($this->parentIdColumnPresent && isset($row['parentId']) && !empty($row['parentId']))
    {
      $this->rowsWithParentId++;

      if (!$this->canFindParent($row['parentId'], $this->options['source']))
      {
        $this->orphanRowsFound = true;
        $this->unmatchedCount++;

        // Add row that triggered this to the output.
        $this->addTestResult(self::TEST_DETAIL, implode(',', $row));
      }
    }

    if ($this->legacyIdColumnPresent)
    {
      $this->legacyIdList[] = $row['legacyId'];
    }
  }

  // Check legacyId history from this set of import files. If not found, check keymap table.
  protected function canFindParent(string $parentId, string $source = '', string $objectType = 'information_object')
  {
    if ('' === trim($parentId))
    {
      return false;
    }

    // Check legacyid list first.
    if (in_array($parentId, $this->legacyIdList))
    {
      return true;
    }

    // If not found, check keymap table in database.
    if (!empty($parentId) && !empty($source) && !empty($objectType))
    {
      $mapEntry = $this->ormClasses['QubitFlatfileImport']::fetchKeymapEntryBySourceAndTargetName(
        $parentId,
        $source,
        $objectType
      );
    }

    if ($mapEntry)
    {
      return true;
    }

    return false;
  }

  public function getTestResult()
  {
    if ($this->parentIdColumnPresent == false)
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_WARN);
      $this->addTestResult(self::TEST_RESULTS, sprintf("'parentId' column not found. CSV contents will be imported as top level records."));
    }
    else
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_INFO);
      $this->addTestResult(self::TEST_RESULTS, sprintf("Rows with parentId populated: %s.", $this->rowsWithParentId));

      // If parentId is present, then it would be an error if legacyId was not present.
      if ($this->legacyIdColumnPresent == false && 0 < $this->rowsWithParentId)
      {
        $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
        $this->addTestResult(self::TEST_RESULTS, sprintf("'legacyId' column not found. Unable to match parentId to CSV rows."));
      }

      if (empty($this->options['source']) && $this->orphanRowsFound)
      {
        $this->addTestResult(self::TEST_STATUS, self::RESULT_WARN);
        $this->addTestResult(self::TEST_RESULTS, sprintf("Not checking AtoM's keymap table for previously imported parent records because 'source' option not specified."));
      }
    }

    if ($this->orphanRowsFound)
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
      $this->addTestResult(self::TEST_RESULTS, sprintf("Unable to find parents for %s rows. These rows will be imported as top level records.", $this->unmatchedCount));
    }

    return parent::getTestResult();
  }
}
