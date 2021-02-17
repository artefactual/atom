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
 * CSV Duplicate column name test. Throw error if any column names are repeated in the file.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvDuplicateColumnNameValidator extends CsvBaseValidator
{
    const TITLE = 'Duplicate Column Name Check';

    protected $columnFrequency = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->columnFrequency = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);
        $header = array_map('trim', $header);

        // Return if this check has already been completed and columnFrequency[] is populated.
        if (!empty($this->columnFrequency)) {
            return;
        }

        foreach ($header as $columnName) {
            if (!isset($this->columnFrequency[$columnName])) {
                $this->columnFrequency[$columnName] = 1;
            } else {
                ++$this->columnFrequency[$columnName];
            }
        }
    }

    public function getTestResult()
    {
        foreach ($this->columnFrequency as $columnName => $count) {
            if (1 < $count) {
                $this->testData->setStatusError();
                $this->testData->addResult(sprintf("Columns with name '%s': %s", $columnName, $count));
            }
        }

        // No duplicate header values when array_unique has only one element, and last element's value === 1.
        if (1 === count(array_unique($this->columnFrequency)) && 1 === end($this->columnFrequency)) {
            $this->testData->addResult('No duplicate column names found.');
        }

        return parent::getTestResult();
    }
}
