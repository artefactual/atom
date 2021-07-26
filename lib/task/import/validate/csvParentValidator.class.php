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
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvParentValidator extends CsvBaseValidator
{
    public const TITLE = 'Parent check';
    public const LIMIT_TO = ['QubitInformationObject'];

    // Persist across multiple CSVs.
    protected $legacyIdList = [];
    // Reset after every CSV.
    protected $orphanRowsFound = false;
    protected $unmatchedCount = 0;
    protected $rowsWithParentId = 0;
    protected $rowsWithQubitParentSlug = 0;
    protected $rowsWithParentIdQubitParentSlug = 0;
    protected $rowsWithoutParentIdQubitParentSlug = 0;

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->orphanRowsFound = false;
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

        // When both are present, qubitParentSlug will override parentId.
        if ($this->columnPresent('qubitParentSlug') && !empty($row['qubitParentSlug'])) {
            ++$this->rowsWithQubitParentSlug;

            if (!$this->canFindBySlug($row['qubitParentSlug'], $this->options['className'])) {
                $this->orphanRowsFound = true;
                ++$this->unmatchedCount;

                // Add row that triggered this to the output.
                $this->testData->addDetail(implode(',', $row));
            }
        } elseif ($this->columnPresent('parentId') && !empty($row['parentId'])) {
            ++$this->rowsWithParentId;

            if (!$this->canFindByParentId($row['parentId'], $this->options['source'])) {
                $this->orphanRowsFound = true;
                ++$this->unmatchedCount;

                // Add row that triggered this to the output.
                $this->testData->addDetail(implode(',', $row));
            }
        }

        if (
            $this->columnDuplicated('parentId')
            || $this->columnDuplicated('qubitParentSlug')
            || $this->columnDuplicated('legacyId')
        ) {
            return;
        }

        if (
            $this->columnPresent('parentId')
            && empty($row['parentId'])
            && $this->columnPresent('qubitParentSlug')
            && empty($row['qubitParentSlug'])
        ) {
            ++$this->rowsWithoutParentIdQubitParentSlug;
        }

        if (
            $this->columnPresent('parentId')
            && !empty($row['parentId'])
            && $this->columnPresent('qubitParentSlug')
            && !empty($row['qubitParentSlug'])
        ) {
            ++$this->rowsWithParentIdQubitParentSlug;
        }

        if ($this->columnPresent('legacyId')) {
            $this->legacyIdList[] = $row['legacyId'];
        }
    }

    public function getTestResult()
    {
        if (!$this->columnPresent('parentId') && !$this->columnPresent('qubitParentSlug')) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(
                sprintf("'parentId' and 'qubitParentSlug' columns not present. CSV contents will be imported as top level records.")
            );

            return parent::getTestResult();
        }

        if ($this->columnDuplicated('parentId')) {
            $this->appendDuplicatedColumnError('parentId');

            return parent::getTestResult();
        }

        if ($this->columnDuplicated('qubitParentSlug')) {
            $this->appendDuplicatedColumnError('qubitParentSlug');

            return parent::getTestResult();
        }

        if ($this->columnDuplicated('legacyId')) {
            $this->appendDuplicatedColumnError('legacyId');

            return parent::getTestResult();
        }

        if ($this->columnPresent('parentId')) {
            $this->testData->addResult(sprintf('Rows with parentId populated: %s', $this->rowsWithParentId));
        }

        if ($this->columnPresent('qubitParentSlug')) {
            $this->testData->addResult(sprintf('Rows with qubitParentSlug populated: %s', $this->rowsWithQubitParentSlug));
        }

        // Rows exist with both parentId and qubitParentSlug populated. Warn that qubitParentSlug will override.
        if (0 < $this->rowsWithParentIdQubitParentSlug) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(
                sprintf("Rows with both 'parentId' and 'qubitParentSlug' populated: %s", $this->rowsWithParentIdQubitParentSlug)
            );
            $this->testData->addResult(
                sprintf("Column 'qubitParentSlug' will override 'parentId' if both are populated.")
            );
        }

        // If parentId is present, then it would be an error if legacyId was not present.
        if (!$this->columnPresent('legacyId') && 0 < $this->rowsWithParentId) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf("'legacyId' column not found. Unable to verify parentId values."));
        }

        // If unable to find a parentId in the DB, and source was not specified, display a message as this is a possible cause.
        if (
            empty($this->options['source'])
            && $this->columnPresent('parentId')
            && $this->orphanRowsFound
        ) {
            $this->testData->addResult(
                sprintf('Verifying parentId values against legacyId values in this file.')
            );
        }

        if (
            !empty($this->options['source'])
            && $this->columnPresent('parentId')
            && $this->orphanRowsFound
        ) {
            $this->testData->addResult(
                sprintf('Verifying parentId values against legacyId values in this file, and AtoM database.')
            );
        }

        if ($this->orphanRowsFound) {
            $this->testData->setStatusError();
            $this->testData->addResult(
                sprintf(
                    'Number of parentID values found for which there is no matching legacyID (will import as top level records): %s',
                    $this->unmatchedCount
                )
            );
        }

        return parent::getTestResult();
    }

    protected function canFindBySlug(string $parentSlug, string $className)
    {
        if ('' === trim($parentSlug)) {
            return false;
        }

        // Check DB for slug.
        $object = $this->ormClasses['QubitObject']::getBySlug($parentSlug);

        if (isset($object) && $object->className === $className) {
            return true;
        }

        return false;
    }

    // Check legacyId history from this set of import files. If not found, check keymap table.
    protected function canFindByParentId(string $parentId, string $source = '', string $objectType = 'information_object')
    {
        if ('' === trim($parentId)) {
            return false;
        }

        // Check legacyid list first.
        if (in_array($parentId, $this->legacyIdList)) {
            return true;
        }

        // If not found, check keymap table in database.
        if (
            !empty($parentId)
            && !empty($source)
            && !empty($objectType)
        ) {
            $mapEntry = $this->ormClasses['QubitFlatfileImport']::fetchKeymapEntryBySourceAndTargetName(
                $parentId,
                $source,
                $objectType
            );
        }

        if ($mapEntry) {
            return true;
        }

        return false;
    }
}
