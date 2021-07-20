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
 * CSV scriptOfDescription column test. Check if present, check values against
 * master list.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvScriptValidator extends CsvBaseValidator
{
    public const TITLE = 'Script of Description Check';
    public const LIMIT_TO = ['QubitInformationObject'];

    protected $scriptOfDescriptionList = [];
    protected $scriptOfDescriptionColumnPresent;
    protected $rowsWithInvalidScriptOfDescription = 0;
    protected $invalidScriptOfDescriptionList = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);
        parent::__construct($options);

        $this->scriptOfDescriptionList = array_keys(sfCultureInfo::getInstance()->getScripts());
    }

    public function reset()
    {
        $this->scriptOfDescriptionColumnPresent = null;
        $this->rowsWithInvalidScriptOfDescription = 0;
        $this->invalidScriptOfDescriptionList = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);
        $row = $this->combineRow($header, $row);

        // Set if scriptOfDescription column is present.
        if (!isset($this->scriptOfDescriptionColumnPresent)) {
            $this->scriptOfDescriptionColumnPresent = isset($row['scriptOfDescription']);
        }

        if (!$this->scriptOfDescriptionColumnPresent || empty($row['scriptOfDescription'])) {
            return;
        }

        // Validate scriptOfDescription value against AtoM.
        $errorDetailAdded = false;
        foreach (explode('|', $row['scriptOfDescription']) as $value) {
            if ($this->isScriptOfDescriptionValid($value)) {
                continue;
            }

            if (!$errorDetailAdded) {
                ++$this->rowsWithInvalidScriptOfDescription;
                $this->testData->addDetail(implode(',', $row));
                $errorDetailAdded = true;
            }

            // Keep a list of invalid scriptOfDescription values.
            if (!in_array(trim($value), $this->invalidScriptOfDescriptionList)) {
                $this->invalidScriptOfDescriptionList[] = trim($value);
            }
        }
    }

    public function getTestResult()
    {
        if (!$this->scriptOfDescriptionColumnPresent) {
            // scriptOfDescription column not present in file.
            $this->testData->addResult(sprintf("'scriptOfDescription' column not present in file."));

            return parent::getTestResult();
        }

        // Rows exist with invalid scriptOfDescription.
        if (0 < $this->rowsWithInvalidScriptOfDescription) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Rows with invalid scriptOfDescription values: %s', $this->rowsWithInvalidScriptOfDescription));
            $this->testData->addResult(sprintf('Invalid scriptOfDescription values: %s', implode(', ', $this->invalidScriptOfDescriptionList)));
        }

        if (0 === $this->rowsWithInvalidScriptOfDescription) {
            $this->testData->addResult(sprintf("'scriptOfDescription' column values are all valid."));
        }

        return parent::getTestResult();
    }

    protected function isScriptOfDescriptionValid(string $scriptOfDescription)
    {
        $scriptOfDescription = trim($scriptOfDescription);

        if ('' === trim($scriptOfDescription)) {
            return false;
        }

        return in_array($scriptOfDescription, $this->scriptOfDescriptionList);
    }
}
