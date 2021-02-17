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
 * CSV column name test. Validate column names against list of valid AtoM import fields.
 * Output warning if unknown column names found. Output list of unknown column names.
 * Validate against files in 'lib/flatfile/config'.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvColumnNameValidator extends CsvBaseValidator
{
    const TITLE = 'Column Name Validation';

    // Do not reset in between multiple CSVs.
    protected $validColumnNames = [];
    protected $validColumnNamesLowercase = [];
    // Reset between files.
    protected $unknownColumnNames = [];
    protected $caseIssuesColumnNameMap = [];
    protected $trimIssuesColumnNames = [];
    protected $complete = false;

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);

        $this->loadObjectColumnNames($this->options['className']);
    }

    public function reset()
    {
        $this->complete = false;
        $this->unknownColumnNames = [];
        $this->caseIssuesColumnNameMap = [];
        $this->trimIssuesColumnNames = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);

        // Only do this check once per file.
        if ($this->complete) {
            return;
        }

        foreach ($header as $columnName) {
            $this->testColumnName($columnName);
        }

        $this->complete = true;
    }

    public function getTestResult()
    {
        $this->testData->addResult(sprintf('Number of unrecognized column names found in CSV: %s', count($this->unknownColumnNames)));

        if (0 < count($this->unknownColumnNames)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult('Unrecognized columns will be ignored by AtoM when the CSV is imported.');
            foreach ($this->unknownColumnNames as $unknownColumnName) {
                $this->testData->addDetail(sprintf('Unrecognized column: %s', $unknownColumnName));
            }
        }

        if (0 < count($this->trimIssuesColumnNames)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf('Number of column names with leading or trailing whitespace characters: %s', count($this->trimIssuesColumnNames)));
            $this->testData->addDetail(sprintf('Column names with leading or trailing whitespace: %s', implode(',', $this->trimIssuesColumnNames)));
        }

        if (0 < count($this->caseIssuesColumnNameMap)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf('Number of unrecognized columns that may be case related: %s', count($this->caseIssuesColumnNameMap)));
            foreach ($this->caseIssuesColumnNameMap as $key => $value) {
                $this->testData->addDetail(sprintf('Possible match for %s: %s', $key, $value));
            }
        }

        return parent::getTestResult();
    }

    protected function testColumnName(string $columnName)
    {
        // If $columnName is not in validColumnNames.
        if (!in_array($columnName, $this->validColumnNames)) {
            // Test for leading or trailing whitespace.
            if (in_array(trim($columnName), $this->validColumnNames)) {
                $this->trimIssuesColumnNames[$columnName] = trim($columnName);
            }
            // Test if a match is found using lowercase trimmed matching.
            elseif (false !== $key = array_search(strtolower(trim($columnName)), $this->validColumnNamesLowercase)) {
                // Map unknown column name to possible match.
                $this->caseIssuesColumnNameMap[trim($columnName)] = $this->validColumnNames[$key];
            }

            // Add to unknown column name list.
            $this->unknownColumnNames[$columnName] = $columnName;
        }
    }

    // Load the column name config from lib/flatfile/config.
    protected function loadObjectColumnNames($resourceClass)
    {
        $resourceTypeBaseConfigFile = $resourceClass.'.yml';

        try {
            $config = QubitFlatfileExport::loadResourceConfigFile($resourceTypeBaseConfigFile, 'base');
        } catch (Exception $e) {
            throw new sfException(sprintf('Column name validation failed - unable to read yml file: %s.', $resourceTypeBaseConfigFile));
        }

        $this->validColumnNames = $config['columnNames'];
        $standardColumns = isset($config['direct']) ? $config['direct'] : [];
        $columnMap = isset($config['map']) ? $config['map'] : [];
        $propertyMap = isset($config['property']) ? $config['property'] : [];

        // If column names/order aren't specified, derive them
        if (null === $this->validColumnNames) {
            // Add standard columns
            $this->validColumnNames = (null !== $standardColumns) ? $standardColumns : [];

            // Add from column map
            if (null !== $columnMap) {
                $this->validColumnNames = array_merge($this->validColumnNames, array_values($columnMap));
            }

            // Add from property map
            if (null !== $propertyMap) {
                $this->validColumnNames = array_merge($this->validColumnNames, array_values($propertyMap));
            }
        }

        $this->validColumnNamesLowercase = array_map('strtolower', $this->validColumnNames);
    }
}
