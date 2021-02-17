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
 * CSV Digital Object Path and URI check. Check digitalObjectPath and report:
 *  - images not referenced from CSV
 *  - images referenced in CSV but not found in image folder
 *  - images referenced more that once in the CSV.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvDigitalObjectPathValidator extends CsvBaseValidator
{
    const TITLE = 'Digital Object Path Test';
    const LIMIT_TO = ['QubitInformationObject'];

    // Do not reset between CSVs.
    protected $fileList = [];
    protected $pathToDigitalObjects = '';
    // Reset between files.
    protected $digitalObjectPathColumnPresent;
    protected $digitalObjectUriColumnPresent;
    protected $digitalObjectUses = [];
    protected $overriddenByUriCount = 0;

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);

        $this->setPathToDigitalObjects($this->options['pathToDigitalObjects']);
    }

    public function reset()
    {
        $this->digitalObjectPathColumnPresent = null;
        $this->digitalObjectUses = [];
        $this->digitalObjectUriColumnPresent = null;
        $this->overriddenByUriCount = 0;

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);
        $row = $this->combineRow($header, $row);

        if (!isset($this->digitalObjectPathColumnPresent)) {
            $this->digitalObjectPathColumnPresent = isset($row['digitalObjectPath']);
        }

        if (!isset($this->digitalObjectUriColumnPresent)) {
            $this->digitalObjectUriColumnPresent = isset($row['digitalObjectUri']);
        }

        if ($this->digitalObjectPathColumnPresent) {
            if (!empty($row['digitalObjectPath'])) {
                $this->addToUsageSummary($row['digitalObjectPath']);
            }

            // URI is preferred by import CLI task if both path and uri are populated.
            if ($this->digitalObjectUriColumnPresent) {
                if (!empty($row['digitalObjectPath']) && !empty($row['digitalObjectUri'])) {
                    ++$this->overriddenByUriCount;
                }
            }
        }
    }

    public function getTestResult()
    {
        if (false === $this->digitalObjectPathColumnPresent) {
            $this->testData->addResult(sprintf("Column 'digitalObjectPath' not present in CSV. Nothing to verify."));

            return parent::getTestResult();
        }

        $this->testData->addResult(sprintf("Column 'digitalObjectPath' found."));

        // Digital object folder option not passed/is invalid.
        if (empty($this->pathToDigitalObjects)) {
            // Option was not supplied.
            if (empty($this->options['pathToDigitalObjects'])) {
                $this->testData->addResult(sprintf('Digital object folder location not specified.'));
            }
            // Path could not be found.
            else {
                $this->testData->addResult(sprintf('Unable to open digital object folder path: %s', $this->options['pathToDigitalObjects']));
            }

            // If digitalObjectPath column is populated in CSV, this is an error.
            if (0 < count($this->digitalObjectUses)) {
                $this->testData->setStatusError();
                $this->testData->addResult(sprintf('Unable to locate files specified in digitalObjectPath column of CSV.'));
            }

            return parent::getTestResult();
        }

        if (empty($this->digitalObjectUses)) {
            $this->testData->addResult(sprintf("Column 'digitalObjectPath' is empty."));

            return parent::getTestResult();
        }

        // Check for Paths that will be overridden by URI.
        if (0 < $this->overriddenByUriCount) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("'digitalObjectPath' will be overridden by 'digitalObjectUri' if both are populated."));
            $this->testData->addResult(sprintf("'digitalObjectPath' values that will be overridden by digitalObjectUri: %s", $this->overriddenByUriCount));
        }

        $digitalObjectPathsUsedMoreThanOnce = $this->getUsedMoreThanOnce();

        if (!empty($digitalObjectPathsUsedMoreThanOnce)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf('Number of duplicated digital object paths found in CSV: %s', count($digitalObjectPathsUsedMoreThanOnce)));

            foreach ($digitalObjectPathsUsedMoreThanOnce as $path) {
                $this->testData->addDetail(sprintf("Number of duplicates for path '%s': %s", $path, $this->digitalObjectUses[$path]));
            }
        }

        $unusedFiles = $this->getUnusedFiles();

        if (!empty($unusedFiles)) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf('Digital objects in folder not referenced by CSV: %s', count($unusedFiles)));

            foreach ($unusedFiles as $file) {
                $this->testData->addDetail(sprintf('Unreferenced digital object: %s', $file));
            }
        }

        $missingFiles = $this->getMissingDigitalObjects();

        if (!empty($missingFiles)) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Digital object referenced by CSV not found in folder: %s', count($missingFiles)));

            foreach ($missingFiles as $file) {
                $this->testData->addDetail(sprintf('Unable to locate digital object: %s/%s', $this->pathToDigitalObjects, $file));
            }
        }

        return parent::getTestResult();
    }

    public function setPathToDigitalObjects(string $path)
    {
        $path = (!empty($path) && is_dir($path)) ? $path : '';

        if (is_dir($path)) {
            $this->pathToDigitalObjects = realpath($path) ? realpath($path) : $path;

            $this->getDigitalObjectFiles();
        }
    }

    protected function addToUsageSummary($value)
    {
        $this->digitalObjectUses[$value] = (!isset($this->digitalObjectUses[$value])) ? 1 : $this->digitalObjectUses[$value] + 1;
    }

    protected function getUsedMoreThanOnce()
    {
        $usedMoreThanOnce = [];

        foreach ($this->digitalObjectUses as $digitalObjectName => $uses) {
            if ($uses > 1) {
                array_push($usedMoreThanOnce, $digitalObjectName);
            }
        }

        return $usedMoreThanOnce;
    }

    private function getUnusedFiles()
    {
        $unusedFiles = [];

        foreach ($this->fileList as $file) {
            if (!isset($this->digitalObjectUses[$file])) {
                array_push($unusedFiles, $file);
            }
        }

        return $unusedFiles;
    }

    private function getMissingDigitalObjects()
    {
        $missingDigitalObjects = [];

        foreach ($this->digitalObjectUses as $file => $uses) {
            if (!file_exists($this->pathToDigitalObjects.'/'.$file)) {
                array_push($missingDigitalObjects, $file);
            }
        }

        return $missingDigitalObjects;
    }

    private function getDigitalObjectFiles()
    {
        $this->fileList = [];

        if (empty($this->pathToDigitalObjects)) {
            return;
        }

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->pathToDigitalObjects));

        foreach ($objects as $filePath => $object) {
            if (!is_dir($filePath)) {
                // Remove absolute path leading to image directory
                $relativeFilePath = substr($filePath, strlen($this->pathToDigitalObjects) + 1, strlen($filePath));
                array_push($this->fileList, $relativeFilePath);
            }
        }
    }
}
