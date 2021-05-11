<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Jobs subclass for export related methods.
 *
 * @author     Mike G <mikeg@artefactual.com>
 */
class arExportJob extends arBaseJob
{
    // Log progress every n rows
    public const LOG_INTERVAL = 100;

    // Child class should set this if creating user downloads
    protected $downloadFileExtension;
    protected $zipFileDownload;
    protected $filenames = [];
    protected $itemsExported = 0;

    public function runJob($parameters)
    {
        $this->params = $parameters;

        $this->zipFileDownload = new arZipFileDownload($this->job->id, $this->downloadFileExtension);

        $tempPath = $this->zipFileDownload->createJobTempDir();

        // Export CSV to temp directory
        $this->info(
            $this->i18n->__(
                'Starting export to %1.',
                ['%1' => $tempPath]
            )
        );

        $this->doExport($tempPath);

        if (count($this->itemsExported) > 0) {
            $this->info($this->i18n->__(
                'Exported %1 records.',
                ['%1' => $this->itemsExported]
            ));

            $this->info($this->i18n->__(
                'Creating ZIP file %1.',
                ['%1' => $this->zipFileDownload->getDownloadFilePath()]
            ));

            // Create ZIP file and add metadata file(s) and digital objects
            $errors = $this->zipFileDownload->createZipForDownload($tempPath, $this->user->isAdministrator());

            if (!empty($errors)) {
                $this->error(
                    $this->i18n->__('Failed to create ZIP file.')
                    .' : '.implode(' : ', $errors)
                );

                return;
            }

            $this->job->downloadPath = $this->zipFileDownload->getDownloadRelativeFilePath();
            $this->info($this->i18n->__('Export and archiving complete.'));
        } else {
            $this->info($this->i18n->__('No relevant records were found to export.'));
        }

        $this->job->setStatusCompleted();
        $this->job->save();

        // Delete temp directory contents and directory
        sfToolkit::clearDirectory($tempPath);
        rmdir($tempPath);
    }

    /**
     * Copy a digital object to the temporary job directory for export.
     *
     * @param mixed  $resource the object to which the digital object is attached
     * @param string $tempDir  the temporary export job directory
     */
    protected function addDigitalObject($resource, $tempDir)
    {
        // Skip if the includeDigitalObjects option is not set, or it is disabled
        if (
            !isset($this->params['includeDigitalObjects'])
            || !$this->params['includeDigitalObjects']
        ) {
            return;
        }

        $digitalObject = $resource->getDigitalObject();

        // Skip this digital object if it doesn't exist in the database or export
        // is not authorized
        if (
            null === $digitalObject
            || !$this->allowDigitalObjectExport($resource, $digitalObject)
        ) {
            return false;
        }

        // Don't try to export an external digital object
        if (!$digitalObject->isLocalFile()) {
            $this->info($this->i18n->__(
                'Skipping external digital object "%1%"',
                ['%1%' => $digitalObject->getPath()]
            ));

            return false;
        }

        $filepath = $digitalObject->getAbsolutePath();

        if (!file_exists($filepath)) {
            $this->info($this->i18n->__(
                'Digital object "%1%" not found',
                ['%1%' => $filepath]
            ));

            return false;
        }

        $filename = $this->zipFileDownload->getUniqueFilename($filepath);
        $dest = $tempDir.DIRECTORY_SEPARATOR.$filename;

        if (!copy($filepath, $dest)) {
            $this->info($this->i18n->__(
                'Failed to copy digital object "%1%" to "%2%"',
                ['%1%' => $filepath, '%2%' => $dest]
            ));

            return false;
        }

        return true;
    }

    protected function allowDigitalObjectExport($resource, $digitalObject)
    {
        // Check that digital object has a URL, the current user is authorized to
        // access it, and a conditional copyright statement doesn't need to be
        // accepted
        if (
            $digitalObject->masterAccessibleViaUrl()
            && QubitAcl::check($resource, 'readMaster')
            && !$digitalObject->hasConditionalCopyright()
        ) {
            // Export is allowed
            return true;
        }

        return false;
    }

    /**
     * Log export progress every LOG_INTERVAL rows and clear Qubit class caches.
     */
    protected function logExportProgress()
    {
        if (0 == $this->itemsExported % self::LOG_INTERVAL) {
            $this->info(
                $this->i18n->__(
                    'Exported %1 items...',
                    ['%1' => $this->itemsExported]
                )
            );

            Qubit::clearClassCaches();
        }
    }
}
