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
 * Bulk export digital object data.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class csvExportDigitalObjectTask extends exportBulkBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'digitalobject-export';
    protected $briefDescription = 'Export digital object data';

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        if (isset($options['items-until-update']) && !ctype_digit($options['items-until-update'])) {
            throw new UnexpectedValueException('items-until-update must be a number');
        }

        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
        $this->context = sfContext::createInstance($configuration);
        $conn = $this->getDatabaseConnection();

        $this->checkPathIsWritable($arguments['path']);

        $exportedCount = $this->exportDigitalObjectsToCsv($arguments['path'], $options);

        if (!$exportedCount) {
            $this->logSection($this->name, 'No digital objects exported.');
        } else {
            $this->logSection($this->name, sprintf('Exported %d digital objects.', $exportedCount));

            $this->log('The exported digital objects can be imported into AtoM using the digital object load task:');

            $this->log(sprintf('$ php symfony digitalobject:load %s/digital_objects.csv', $arguments['path']));
        }
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addCoreArgumentsAndOptions();

        $this->addOptions([
            new sfCommandOption(
                'single-slug',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Export digital objects related to a single fonds or collection based on slug'
            ),
            new sfCommandOption(
                'current-level-only',
                null,
                sfCommandOption::PARAMETER_NONE,
                "Don't export digital objects related to child descriptions (when using single-slug option)",
                null
            ),
        ]);
    }

    /**
     * Handle export of digital object data.
     *
     * @param string $exportPath path to export to (file or directory)
     * @param mixed  $options    export options
     *
     * @return int number of digital objects exported
     */
    private function exportDigitalObjectsToCsv($exportPath, $options)
    {
        // Handle option to export a single hierarchy or description's digital
        // objects only
        if (!empty($slug = $options['single-slug'])) {
            // Determine IDS of terms in hierarchy or for a single description
            $digitalObjectIds = $this->getDigitalObjectIdsInHierarchy(
                $slug,
                $options['current-level-only']
            );

            // Write export and return number of digital objects exported
            return $this->writeExport($exportPath, $options, $digitalObjectIds);
        }

        // Write export and return number of digital objects exported
        return $this->writeExport($exportPath, $options);
    }

    /**
     * Get IDs of digital objects from a description or hierarchy.
     *
     * @param string $slug             slug of description hierarchy
     * @param bool   $currentLevelOnly whether or not to descend hierarchy
     *
     * @return array IDs of digital objects
     */
    private function getDigitalObjectIdsInHierarchy($slug, $currentLevelOnly = false)
    {
        $digitalObjectIds = [];

        $io = QubitInformationObject::getBySlug($slug);

        if (null === $io) {
            throw new sfException('No information object found with that slug.');
        }

        if (!empty($io->digitalObjectsRelatedByobjectId[0])) {
            $digitalObjectIds[] = $io->digitalObjectsRelatedByobjectId[0]->id;
        }

        if (!$currentLevelOnly) {
            foreach ($io->getDescendantsForExport() as $descendant) {
                if (!empty($descendant->digitalObjectsRelatedByobjectId[0])) {
                    $digitalObjectIds[] = $descendant->digitalObjectsRelatedByobjectId[0]->id;
                }
            }
        }

        return $digitalObjectIds;
    }

    /**
     * Initialze export files.
     *
     * @param string $exportPath path to export
     *
     * @return resource CSV file resource
     */
    private function initializeExportFiles($exportPath)
    {
        // Create directory to put files into
        $fileSubDir = $exportPath.'/files';

        if (!is_dir($fileSubDir)) {
            mkdir($fileSubDir);
        }

        // Open CSV file
        $fp = fopen($exportPath.'/digital_objects.csv', 'w');

        // Add header row
        fputcsv($fp, ['slug', 'filename']);

        return $fp;
    }

    /**
     * Write export to filesyastem.
     *
     * @param string $exportPath       path to export
     * @param mixed  $options          export options
     * @param array  $digitalObjectIds array of IDs of digital objects to export
     *
     * @return int number of items items exported
     */
    private function writeExport($exportPath, $options, $digitalObjectIds = null)
    {
        $exportedCount = 0;

        // Create directory to put files into
        $fileSubDir = $exportPath.'/files';

        if (!empty($digitalObjectIds)) {
            $fp = $this->initializeExportFiles($exportPath);

            // Export select digital objects
            foreach ($digitalObjectIds as $doId) {
                $do = QubitDigitalObject::getById($doId);
                $this->exportDigitalObject($exportPath, $fp, $do);

                ++$exportedCount;
            }
        } elseif (!$options['single-slug']) {
            $headerWritten = false;

            // Export all master digital objects
            $criteria = new Criteria();

            $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::MASTER_ID);

            foreach (QubitDigitalObject::get($criteria) as $do) {
                // Now that it's clear there's something to export, write CSV header
                if (!$headerWritten) {
                    $fp = $this->initializeExportFiles($exportPath);

                    $headerWritten = true;
                }

                $this->exportDigitalObject($exportPath, $fp, $do);

                ++$exportedCount;
            }
        }

        fclose($fp);

        return $exportedCount;
    }

    /**
     * Export single digital object.
     *
     * @param string   $fileSubDir directory to export to
     * @param resource $fp         file pointer of CSV file
     * @param object   $do         digital object
     * @param object   $io         information object
     * @param mixed    $exportPath
     *
     * @return int number of items items exported
     */
    private function exportDigitalObject($exportPath, $fp, $do, $io = null)
    {
        if (empty($io)) {
            $io = QubitInformationObject::getById($do->objectId);
        }

        if (null !== $io) {
            // Assemble destination relative path based on digital object path
            $relativeFilePath = ltrim(ltrim($do->path.$do->name), '/');
            $newRelPath = $exportPath.'/files/'.$do->id;
            $newRelFilePath = $newRelPath.'/'.$do->name;

            // Create destination relative path
            if (!is_dir($newRelPath)) {
                mkdir($newRelPath);
            }

            // Copy master file to export directory and add row to CSV
            copy($relativeFilePath, $newRelFilePath);

            fputcsv($fp, [$io->slug, $newRelFilePath]);
        }
    }
}
