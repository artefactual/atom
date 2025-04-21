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
 * Export accession records to a single CSV file.
 *
 * @author     Daniel Lovegrove <d.lovegrove11@gmail.com>
 */
class csvExportAccessionsTask extends exportBulkBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'accession-export';
    protected $briefDescription = 'Export accession record data to a CSV file';

    protected $detailedDescription = <<<'EOF'
Exports all accession record data to a CSV file.

For example, this command exports all accessions to a CSV file located at /path/to/accession.csv:

    [php symfony csv:accession-export /path/to/accession.csv|INFO]

EOF;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
        $this->context = sfContext::createInstance($configuration);

        // Prepare CSV exporter
        $writer = new csvAccessionExport($arguments['path']);
        $writer->loadResourceSpecificConfiguration('QubitAccession');

        $itemsExported = 0;

        $rows = $this->getAccessionRecords();
        $numItems = count($rows);

        $this->logSection('csv', "Found {$numItems} accession records to export. Starting export.");

        foreach ($rows as $row) {
            $accessionRecord = QubitAccession::getById($row['id']);
            $this->context->getUser()->setCulture($row['culture']);

            $writer->exportResource($accessionRecord);

            $this->indicateProgress($options['items-until-update']);
            ++$itemsExported;
        }

        $this->log('');
        $this->logSection('csv', 'Export complete!');

        return 0;
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addCoreArgumentsAndOptions();
    }

    private function getAccessionRecords()
    {
        $sql = 'SELECT ai.id, ai.culture '.
            'FROM accession_i18n ai '.
            'INNER JOIN object o ON ai.id=o.id '.
            "WHERE o.class_name='QubitAccession';";

        return QubitPdo::fetchAll($sql, null, ['fetchMode' => PDO::FETCH_ASSOC]);
    }
}
