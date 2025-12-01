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
 * CSV event date validity test. Checks if eventStartDate and eventEndDate
 * all contain a permissible date format.
 *
 * @author     Anvit Srivastav <asrivastav@artefactual.com>
 */
class CsvEventDateValidator extends CsvBaseValidator
{
    public const TITLE = 'Event Date Check';
    public const LIMIT_TO = ['QubitInformationObject'];

    protected $invalidEventDates = [];
    protected $rowsWithInvalidDates = 0;

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);
    }

    public function reset()
    {
        $this->invalidEventDates = [];
        $this->rowsWithInvalidDates = 0;

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        $invalidDateFound = false;
        if (!parent::testRow($header, $row)) {
            return;
        }

        $row = $this->combineRow($header, $row);

        if (false !== strpos($row['eventStartDate'], '|')) {
            foreach (explode($row['eventStartDate'], '|') as $date) {
                if (!$this->checkDate($date)) {
                    $this->invalidEventDates[] = $date;
                    $invalidDateFound = true;
                }
            }
        } elseif (!$this->checkDate($row['eventStartDate'])) {
            $this->invalidEventDates[] = $row['eventStartDate'];
            $invalidDateFound = true;
        }

        if (false !== strpos($row['eventEndDate'], '|')) {
            foreach (explode($row['eventEndDate'], '|') as $date) {
                if (!$this->checkDate($date)) {
                    $this->invalidEventDates[] = $date;
                    $invalidDateFound = true;
                }
            }
        } elseif (!$this->checkDate($row['eventEndDate'])) {
            $this->invalidEventDates[] = $row['eventEndDate'];
            $invalidDateFound = true;
        }

        if ($invalidDateFound) {
            ++$this->rowsWithInvalidDates;
            $this->appendToCsvRowList();
        }
    }

    public function getTestResult()
    {
        if (0 < $this->rowsWithInvalidDates) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Rows with invalid event date values: %s', $this->rowsWithInvalidDates));
        } else {
            $this->testData->addResult(sprintf("All ''eventStartDate' and 'eventEndDate' columns contain dates in a valid format."));
        }

        if (!empty($this->getCsvRowList())) {
            $this->testData->addDetail(sprintf('CSV row numbers where issues were found: %s', implode(', ', $this->getCsvRowList())));
            $this->testData->addDetail(sprintf('Listing invalid date values: %s', implode(', ', $this->invalidEventDates)));
        }

        return parent::getTestResult();
    }

    protected function checkDate(?string $eventDate)
    {
        // Blank entries are valid
        if (empty($eventDate)) {
            return true;
        }

        // Check for YYYY-MM-DD format for dates
        $date = trim($eventDate);
        if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $date)) {
            return true;
        }

        return false;
    }
}
