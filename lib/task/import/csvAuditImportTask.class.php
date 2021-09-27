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
 * Audit CSV import.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class csvAuditImportTask extends arBaseTask
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

        $auditOptions = $this->setAuditOptions($options);

        $auditer = new CsvImportAuditer($auditOptions);
        $auditer->setSourceName($arguments['sourcename']);
        $auditer->setFilename($arguments['filename']);

        if (!empty($options['target-name'])) {
            $auditer->setTargetName($options['target-name']);
        }

        $this->log(sprintf(
            'Auditing import data from %s...'.PHP_EOL,
            $auditer->getFilename()
        ));

        $auditer->doAudit();

        $this->log(
            sprintf(
                PHP_EOL.'Done! Audited %u rows.'.PHP_EOL,
                $auditer->countRowsTotal()
            )
        );
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument(
                'sourcename',
                sfCommandArgument::REQUIRED,
                'The source name of the previous import.'
            ),
            new sfCommandArgument(
                'filename',
                sfCommandArgument::REQUIRED,
                'The input file name (CSV format).'
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

            // Audit options
            new sfCommandOption(
                'target-name',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Keymap target name',
                null
            ),
            new sfCommandOption(
                'id-column-name',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'Name of the ID column in the source CSV file (default: "legacyId")'
            ),
        ]);

        $this->namespace = 'csv';
        $this->name = 'audit-import';
        $this->briefDescription = 'Audit CSV import.';
        $this->detailedDescription = <<<'EOF'
Audit CSV import by checking to make sure a keymap has been created for each
row.
EOF;
    }

    protected function setAuditOptions($options)
    {
        $opts = [];

        $keymap = [
            'id-column-name' => 'idColumnName',
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
