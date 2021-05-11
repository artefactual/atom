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
 * CSV validation test base class. All test classes in lib/task/import/validate
 * extends this class. Each test class should override:
 * 1) __construct() - set title and any other test initialization
 * 2) testRow() - Called once per row in the CSV. Test row and record results here.
 * 3) reset() - Reset variables as needed before processing next CSV file.
 * 4) getTestResults() - finalize test results and set details in $testData (CsvValidatorResult).
 *
 * See class CsvSampleValuesValidator for an example.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
abstract class CsvBaseValidator
{
    const HEADER_PLACEHOLDER = 'EXTRA_COLUMN';
    const LIMIT_TO = [];

    protected $filename = '';
    protected $displayFilename = '';
    protected $columnCount = 0;
    protected $rowNumber = 1;
    protected $title = '';
    protected $options = [];
    protected $ormClasses = [];
    protected $testData;

    public function __construct(?array $options = null)
    {
        if (isset($options)) {
            $this->setOptions($options);
        }

        $this->testData = new CsvValidatorResult($this->title, $this->filename, $this->displayFilename, $this->getClassName());
    }

    public function testRow(array $header, array $row)
    {
        ++$this->rowNumber;
    }

    public function reset()
    {
        $this->filename = '';
        $this->displayFilename = '';
        $this->testData = new CsvValidatorResult($this->title, $this->filename, $this->displayFilename, $this->getClassName());
    }

    public function setOrmClasses(array $classes)
    {
        $this->ormClasses = $classes;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
        $this->testData->setFilename($filename);
    }

    public function setDisplayFilename(string $displayFilename)
    {
        $this->displayFilename = $displayFilename;
        $this->testData->setDisplayFilename($displayFilename);
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getDisplayFilename()
    {
        return $this->displayFilename;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setColumnCount(int $count)
    {
        $this->columnCount = $count;
    }

    public function getColumnCount(): int
    {
        return $this->columnCount;
    }

    public function getTestResult()
    {
        return $this->testData;
    }

    public function getClassName()
    {
        return get_class($this);
    }

    protected function combineRow(array $header, array $row)
    {
        // Enforce header has $columnCount elements. Add elements if necessary.
        for ($i = count($header); $i < $this->columnCount; ++$i) {
            $header[] = sprintf('%s-%d', self::HEADER_PLACEHOLDER, $i);
        }

        // Enforce row has $columnCount elements.
        for ($i = count($row); $i < $this->columnCount; ++$i) {
            $row[] = '';
        }

        // return array_combined row, trim each element.
        return array_combine(array_map('trim', $header), array_map('trim', $row));
    }
}
