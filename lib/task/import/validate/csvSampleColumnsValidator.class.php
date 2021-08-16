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
 * CSV Sample Values test. Output column names and a sample value from first
 * populated row found. Include list containing unpopulated columns.
 *
 * Output error if duplicate column names detected.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvSampleValuesValidator extends CsvBaseValidator
{
    public const TITLE = 'Sample Values';

    protected $values = [];
    protected $duplicatedColumnNames;
    protected $emptyColumnNames;

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->values = [];
        $this->duplicatedColumnNames = null;
        $this->emptyColumnNames = null;

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);
        $row = $this->combineRow($header, $row);

        if (!isset($this->emptyColumnNames)) {
            $this->emptyColumnNames = $row;
        }

        // Check for dupe column names.
        if (!isset($this->duplicatedColumnNames)) {
            $this->duplicatedColumnNames = [];

            foreach ($header as $value) {
                if ($this->columnDuplicated($value)) {
                    $this->duplicatedColumnNames[$value] = $value;
                }
            }
        }

        // Create sample values array.
        foreach ($row as $columnName => $value) {
            if (!isset($this->values[$columnName]) && !empty($value)) {
                $this->values[$columnName] = $value;
                unset($this->emptyColumnNames[$columnName]);
            }
        }
    }

    public function getTestResult()
    {
        if (isset($this->emptyColumnNames) && !empty($this->emptyColumnNames)) {
            $this->testData->addResult(sprintf('Empty columns detected: %s', implode(',', array_keys($this->emptyColumnNames))));
            $this->testData->addResult('');
        }

        if (isset($this->duplicatedColumnNames) && !empty($this->duplicatedColumnNames)) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Duplicate column names detected: %s', implode(',', $this->duplicatedColumnNames)));
            $this->testData->addResult('');
        }

        foreach ($this->values as $columnName => $sampleValue) {
            $this->testData->addResult(sprintf('%s:  %s', $columnName, $sampleValue));
        }

        return parent::getTestResult();
    }
}
