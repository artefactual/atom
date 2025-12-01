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
 * CSV language and script validation. Tests for valid languages and
 * scripts.
 *
 * @author     Anvit Srivastav <asrivastav@artefactual.com>
 */
class CsvLanguageScriptValidator extends CsvBaseValidator
{
    public const TITLE = 'Language and Script Check';
    public const LIMIT_TO = ['QubitInformationObject'];

    // Fields to check
    protected $fields = [
        'language',
        'script',
        'languageOfDescription',
        'scriptOfDescription',
    ];

    protected $invalidValues = [];
    protected $rowsWithInvalidValues = 0;

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->invalidValues = [];
        $this->rowsWithInvalidValues = 0;

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        $invalidValueFound = false;
        parent::testRow($header, $row);
        $row = $this->combineRow($header, $row);

        foreach ($this->fields as $field) {
            if (!$row[$field]) {
                continue;
            }

            // check if these fields contains a space
            if (false !== strpos(trim($row[$field]), ' ')) {
                $this->invalidValues[] = $row[$field];
                $invalidValueFound = true;
            }
        }

        if ($invalidValueFound) {
            ++$this->rowsWithInvalidValues;
            $this->appendToCsvRowList();
        }
    }

    public function getTestResult()
    {
        if (0 < $this->rowsWithInvalidValues) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Rows with invalid language/script values: %s', $this->rowsWithInvalidValues));
        } else {
            $this->testData->addResult('All language and script columns contain valid characters.');
        }

        if (!empty($this->getCsvRowList())) {
            $this->testData->addDetail(sprintf('CSV row numbers where issues were found: %s', implode(', ', $this->getCsvRowList())));
            $this->testData->addDetail(sprintf('Listing invalid language/script values: %s', implode(', ', $this->invalidValues)));
        }

        return parent::getTestResult();
    }
}
