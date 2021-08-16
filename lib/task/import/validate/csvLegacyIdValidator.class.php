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
 * CSV legacyId test. Check if legacyId is blank.
 * Output error status and any rows where legacyId is not found.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvLegacyIdValidator extends CsvBaseValidator
{
    public const TITLE = 'LegacyId check';
    public const LIMIT_TO = ['QubitInformationObject'];

    // Persist across multiple CSVs.
    protected $legacyIdValues = [];
    // Reset after every CSV.
    protected $rowsWithoutLegacyId = [];
    protected $nonUniqueLegacyIdValues = [];
    protected $prevRowLegacyId = 0;
    protected $prevRowCulture = '';

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);

        $this->setRequiredColumns(['legacyId']);
    }

    public function reset()
    {
        $this->rowsWithoutLegacyId = [];
        $this->nonUniqueLegacyIdValues = [];
        $this->prevRowLegacyId = 0;
        $this->prevRowCulture = '';

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        if (!parent::testRow($header, $row)) {
            return;
        }

        $row = $this->combineRow($header, $row);

        if (empty($row['legacyId'])) {
            $this->rowsWithoutLegacyId[] = $this->rowNumber;

            return;
        }

        if ($this->columnPresent('culture')) {
            if (
                $row['legacyId'] === $this->prevRowLegacyId
                && $row['culture'] === $this->prevRowCulture
            ) {
                $this->duplicateTranslationRows[] = sprintf('legacyId: %s; culture: %s', $row['legacyId'], $row['culture']);
            }

            $this->prevRowLegacyId = $row['legacyId'];
            $this->prevRowCulture = $row['culture'];
        }

        if (in_array($row['legacyId'], $this->legacyIdValues)) {
            // This is a duplicate legacyId. Add to list of
            // non-unique legacyIds if not already present.
            if (!in_array($row['legacyId'], $this->nonUniqueLegacyIdValues)) {
                $this->nonUniqueLegacyIdValues[] = $row['legacyId'];
            }

            return;
        }

        $this->legacyIdValues[] = $row['legacyId'];
    }

    public function getTestResult()
    {
        if (!$this->columnPresent('legacyId')) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("'legacyId' column not present. Future CSV updates may not match these records."));

            return parent::getTestResult();
        }

        if ($this->columnDuplicated('legacyId')) {
            $this->appendDuplicatedColumnError('legacyId');

            return parent::getTestResult();
        }

        // Rows exist with non unique legacyId.
        if (0 < count($this->nonUniqueLegacyIdValues)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("Rows with non-unique 'legacyId' values: %s", count($this->nonUniqueLegacyIdValues)));
            $this->testData->addDetail(sprintf("Non-unique 'legacyId' values: %s", implode(', ', $this->nonUniqueLegacyIdValues)));
        } else {
            $this->testData->addResult(sprintf("'legacyId' values are all unique."));
        }

        // If this legacyid and culture matches prev row, this will
        // trigger CSV import translation row errors.
        if (!empty($this->duplicateTranslationRows)) {
            $this->testData->setStatusError();
            $this->testData->addResult('Consecutive CSV rows with matching legacyId and culture will trigger errors during CSV import.');
            foreach ($this->duplicateTranslationRows as $value) {
                $this->testData->addDetail(sprintf('Duplicate translation values for: %s', $value));
            }
        }

        // Rows exist with blank legacyId.
        if (!empty($this->rowsWithoutLegacyId)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("Rows with empty 'legacyId' column: %s", count($this->rowsWithoutLegacyId)));
            $this->testData->addDetail(sprintf("CSV row numbers missing 'legacyId': %s", implode(', ', $this->rowsWithoutLegacyId)));
        }

        if (!empty($this->rowsWithoutLegacyId) || 0 < count($this->nonUniqueLegacyIdValues)) {
            $this->testData->addResult(sprintf('Future CSV updates may not match these records.'));
        }

        return parent::getTestResult();
    }
}
