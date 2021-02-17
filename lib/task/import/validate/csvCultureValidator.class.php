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
 * CSV culture column test. Check if present, check values against master list,
 * and check if piped value.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvCultureValidator extends CsvBaseValidator
{
    const TITLE = 'Culture Check';

    protected $cultureColumnPresent;
    protected $rowsWithBlankCulture = 0;
    protected $rowsWithPipeFoundInCulture = 0;
    protected $rowsWithInvalidCulture = 0;
    protected $invalidCultures = [];

    public function __construct(?array $options = null)
    {
        $this->setTitle(self::TITLE);

        parent::__construct($options);
    }

    public function reset()
    {
        $this->cultureColumnPresent = null;
        $this->rowsWithBlankCulture = 0;
        $this->rowsWithPipeFoundInCulture = 0;
        $this->rowsWithInvalidCulture = 0;
        $this->invalidCultures = [];

        parent::reset();
    }

    public function testRow(array $header, array $row)
    {
        parent::testRow($header, $row);
        $row = $this->combineRow($header, $row);

        // Set if culture column is present.
        if (!isset($this->cultureColumnPresent)) {
            $this->cultureColumnPresent = isset($row['culture']);
        }

        if (!$this->cultureColumnPresent) {
            return;
        }

        // If present check contents.
        if (empty($row['culture'])) {
            ++$this->rowsWithBlankCulture;

            return;
        }

        // Validate culture value against AtoM.
        if ($this->isCultureValid($row['culture'])) {
            return;
        }

        // Keep a list of invalid culture values.
        if (!in_array($row['culture'], $this->invalidCultures)) {
            $this->invalidCultures[] = $row['culture'];
        }

        // Check if contains pipe.
        if (0 < strpos($row['culture'], '|')) {
            ++$this->rowsWithPipeFoundInCulture;
            $this->testData->addDetail(implode(',', $row));
        } else {
            ++$this->rowsWithInvalidCulture;
            $this->testData->addDetail(implode(',', $row));
        }
    }

    public function getTestResult()
    {
        if (!$this->cultureColumnPresent) {
            // culture column not present in file.
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf("'culture' column not present in file."));
            $this->testData->addResult(sprintf("Rows without a valid culture value will be imported using AtoM's default source culture."));

            return parent::getTestResult();
        }

        // Rows exist without culture populated.
        if (0 < $this->rowsWithBlankCulture) {
            $this->testData->setStatusWarn();
            $this->testData->addResult(sprintf('Rows with blank culture value: %s', $this->rowsWithBlankCulture));
        }

        // Rows exist with invalid culture.
        if (0 < $this->rowsWithInvalidCulture) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Rows with invalid culture values: %s', $this->rowsWithInvalidCulture));
        }

        // Rows exist with culture containing pipe '|'
        if (0 < $this->rowsWithPipeFoundInCulture) {
            $this->testData->setStatusError();
            $this->testData->addResult(sprintf('Rows with pipe character in culture values: %s', $this->rowsWithPipeFoundInCulture));
            $this->testData->addResult(sprintf("'culture' column does not allow for multiple values separated with a pipe '|' character."));
        }

        if (
            0 < $this->rowsWithInvalidCulture
            || 0 < $this->rowsWithPipeFoundInCulture
        ) {
            $this->testData->addResult(sprintf('Invalid culture values: %s', implode(', ', $this->invalidCultures)));
        }

        if (
            0 < $this->rowsWithBlankCulture
            || 0 < $this->rowsWithInvalidCulture
        ) {
            $this->testData->addResult(sprintf("Rows with a blank culture value will be imported using AtoM's default source culture."));
        }

        if (
            0 === $this->rowsWithBlankCulture
            && 0 === $this->rowsWithInvalidCulture
            && 0 === $this->rowsWithPipeFoundInCulture
        ) {
            $this->testData->addResult(sprintf("'culture' column values are all valid."));
        }

        return parent::getTestResult();
    }

    // TODO: Remove these DB accesses to a wrapper class so it's not performed in the
    // test class itself.
    protected function isCultureValid(string $culture)
    {
        if (!empty($culture)) {
            return sfCultureInfo::validCulture($culture);
        }
    }
}
