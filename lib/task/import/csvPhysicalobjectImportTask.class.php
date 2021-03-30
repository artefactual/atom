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
 * Import csv authoriy record data.
 *
 * @author     David Juhasz <djuhasz@artefactual.com>
 */
class csvPhysicalobjectImportTask extends arBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $importOptions = $this->setImportOptions($options);

        $importer = new PhysicalObjectCsvImporter(
            $this->context,
            $this->getDbConnection(),
            $importOptions
        );
        $importer->setFilename($arguments['filename']);

        $this->log(sprintf(
            'Importing physical object data from %s...'.PHP_EOL,
            $importer->getFilename()
        ));

        if (isset($options['skip-rows']) && $options['skip-rows'] > 0) {
            if (1 == $options['skip-rows']) {
                $this->log('Skipping first row...');
            } else {
                $this->log(sprintf('Skipping first %u rows...', $options['skip-rows']));
            }
        }

        $importer->doImport();

        $this->log(
            sprintf(
                PHP_EOL.'Done! Imported %u of %u rows.'.PHP_EOL,
                $importer->countRowsImported(),
                $importer->countRowsTotal()
            )
        );

        $this->log($importer->reportTimes());
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument(
                'filename',
                sfCommandArgument::REQUIRED,
                'The input file name (csv format).'
            ),
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

            // Import options
            new sfCommandOption(
                'culture',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'ISO 639-1 Code for rows without an explicit culture',
                'en'
            ),
            new sfCommandOption(
                'debug',
                'd',
                sfCommandOption::PARAMETER_NONE,
                'Enable debug mode'
            ),
            new sfCommandOption(
                'empty-overwrite',
                'e',
                sfCommandOption::PARAMETER_NONE,
                'When set an empty CSV value will overwrite existing data (update only)'
            ),
            new sfCommandOption(
                'error-log',
                'l',
                sfCommandOption::PARAMETER_REQUIRED,
                'Log errors to indicated file'
            ),
            new sfCommandOption(
                'header',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Provide column names (CSV format) and import first row of file as data'
            ),
            new sfCommandOption(
                'index',
                'i',
                sfCommandOption::PARAMETER_NONE,
                'Update search index during import'
            ),
            new sfCommandOption(
                'multi-match',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Action when matching more than one existing record:
                    "skip" : don\'t update any records
                    "first": update first matching record,
                    "all"  : update all matching records',
                'skip'
            ),
            new sfCommandOption(
                'partial-matches',
                'p',
                sfCommandOption::PARAMETER_NONE,
                'Match existing records if first part of name matches import name'
            ),
            new sfCommandOption(
                'rows-until-update',
                'r',
                sfCommandOption::PARAMETER_REQUIRED,
                'Show import progress every [n] rows (n=0: errors only)',
                1
            ),
            new sfCommandOption(
                'skip-rows',
                'o',
                sfCommandOption::PARAMETER_REQUIRED,
                'Skip [n] rows before importing',
                0
            ),
            new sfCommandOption(
                'skip-unmatched',
                's',
                sfCommandOption::PARAMETER_NONE,
                'Skip unmatched records during update instead of creating new records'
            ),
            new sfCommandOption(
                'source-name',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Source name to use when inserting keymap entries'
            ),
            new sfCommandOption(
                'update',
                'u',
                sfCommandOption::PARAMETER_NONE,
                'Update existing record if name matches imported name.'
            ),
        ]);

        $this->namespace = 'csv';
        $this->name = 'physicalobject-import';
        $this->briefDescription = 'Import physical object CSV data.';
        $this->detailedDescription = <<<'EOF'
Import physical object CSV data
EOF;
    }

    protected function getDbConnection()
    {
        $databaseManager = new sfDatabaseManager($this->configuration);

        return $databaseManager->getDatabase('propel')->getConnection();
    }

    protected function setImportOptions($options)
    {
        $this->validateOptions($options);

        $opts = [];

        $keymap = [
            'culture' => 'defaultCulture',
            'debug' => 'debug',
            'empty-overwrite' => 'overwriteWithEmpty',
            'error-log' => 'errorLog',
            'header' => 'header',
            'index' => 'updateSearchIndex',
            'skip-rows' => 'offset',
            'skip-unmatched' => 'insertNew',
            'multi-match' => 'onMultiMatch',
            'partial-matches' => 'partialMatches',
            'quiet' => 'quiet',
            'rows-until-update' => 'progressFrequency',
            'source-name' => 'sourceName',
            'update' => 'updateExisting',
        ];

        foreach ($keymap as $oldkey => $newkey) {
            if (empty($options[$oldkey])) {
                continue;
            }

            // Invert value of skip-unmatched
            if ('skip-unmatched' == $oldkey) {
                $opts[$newkey] = !$options[$oldkey];

                continue;
            }

            $opts[$newkey] = $options[$oldkey];
        }

        return $opts;
    }

    protected function validateOptions($options)
    {
        if ($options['skip-unmatched'] && !$options['update']) {
            $msg = <<<'EOM'
The --skip-unmatched option can not be used without the --update option.
EOM;

            throw new sfException($msg);
        }
    }
}
