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
 * CSV validation result collection class.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvValidatorResultCollection
{
    protected $results = [];

    public function appendResult(CsvValidatorResult $result)
    {
        $this->results[] = $result;

        $this->sortByFilenameStatusDescending();
    }

    public function toArray(bool $verbose = false)
    {
        $resultArray = [];

        foreach ($this->results as $testResult) {
            $resultArray[$testResult->getFilename()][$testResult->getClassname()] = $testResult->toArray();
        }

        return $resultArray;
    }

    public function toJson(bool $verbose = false)
    {
        return json_encode($this->toArray($verbose));
    }

    public function getByFilenameTestname(string $filename, string $testname)
    {
        foreach ($this->results as $result) {
            if ($filename === $result->getFilename() && $testname === $result->getClassname()) {
                return $result->toArray();
            }
        }
    }

    public function getErrorCount()
    {
        $errorCount = 0;

        foreach ($this->results as $testResult) {
            if (CsvValidatorResult::RESULT_ERROR === $testResult->getStatus()) {
                ++$errorCount;
            }
        }

        return $errorCount;
    }

    public function getWarnCount()
    {
        $warnCount = 0;

        foreach ($this->results as $testResult) {
            if (CsvValidatorResult::RESULT_WARN === $testResult->getStatus()) {
                ++$warnCount;
            }
        }

        return $warnCount;
    }

    public function sortByFilenameStatusDescending()
    {
        uasort($this->results, ['CsvValidatorResultCollection', 'compare']);
    }

    public static function renderResultsAsText(CsvValidatorResultCollection $results, bool $verbose = false): string
    {
        $outputString = '';
        $errorCount = $results->getErrorCount();
        $warnCount = $results->getWarnCount();

        if (!empty($errorCount)) {
            $outputString .= sprintf("\n** Issues have been detected with this CSV that will prevent it from being imported correctly.\n\n");
        } elseif (!empty($warnCount)) {
            $outputString .= sprintf("\n** Warnings should be reviewed before proceeding with importing this CSV.\n\n");
        } else {
            $outputString .= sprintf("\nNo issues detected.\n\n");
        }

        $outputString .= sprintf("Errors: %s\n", $errorCount);
        $outputString .= sprintf("Warnings: %s\n", $warnCount);

        $resultArray = $results->toArray();

        foreach ($resultArray as $filename => $fileGroup) {
            $fileStr = sprintf("\nFilename: %s", $filename);
            $outputString .= sprintf("%s\n", $fileStr);
            $outputString .= sprintf("%s\n", str_repeat('=', strlen($fileStr)));

            foreach ($fileGroup as $testResult) {
                if (CsvValidatorResult::RESULT_INFO === $testResult['status'] && !$verbose) {
                    continue;
                }
                $outputString .= CsvValidatorResultCollection::renderResultArrayAsText($testResult, $verbose);
            }
        }

        return $outputString;
    }

    protected static function renderResultArrayAsText(array $result, bool $verbose = false): string
    {
        $outputString = '';

        $outputString .= sprintf("\n%s - %s\n", $result['title'], CsvValidatorResult::formatStatus($result['status']));
        $outputString .= sprintf("%s\n", str_repeat('-', strlen($result['title'])));

        foreach ($result['results'] as $line) {
            $outputString .= sprintf("%s\n", $line);
        }

        if ($verbose && 0 < count($result['details'])) {
            $outputString .= sprintf("\nDetails:\n");

            foreach ($result['details'] as $line) {
                $outputString .= sprintf("%s\n", $line);
            }
        }

        return $outputString;
    }

    protected function compare(CsvValidatorResult $a, CsvValidatorResult $b)
    {
        if ($a->getFilename() === $b->getFilename()) {
            if ($a->getStatus() === $b->getStatus()) {
                return 0;
            }

            return ($a->getStatus() > $b->getStatus()) ? -1 : 1;
        }

        return ($a->getFilename() < $b->getFilename()) ? -1 : 1;
    }
}
