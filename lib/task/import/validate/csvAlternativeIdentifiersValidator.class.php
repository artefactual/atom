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
 * CSV alternativeIdentifiers test. Check that the 'alternativeIdentifiers' and
 * 'alternativeIdentifierLabels' columns have the same number of values.
 *
 * CSV import throws an exception when a row has values in both columns but the
 * number of piped elements differs. Rows with values in only one of the two
 * columns are silently skipped during import.
 *
 * @author     Daniel Lovegrove <d.lovegrove11@gmail.com>
 */
class CsvAlternativeIdentifiersValidator extends CsvBaseValidator
{
    public const TITLE = 'Alternative identifier count test';
    public const LIMIT_TO = ['QubitInformationObject'];

    protected $columnsFound = [];
    protected $columnsChecked = false;
    protected $mismatchedRows = [];
    protected $rowsWithValuesInOneColumnOnly = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->columnsFound = [];
        $this->columnsChecked = false;
        $this->mismatchedRows = [];
        $this->rowsWithValuesInOneColumnOnly = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);
        $row = $this->combineRow($header, $row);

        // Check which columns are present on first row read.
        if (!$this->columnsChecked) {
            foreach (['alternativeIdentifiers', 'alternativeIdentifierLabels'] as $columnName) {
                if ($this->columnPresent($columnName)) {
                    $this->columnsFound[] = $columnName;
                }
            }

            $this->columnsChecked = true;
        }

        if (2 !== count($this->columnsFound)) {
            return;
        }

        $valueCounts = [];
        foreach ($this->columnsFound as $columnName) {
            if ('' === $row[$columnName]) {
                $valueCounts[$columnName] = 0;
            } else {
                $valueCounts[$columnName] = count(explode('|', $row[$columnName]));
            }
        }

        // Both empty: OK
        if (0 === $valueCounts['alternativeIdentifiers'] + $valueCounts['alternativeIdentifierLabels']) {
            return;
        }

        // Import silently skips rows where only one column has values
        if (0 === $valueCounts['alternativeIdentifiers'] || 0 === $valueCounts['alternativeIdentifierLabels']) {
            $this->rowsWithValuesInOneColumnOnly[] = $this->rowNumber;

            return;
        }

        // Import throws an exception when both columns have values but the counts differ
        if ($valueCounts['alternativeIdentifiers'] !== $valueCounts['alternativeIdentifierLabels']) {
            $this->mismatchedRows[] = $this->rowNumber;
        }
    }

    public function getTestResult()
    {
        if (empty($this->columnsFound)) {
            $this->testData->addResult('No alternativeIdentifier columns to check.');
        } else {
            $this->testData->addResult(sprintf('Checking columns: %s', implode(',', $this->columnsFound)));
        }

        if (0 < count($this->mismatchedRows)) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf("Rows where 'alternativeIdentifiers' and 'alternativeIdentifierLabels' value counts do not match: %s", count($this->mismatchedRows)));
            $this->testData->addDetail(sprintf('CSV row numbers where value counts do not match: %s', implode(', ', $this->mismatchedRows)));
            $this->testData->addDetail("CSV import will fail with error 'Number of alternative ids does not match number of alt id labels' on these rows.");
        }

        if (0 < count($this->rowsWithValuesInOneColumnOnly)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("Rows where only one of 'alternativeIdentifiers' and 'alternativeIdentifierLabels' has values: %s", count($this->rowsWithValuesInOneColumnOnly)));
            $this->testData->addDetail(sprintf('CSV row numbers where values in only one column were found: %s', implode(', ', $this->rowsWithValuesInOneColumnOnly)));
            $this->testData->addDetail('Alternative identifiers in these rows will not be imported.');
        }

        return parent::getTestResult();
    }
}
