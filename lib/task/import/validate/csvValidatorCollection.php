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
 * Collection of CSV validator objects.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class CsvValidatorCollection
{
    // Static map of available validator classes.
    public static $validatorsAvailable = [
        'CsvSampleValuesValidator' => CsvSampleValuesValidator::class,
        'CsvFileEncodingValidator' => CsvFileEncodingValidator::class,
        'CsvColumnNameValidator' => CsvColumnNameValidator::class,
        'CsvColumnCountValidator' => CsvColumnCountValidator::class,
        'CsvDuplicateColumnNameValidator' => CsvDuplicateColumnNameValidator::class,
        'CsvEmptyRowValidator' => CsvEmptyRowValidator::class,
        'CsvCultureValidator' => CsvCultureValidator::class,
        'CsvLanguageValidator' => CsvLanguageValidator::class,
        'CsvFieldLengthValidator' => CsvFieldLengthValidator::class,
        'CsvParentValidator' => CsvParentValidator::class,
        'CsvLegacyIdValidator' => CsvLegacyIdValidator::class,
        'CsvEventValuesValidator' => CsvEventValuesValidator::class,
        'CsvDigitalObjectPathValidator' => CsvDigitalObjectPathValidator::class,
        'CsvDigitalObjectUriValidator' => CsvDigitalObjectUriValidator::class,
    ];

    protected $validators = [];
    protected $options = [];

    protected function __construct(array $validatorArray, array $options)
    {
        $this->options = $options;

        foreach ($validatorArray as $key => $class) {
            $this->validators[$key] = new $class($options);
        }
    }

    public function setOrmClasses(array $ormClasses)
    {
        foreach ($this->validators as $validator) {
            $validator->setOrmClasses($ormClasses);
        }
    }

    public function reset()
    {
        foreach ($this->validators as $validator) {
            $validator->reset();
        }
    }

    public function setFilename(string $filename, string $displayFilename)
    {
        foreach ($this->validators as $validator) {
            $validator->setFilename($filename);
            $validator->setDisplayFilename($displayFilename);
        }
    }

    public function setColumnCount(int $longestRow)
    {
        foreach ($this->validators as $validator) {
            $validator->setColumnCount($longestRow);
        }
    }

    public function testRow(array $header, array $row)
    {
        foreach ($this->validators as $validator) {
            $validator->testRow($header, $row);
        }
    }

    public function getResultCollection(?CsvValidatorResultCollection $resultCollection = null): CsvValidatorResultCollection
    {
        if (!isset($resultCollection)) {
            $resultCollection = new CsvValidatorResultCollection();
        }

        foreach ($this->validators as $validator) {
            $resultCollection->appendResult($validator->getTestResult());
        }

        return $resultCollection;
    }

    /**
     * Gets an instantiated validatorCollection object populated with
     * the correct Validator objects for the specified $className.
     *
     * @param string $className             The entity name. e.g. QubitInformationObject
     * @param array  $options               The options array passed to the validator
     * @param string $specificValidatorList Comma separated string of validator class names
     */
    public static function getValidatorCollection(string $className, array &$options, ?string $validatorList = null): CsvValidatorCollection
    {
        $validatorsAvailable = self::$validatorsAvailable;

        // Remove tests that are not applicable to this $className.
        foreach ($validatorsAvailable as $key => $class) {
            if (!empty($class::LIMIT_TO) && !in_array($className, $class::LIMIT_TO)) {
                unset($validatorsAvailable[$key]);
            }
        }

        // If specific tests not used, use default validators for this $className.
        if (empty($validatorList)) {
            return new CsvValidatorCollection($validatorsAvailable, $options);
        }

        // Using specified validators. Ensure they are valid for this $className.
        $specificValidators = explode(',', $validatorList);
        $validators = [];

        foreach ($specificValidators as $validatorClassName) {
            // Test if validator class is valid.
            if (isset($validatorsAvailable[$validatorClassName])) {
                // Create test class array.
                $validators[$validatorClassName] = $validatorsAvailable[$validatorClassName];
            } else {
                throw new UnexpectedValueException(sprintf('Invalid tests "%s".', $validatorList));
            }
        }

        // if empty, throw exception
        if (empty($validators)) {
            throw new UnexpectedValueException(sprintf('Invalid tests "%s".', $validatorList));
        }

        $options['specificTests'] = $validatorList;

        return new CsvValidatorCollection($validators, $options);
    }
}
