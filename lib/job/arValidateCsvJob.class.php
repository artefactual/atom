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
 * Job worker for CSV Validation requests initiated from the WebUI.
 */
class arValidateCsvJob extends arBaseJob
{
    protected $zipFileDownload;
    protected $downloadFileExtension = 'zip';
    protected $verboseReportContents;

    /**
     * @see arBaseJob::$requiredParameters
     *
     * @param mixed $parameters
     */
    public function runJob($parameters)
    {
        if (!isset($parameters['file'])) {
            $this->job->setStatusError($this->i18n->__('Validation file name not set.'));
            $this->job->save();

            return false;
        }

        $this->info($this->i18n->__('Validating file: %1.', ['%1' => $parameters['file']['name']]));

        // Run CsvValidator and output short report to log panel.
        $validationResultString = $this->runCsvValidator($this->context, $parameters);
        $this->info($validationResultString);

        // Attempt export of verbose report.
        $this->zipFileDownload = new arZipFileDownload($this->job->id, $this->downloadFileExtension);
        $tempPath = $this->zipFileDownload->createJobTempDir();

        // Write verbose report contents to tempPath.
        $exportFile = $tempPath.DIRECTORY_SEPARATOR.'validation_results.txt';
        file_put_contents($exportFile, $this->verboseReportContents);

        // Compress CSV export files as a ZIP archive.
        $this->info($this->i18n->__(
            'Creating ZIP file %1.',
            ['%1' => $this->zipFileDownload->getDownloadFilePath()]
        ));

        // Create ZIP file.
        $errors = $this->zipFileDownload->createZipForDownload($tempPath, $this->user->isAdministrator());

        if (!empty($errors)) {
            $this->error(
                $this->i18n->__('Failed to create ZIP file.')
                .' : '.implode(' : ', $errors)
            );

            return;
        }

        $this->job->downloadPath = $this->zipFileDownload->getDownloadRelativeFilePath();
        $this->job->setStatusCompleted();
        $this->job->save();

        return true;
    }

    public function runCsvValidator($context, array $options = [])
    {
        $file = $options['file'];

        $validatorOptions = self::mapImportTypeToClassName(self::setOptions($options));

        $validator = new CsvImportValidator($context, null, $validatorOptions);
        $validator->setFilenames([$file['name'] => $file['tmp_name']]);
        $results = $validator->validate();

        $this->verboseReportContents = CsvValidatorResultCollection::renderResultsAsText($results, true);

        // Return short report.
        return CsvValidatorResultCollection::renderResultsAsText($results, false);
    }

    public static function setOptions($options = [])
    {
        $opts = [];

        $keymap = [
            'source' => 'source',
            'objectType' => 'className',
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

    public static function mapImportTypeToClassName($options)
    {
        $importOjectClassNames = [
            'informationObject' => 'QubitInformationObject',
            'accession' => 'QubitAccession',
            'authorityRecord' => 'QubitActor',
            'event' => 'QubitEvent',
            'repository' => 'QubitRepository',
            'authorityRecordRelationship' => 'QubitRelation-actor',
        ];

        $options['className'] = $importOjectClassNames[$options['className']];

        return $options;
    }
}
