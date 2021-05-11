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
 * A job to export a physical object holdings report as CSV data.
 */
class arPhysicalObjectCsvHoldingsReportJob extends arExportJob
{
    /**
     * @see arBaseJob::$requiredParameters
     */
    protected $downloadFileExtension = 'zip';
    protected $zipFileDownload;

    public function runJob($parameters)
    {
        // Indicate beginning of export and describe parameters provided
        $this->info($this->i18n->__('Starting physical storage holdings report CSV export.'));

        if (!empty($parameters['suppressEmpty'])) {
            $this->info($this->i18n->__('Omitting physical storage without holdings.'));
        }

        if (!empty($parameters['holdingType'])) {
            $this->info($this->i18n->__(
                'Including physical storage containing holding type: %1.',
                ['%1' => $parameters['holdingType']]
            ));
        }

        // Attempt export
        $this->zipFileDownload = new arZipFileDownload($this->job->id, $this->downloadFileExtension);
        $tempPath = $this->zipFileDownload->createJobTempDir();

        $exportFile = $tempPath.DIRECTORY_SEPARATOR.'holdings.csv';

        $report = new QubitPhysicalObjectCsvHoldingsReport($this->getReportOptions($parameters));
        $report->write($exportFile);

        // Compress CSV export files as a ZIP archive
        $this->info($this->i18n->__('Creating ZIP file %1.', ['%1' => $this->zipFileDownload->getDownloadFilePath()]));
        $errors = $this->zipFileDownload->createZipForDownload($tempPath, $this->user->isAdministrator());

        if (!empty($errors)) {
            $this->error($this->i18n->__('Failed to create ZIP file.').' : '.implode(' : ', $errors));

            return false;
        }

        // Mark job as complete
        $this->info($this->i18n->__('Export complete.'));
        $this->job->setStatusCompleted();
        $this->job->downloadPath = $this->zipFileDownload->getDownloadRelativeFilePath();
        $this->job->save();

        return true;
    }

    private function getReportOptions($parameters)
    {
        $options = [];

        $options['suppressEmpty'] = (!empty($parameters['suppressEmpty'])) ? $parameters['suppressEmpty'] : null;

        if (!empty($parameters['holdingType'])) {
            $options['holdingType'] = $parameters['holdingType'];
        }

        return $options;
    }
}
