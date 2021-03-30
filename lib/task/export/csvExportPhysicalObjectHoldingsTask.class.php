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
 * Export physical object holdings CSV data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvExportPhysicalObjectHoldingsTask extends arBaseTask
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

        $this->log('Exporting physical storage holdings report..');

        $report = new QubitPhysicalObjectCsvHoldingsReport($this->getReportOptions($options));
        $report->write($arguments['filename']);

        $this->log('Done.');
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
            new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'Output filename'),
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                true
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
                'omit-empty',
                's',
                sfCommandOption::PARAMETER_NONE,
                'Omit physical storage without holdings'
            ),
            new sfCommandOption(
                'holding-type',
                'e',
                sfCommandOption::PARAMETER_OPTIONAL,
                'Only include specific holding type ("description", "accession", or "none")'
            ),
        ]);

        $this->namespace = 'csv';
        $this->name = 'physicalstorage-holdings';
        $this->briefDescription = 'Export physical storage holdings report as CSV data.';
        $this->detailedDescription = <<<'EOF'
Export physical storage holdings report as CSV data

Physical storage containing no holdings will be included unless the --omit-empty option is used.

Holdings can be filtered by type. Valid holding types are:
* "description" (information objects)
* "accession" (accessions)
* "none" (omit non-empty physical storage)
EOF;
    }

    protected function getReportOptions($options = [])
    {
        $this->validateOptions($options);

        $reportOptions = [];

        $reportOptions['suppressEmpty'] = $options['omit-empty'];

        if (!empty($type = strtolower($options['holding-type']))) {
            $reportOptions['holdingType'] = ('none' == $type)
                ? $type
                : QubitPhysicalObjectCsvHoldingsReport::$defaultTypeMap[$options['holding-type']];
        }

        return $reportOptions;
    }

    protected function validateOptions($options = [])
    {
        // Throw error if holding type isn't one of the allowed types
        $allowedValues = array_merge(array_keys(QubitPhysicalObjectCsvHoldingsReport::$defaultTypeMap), ['none']);

        if (!empty($options['holding-type']) && !in_array($options['holding-type'], $allowedValues)) {
            $message = sprintf(
                'Invalid holding type "%s" (must be one of: %s).',
                $options['holding-type'],
                implode(', ', $allowedValues)
            );

            throw new Exception($message);
        }
    }
}
