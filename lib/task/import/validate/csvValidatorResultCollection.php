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
class CsvValidatorResultCollection implements Iterator
{
    protected $results = [];
    private $index = 0;

    public function __construct()
    {
        $this->index = 0;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function current()
    {
        return $this->results[$this->index];
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return isset($this->results[$this->index]);
    }

    public function next()
    {
        ++$this->index;
    }

    public function appendResult(CsvValidatorResult $result)
    {
        $this->results[] = $result;

        $this->sortByFilenameStatusDescending();
    }

    public function toArray()
    {
        $resultArray = [];

        foreach ($this->results as $testResult) {
            $resultArray[$testResult->getDisplayFilename()][$testResult->getClassname()] = $testResult->toArray();
        }

        return $resultArray;
    }

    public function toJson()
    {
        return json_encode($this->toArray());
    }

    public function getByFilenameTestname(string $filename, string $testname)
    {
        foreach ($this->results as $result) {
            if ($filename === $result->getFilename() && $testname === $result->getClassname()) {
                return $result->toArray();
            }
        }
    }

    public function getErrorCount(?string $filename = null)
    {
        $errorCount = 0;

        foreach ($this->results as $result) {
            if ($this->testFilename($filename, $result->getFilename()) && CsvValidatorResult::RESULT_ERROR === $result->getStatus()) {
                ++$errorCount;
            }
        }

        return $errorCount;
    }

    public function getWarnCount(?string $filename = null)
    {
        $warnCount = 0;

        foreach ($this->results as $result) {
            if ($this->testFilename($filename, $result->getFilename()) && CsvValidatorResult::RESULT_WARN === $result->getStatus()) {
                ++$warnCount;
            }
        }

        return $warnCount;
    }

    public function sortByFilenameStatusDescending()
    {
        uasort($this->results, ['CsvValidatorResultCollection', 'compare']);
        // Reindex sorted array since uasort does not alter index values.
        // Without this, foreach() will not use sorted order - it will use index order.
        $this->results = array_values($this->results);
    }

    public static function renderResultsAsText(CsvValidatorResultCollection $results, bool $verbose = false): string
    {
        $outputString = '';

        $outputString .= "CSV Results:\n";

        foreach ($results as $result) {
            if ($filename !== $result->getFilename()) {
                if (!empty($filename)) {
                    $outputString .= "\n";
                }
                $filename = $result->getFilename();
                $displayFilename = $result->getDisplayFilename();

                $fileStr = sprintf('Filename: %s', $displayFilename);
                $outputString .= sprintf("%s\n", str_repeat('-', strlen($fileStr)));
                $outputString .= sprintf("%s\n", $fileStr);
                $outputString .= sprintf("%s\n", str_repeat('-', strlen($fileStr)));

                $errorCount = $results->getErrorCount($filename);
                $warnCount = $results->getWarnCount($filename);

                $outputString .= sprintf("Errors: %s\n", $errorCount);
                $outputString .= sprintf("Warnings: %s\n", $warnCount);

                if (!empty($errorCount)) {
                    $outputString .= sprintf("\n** Issues have been detected with this CSV that will prevent it from being imported correctly.\n");
                } elseif (!empty($warnCount)) {
                    $outputString .= sprintf("\n** Warnings should be reviewed before proceeding with importing this CSV.\n");
                } else {
                    $outputString .= sprintf("\nNo issues detected.\n");
                }
            }

            if (CsvValidatorResult::RESULT_INFO === $result->getStatus() && !$verbose) {
                continue;
            }

            $outputString .= CsvValidatorResultCollection::renderResultAsText($result, $verbose);
        }
        $outputString .= "\n";

        return $outputString;
    }

    protected function testFilename(?string $filename, string $resultFilename): bool
    {
        if (null === $filename) {
            return true;
        }

        return $filename === $resultFilename;
    }

    protected static function renderResultAsText(CsvValidatorResult $result, bool $verbose = false): string
    {
        $outputString = '';

        $outputString .= sprintf("\n%s - %s\n", $result->getTitle(), CsvValidatorResult::formatStatus($result->getStatus()));
        $outputString .= sprintf("%s\n", str_repeat('-', strlen($result->getTitle())));

        $results = $result->getResults();
        $details = $result->getDetails();

        foreach ($results as $line) {
            $outputString .= sprintf("%s\n", $line);
        }

        if ($verbose && 0 < count($details)) {
            $outputString .= sprintf("\nDetails:\n");

            foreach ($details as $line) {
                $outputString .= sprintf("%s\n", $line);
            }
        }

        return $outputString;
    }

    protected function compare(CsvValidatorResult $a, CsvValidatorResult $b)
    {
        if ($a->getDisplayFilename() === $b->getDisplayFilename()) {
            if ($a->getStatus() === $b->getStatus()) {
                return 0;
            }

            return ($a->getStatus() > $b->getStatus()) ? -1 : 1;
        }

        return ($a->getDisplayFilename() < $b->getDisplayFilename()) ? -1 : 1;
    }
}
