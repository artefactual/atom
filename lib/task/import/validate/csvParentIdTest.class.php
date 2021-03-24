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
  // Persist across multiple CSVs.
  protected $legacyIdList = [];

  // Reset after every CSV.
  protected $orphanRowsFound = false;
  protected $parentIdColumnPresent = null;
  protected $qubitParentSlugColumnPresent = null;
  protected $unmatchedCount = 0;
  protected $rowsWithParentId = 0;
  protected $rowsWithQubitParentSlug = 0;
  protected $rowsWithParentIdQubitParentSlug = 0;
  protected $rowsWithoutParentIdQubitParentSlug = 0;

  const TITLE = 'ParentId and qubitParentSlug column check';

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
    $this->qubitParentSlugColumnPresent = null;
    $this->unmatchedCount = 0;
    $this->rowsWithParentId = 0;
    $this->rowsWithQubitParentSlug = 0;
    $this->rowsWithParentIdQubitParentSlug = 0;
    $this->rowsWithoutParentIdQubitParentSlug = 0;
    
    parent::reset();
  }

  public function testRow(array $header, array $row)
  {
    parent::testRow($header, $row);
    $row = $this->combineRow($header, $row);

    // Is parentId column present?
    if (!isset($this->parentIdColumnPresent))
    {
      $this->parentIdColumnPresent = (array_key_exists('parentId', $row) ? true : false);
    }

    // Is qubitParentSlug column present?
    if (!isset($this->qubitParentSlugColumnPresent))
    {
      $this->qubitParentSlugColumnPresent = (array_key_exists('qubitParentSlug', $row) ? true : false);
    }

    // Is legacyId column present?
    if (!isset($this->legacyIdColumnPresent))
    {
      $this->legacyIdColumnPresent = (array_key_exists('legacyId', $row) ? true : false);
    }

    // When both are present, qubitParentSlug will override parentId.
    if ($this->qubitParentSlugColumnPresent && !empty($row['qubitParentSlug']))
    {
      $this->rowsWithQubitParentSlug++;

      if (!$this->canFindBySlug($row['qubitParentSlug'], $this->options['className']))
      {
        $this->orphanRowsFound = true;
        $this->unmatchedCount++;

        // Add row that triggered this to the output.
        $this->addTestResult(self::TEST_DETAIL, implode(',', $row));
      }
    }
    else if ($this->parentIdColumnPresent && !empty($row['parentId']))
    {
      $this->rowsWithParentId++;

      if (!$this->canFindByParentId($row['parentId'], $this->options['source']))
      {
        $this->orphanRowsFound = true;
        $this->unmatchedCount++;

        // Add row that triggered this to the output.
        $this->addTestResult(self::TEST_DETAIL, implode(',', $row));
      }
    }

    if ($this->parentIdColumnPresent && empty($row['parentId']) &&
      $this->qubitParentSlugColumnPresent && empty($row['qubitParentSlug']))
    {
      $this->rowsWithoutParentIdQubitParentSlug++;
    }

    if ($this->parentIdColumnPresent && !empty($row['parentId']) &&
      $this->qubitParentSlugColumnPresent && !empty($row['qubitParentSlug']))
    {
      $this->rowsWithParentIdQubitParentSlug++;
    }

    if ($this->legacyIdColumnPresent)
    {
      $this->legacyIdList[] = $row['legacyId'];
    }
  }

  protected function canFindBySlug(string $parentSlug, string $className)
  {
    if ('' === trim($parentSlug))
    {
      return false;
    }

    // Check DB for slug
    //$parentId = QubitFlatfileImport::getIdCorrespondingToSlug($options['default-parent-slug']);
    $object = QubitObject::getBySlug($parentSlug);
    
    if (isset($object) && $object->class_name == $className)
    {
      return true;
    }

    return false;
  }

  // Check legacyId history from this set of import files. If not found, check keymap table.
  protected function canFindByParentId(string $parentId, string $source = '', string $objectType = 'information_object')
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
    if (false == $this->parentIdColumnPresent && false == $this->qubitParentSlugColumnPresent)
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_WARN);
      $this->addTestResult(self::TEST_RESULTS, sprintf("'parentId' and 'qubitParentSlugColumnPresent' columns not present. CSV contents will be imported as top level records."));
    }
    else
    {
      $this->addTestResult(self::TEST_STATUS, self::RESULT_INFO);

      if ($this->parentIdColumnPresent)
      {
        $this->addTestResult(self::TEST_RESULTS, sprintf("Rows with parentId populated: %s.", $this->rowsWithParentId));
      }
      if ($this->qubitParentSlugColumnPresent)
      {
        $this->addTestResult(self::TEST_RESULTS, sprintf("Rows with qubitParentSlug populated: %s.", $this->rowsWithQubitParentSlug));  
      }

      // Rows exist with both parentId and qubitParentSlug populated. Warn that qubitParentSlug will override.
      if (0 < $this->rowsWithParentIdQubitParentSlug)
      {
        $this->addTestResult(self::TEST_STATUS, self::RESULT_WARN);
        $this->addTestResult(self::TEST_RESULTS, sprintf("Rows with both 'parentId' and 'qubitParentSlug' populated: %s.", $this->rowsWithParentIdQubitParentSlug));
        $this->addTestResult(self::TEST_RESULTS, sprintf("Column 'qubitParentSlug' will override 'parentId' if both are populated."));
      }

      // If parentId is present, then it would be an error if legacyId was not present.
      if (false == $this->legacyIdColumnPresent && 0 < $this->rowsWithParentId)
      {
        $this->addTestResult(self::TEST_STATUS, self::RESULT_ERROR);
        $this->addTestResult(self::TEST_RESULTS, sprintf("'legacyId' column not found. Unable to match parentId to CSV rows."));
      }

      // If unable to find some parents in the DB, and source was not specified, display a message as this is a possible cause.
      if (empty($this->options['source']) && $this->parentIdColumnPresent && $this->orphanRowsFound)
      {
        $this->addTestResult(self::TEST_STATUS, self::RESULT_WARN);
        $this->addTestResult(self::TEST_RESULTS, sprintf("'source' option not specified. Unable to check parentId values against AtoM's database."));
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
