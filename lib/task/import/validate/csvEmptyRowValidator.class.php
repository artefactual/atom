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
 * CSV empty row test. Test for rows which are:
 *  -  completely empty
 *  -  have CSV fields but all are entirely empty.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvEmptyRowValidator extends CsvBaseValidator
{
    const TITLE = 'CSV Empty Row Check';

    protected $headerIsBlank;
    protected $blankRowSummary = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->headerIsBlank = null;
        $this->blankRowSummary = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);

        // Test if header is blank
        if (!isset($this->headerIsBlank)) {
            $this->headerIsBlank = 0 === strlen(trim(implode($header)));
        }

        // Test if row is blank. Record line numbers of blank rows.
        if (0 === strlen(trim(implode($row)))) {
            $this->blankRowSummary[] = $this->rowNumber;
        }
    }

    public function getTestResult()
    {
        if ($this->headerIsBlank) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('CSV Header is blank.'));
        }

        if (0 < count($this->blankRowSummary)) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('CSV blank row count: %s', count($this->blankRowSummary)));
            $this->testData->addDetail(sprintf('Blank row numbers: %s', implode(', ', $this->blankRowSummary)));
        } else {
            $this->testData->addResult(sprintf('CSV does not have any blank rows.'));
        }

        return parent::getTestResult();
    }
}
