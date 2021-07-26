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
    protected $rowsWithoutLegacyId = 0;
    protected $nonUniqueLegacyIdValues = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);

        $this->setRequiredColumns(['legacyId']);
    }

    public function reset()
    {
        $this->rowsWithoutLegacyId = 0;
        $this->nonUniqueLegacyIdValues = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        if (!parent::testRow($header, $row)) {
            return;
        }

        $row = $this->combineRow($header, $row);

        if (empty($row['legacyId'])) {
            ++$this->rowsWithoutLegacyId;
            $this->testData->addDetail(implode(',', $row));

            return;
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

        // Rows exist with non unique legacyId. Warn that this will complicate/break future CSV update matching.
        if (0 < count($this->nonUniqueLegacyIdValues)) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf("Rows with non-unique 'legacyId' values: %s", count($this->nonUniqueLegacyIdValues)));
            $this->testData->addDetail(sprintf("Non-unique 'legacyId' values: %s", implode(', ', $this->nonUniqueLegacyIdValues)));
        } else {
            $this->testData->addResult(sprintf("'legacyId' values are all unique."));
        }

        // Rows exist with both parentId and qubitParentSlug populated. Warn that qubitParentSlug will override.
        if (0 < $this->rowsWithoutLegacyId) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("Rows with empty 'legacyId' column: %s", $this->rowsWithoutLegacyId));
        }

        if (0 < $this->rowsWithoutLegacyId || 0 < count($this->nonUniqueLegacyIdValues)) {
            $this->testData->addResult(sprintf('Future CSV updates may not match these records.'));
        }

        return parent::getTestResult();
    }
}
