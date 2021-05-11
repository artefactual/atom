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
 * CSV validation result class.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvValidatorResult
{
    // Integer type to allow comparison of severity values.
    const RESULT_INFO = 0;
    const RESULT_WARN = 1;
    const RESULT_ERROR = 2;

    const TEST_TITLE = 'title';
    const TEST_STATUS = 'status';
    const TEST_RESULTS = 'results';
    const TEST_DETAILS = 'details';

    protected $testData = [];
    protected $filename;
    protected $displayFilename;
    protected $classname;

    public function __construct(string $title = '', string $filename = '', string $displayFilename = '', string $classname = '')
    {
        $this->filename = $filename;
        $this->displayFilename = $displayFilename;
        $this->classname = $classname;

        $this->testData = [
            self::TEST_TITLE => $title,
            self::TEST_STATUS => self::RESULT_INFO,
            self::TEST_RESULTS => [],
            self::TEST_DETAILS => [],
        ];
    }

    public function setTitle(string $title)
    {
        $this->testData[self::TEST_TITLE] = $title;
    }

    public function setStatus(int $status)
    {
        if (
            in_array($status, [self::RESULT_INFO, self::RESULT_WARN, self::RESULT_ERROR])
            && ($status > $this->testData[self::TEST_STATUS])
        ) {
            $this->testData[self::TEST_STATUS] = intval($status);
        }
    }

    public function setStatusWarn()
    {
        if ($this->testData[self::TEST_STATUS] < self::RESULT_WARN) {
            $this->testData[self::TEST_STATUS] = self::RESULT_WARN;
        }
    }

    public function setStatusError()
    {
        if ($this->testData[self::TEST_STATUS] < self::RESULT_ERROR) {
            $this->testData[self::TEST_STATUS] = self::RESULT_ERROR;
        }
    }

    public function addDetail(string $value)
    {
        $this->testData[self::TEST_DETAILS][] = $value;
    }

    public function addResult(string $value)
    {
        $this->testData[self::TEST_RESULTS][] = $value;
    }

    public function getStatus()
    {
        return $this->testData[self::TEST_STATUS];
    }

    public function getTitle(): string
    {
        return $this->testData[self::TEST_TITLE];
    }

    public function getDetails(): array
    {
        return $this->testData[self::TEST_DETAILS];
    }

    public function getResults(): array
    {
        return $this->testData[self::TEST_RESULTS];
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function setDisplayFilename(string $displayFilename)
    {
        $this->displayFilename = $displayFilename;
    }

    public function getDisplayFilename(): string
    {
        return $this->displayFilename;
    }

    public function getClassname(): string
    {
        return $this->classname;
    }

    public function setClassname(string $classname)
    {
        $this->classname = $classname;
    }

    public function toArray()
    {
        return $this->testData;
    }

    public static function formatStatus(int $status)
    {
        switch ($status) {
            case self::RESULT_INFO:
                return 'info';

            case self::RESULT_WARN:
                return 'warning';

            case self::RESULT_ERROR:
                return 'error';
        }
    }
}
