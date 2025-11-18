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
 * Bulk export data to CSV.
 */
class csvExportBulkTask extends exportBulkBaseTask
{
    protected $namespace = 'export';
    protected $name = 'bulk-csv';
    protected $briefDescription = 'Bulk export multiple CSV files at once';

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        // Make sure standard is lower case
        $options['standard'] = $this->normalizeExportFormat(
            $options['standard'],
            ['isad', 'rad']
        );

        if (!isset($options['single-slug'])) {
            $this->checkPathIsWritable($arguments['path']);
        }

        $configuration = ProjectConfiguration::getApplicationConfiguration(
            'qubit',
            'cli',
            false
        );
        $context = sfContext::createInstance($configuration);

        // QubitSetting are not available for tasks? See lib/SiteSettingsFilter.class.php
        sfConfig::add(QubitSetting::getSettingsArray());

        $itemsExported = 0;

        $conn = $this->getDatabaseConnection();
        $rows = $conn->query(
            $this->informationObjectQuerySql($options), PDO::FETCH_ASSOC
        );

        echo 'Exporting as '.strtoupper($options['standard']).".\n";

        foreach ($rows as $row) {
            $resource = QubitInformationObject::getById($row['id']);

            // Don't export draft descriptions with public option
            if (
                isset($options['public'])
                && $options['public']
                && QubitTerm::PUBLICATION_STATUS_DRAFT_ID ==
                    $resource->getPublicationStatus()->statusId
            ) {
                continue;
            }

            if (isset($options['single-slug'])) {
                if (is_dir($arguments['path'])) {
                    throw new sfException(
                      'When using the single-slug option, path should'.
                      ' be a file.'
                    );
                }

                // If we're just exporting a single hierarchy of descriptions,
                // the given path is actually the full path and filename
                $filePath = $arguments['path'];
            } else {
                $filename = $this->generateSortableFilename(
                  $resource, 'csv', $options['standard']
                );
                $filePath = sprintf('%s/%s', $arguments['path'], $filename);
            }

            // Make a new writer for each row
            $writer = new csvInformationObjectExport(
                $filePath,
                $options['standard'],
                $options['rows-per-file']
            );
            $writer->user = $context->getUser();
            $writer->user->setCulture($row['culture']);
            $writer->setOptions($options);

            $writer->exportResource($resource);

            $this->indicateProgress($options['items-until-update']);

            ++$itemsExported;

            if (0 === ($itemsExported % 1000)) {
                Qubit::clearClassCaches();
            }
        }

        echo "\nExport complete (".$itemsExported." descriptions exported).\n";
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addCommonArgumentsAndOptions();
        $this->addOptions([
            new sfCommandOption('standard', null, sfCommandOption::PARAMETER_OPTIONAL, 'Description format ("isad" or "rad")', 'isad'),
        ]);
        $this->addOptions([
            new sfCommandOption('rows-per-file', null, sfCommandOption::PARAMETER_OPTIONAL, 'Rows per file (disregarded if writing to a file, not a directory)', false),
        ]);
    }
}
