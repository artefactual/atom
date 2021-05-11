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
 * Check csv data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class csvCheckImportTask extends arBaseTask
{
    protected $verbose = false;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $validatorOptions = $this->setOptions($options);

        if (!empty($options['verbose'])) {
            $this->verbose = true;
        }

        $filenames = $this->setCsvValidatorFilenames($arguments['filename']);

        $validator = new CsvImportValidator(
            $this->context, $this->getDbConnection(), $validatorOptions
        );

        $validator->setShowDisplayProgress(true);
        $validator->setFilenames($filenames);
        $results = $validator->validate();

        $output = CsvValidatorResultCollection::renderResultsAsText($results, $this->verbose);
        echo $output;

        unset($validator);
    }

    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::REQUIRED,
              'The input file name (csv format).'),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                'qubit'
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),
            new sfCommandOption(
                'verbose',
                'i',
                sfCommandOption::PARAMETER_NONE,
                'Provide detailed information regarding each test.'
            ),
            new sfCommandOption(
                'source',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Source name for validating parentId matching against previous imports. If not set, parentId validation against AtoM\'s database will be skipped.'
            ),
            new sfCommandOption(
                'class-name',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Qubit object type contained in CSV.',
                'QubitInformationObject'
            ),
            new sfCommandOption(
                'specific-tests',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Specific test classes to run.'
            ),
            new sfCommandOption(
                'path-to-digital-objects',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Path to root of digital object folder that will match digitalObjectPath in CSV.'
            ),
        ]);

        $this->namespace = 'csv';
        $this->name = 'check-import';
        $this->briefDescription = 'Check CSV data, providing diagnostic info.';
        $this->detailedDescription = <<<'EOF'
    Check CSV data, providing information about it.
EOF;
    }

    protected function getDbConnection()
    {
        $databaseManager = new sfDatabaseManager($this->configuration);

        return $databaseManager->getDatabase('propel')->getConnection();
    }

    protected function setCsvValidatorFilenames($filenameString)
    {
        // Could be a comma separated list of filenames or just one.
        foreach (explode(',', $filenameString) as $filename) {
            CsvImportValidator::validateFileName($filename);
            // The validator expects an associative array of files
            // where displayname => filename
            $filenames[$filename] = $filename;
        }

        return $filenames;
    }

    protected function setOptions($options = [])
    {
        $opts = [];

        $keymap = [
            'source' => 'source',
            'class-name' => 'className',
            'escape' => 'escape',
            'specific-tests' => 'specificTests',
            'path-to-digital-objects' => 'pathToDigitalObjects',
        ];

        foreach ($keymap as $oldkey => $newkey) {
            if (empty($options[$oldkey])) {
                continue;
            }

            $opts[$newkey] = $options[$oldkey];
        }

        return $opts;
    }
}
