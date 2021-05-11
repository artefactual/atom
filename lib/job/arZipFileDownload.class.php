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
 * Class containing functions needed for creating a downloadable zip file.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class arZipFileDownload
{
    protected $jobId;
    protected $downloadFileExtension;
    protected $i18n;

    public function __construct($jobId, $downloadFileExtension)
    {
        $context = sfContext::getInstance();
        $this->i18n = $context->i18n;

        $this->jobId = $jobId;

        $this->downloadFileExtension = $downloadFileExtension;
    }

    /**
     * Create job temporary directory where the files will be added before
     * they are compressed and added to the downloads folder. Use a MD5 hash
     * created from instance info, job id and the current Epoch time to avoid
     * collisions when multiple AtoM instances are available on the same machine
     * and in instances where the database is regenerated from another dump (like
     * it's done in sites with public and private instances), where the job id
     * could be repeated, adding the export results to an existing export folder.
     *
     * @return string Temporary directory path
     */
    public function createJobTempDir()
    {
        $name = md5(
            sfConfig::get('sf_root_dir')
            .sfConfig::get('app_workers_key', '')
            .$this->jobId
            .date_timestamp_get()
        );
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name;
        mkdir($path);

        return $path;
    }

    /**
     * Return the job's download file path (or null if job doesn't create
     * a download).
     *
     * @return string file path
     */
    public function getDownloadFilePath()
    {
        $downloadFilePath = null;

        if (!is_null($this->downloadFileExtension)) {
            $downloadFilePath = $this->getJobsDownloadDirectory()
                .DIRECTORY_SEPARATOR
                .$this->getJobDownloadFilename();
        }

        return $downloadFilePath;
    }

    /**
     * Get the jobs download directory, a subdirectory of main AtoM downloads
     * directory.
     *
     * @return string directory path
     */
    public function getJobsDownloadDirectory()
    {
        $path = sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'downloads'
            .DIRECTORY_SEPARATOR.'jobs';

        // Create the "downloads/jobs" directory if it doesn't exist already
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * Create ZIP file from exported files.
     *
     * @param string   Path of file to write CSV data to
     * @param bool  Optional: Whether to include digital objects
     * @param mixed $tempDir
     * @param mixed $userIsAdministrator
     *
     * @return array Error messages
     */
    public function createZipForDownload($tempDir, $userIsAdministrator)
    {
        $errors = [];

        if (!is_writable($this->getJobsDownloadDirectory())) {
            return [$this->i18n->__('Cannot write to directory')];
        }

        $zip = new ZipArchive();

        if (
            !$zip->open(
                $this->getDownloadFilePath(),
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            )
        ) {
            return [$this->i18n->__('Cannot create zip file')];
        }

        // Add exported files
        $this->addFilesToZip($tempDir, $zip, $errors, $userIsAdministrator);

        $zip->close();

        return $errors;
    }

    /**
     * Return the job's download file's relative path (or null if job doesn't
     * create a download).
     *
     * @return string file path
     */
    public function getDownloadRelativeFilePath()
    {
        $downloadRelativeFilePath = null;

        if (!is_null($this->downloadFileExtension)) {
            $relativeBaseDir = 'downloads'.DIRECTORY_SEPARATOR.'jobs';
            $downloadRelativeFilePath = $relativeBaseDir.DIRECTORY_SEPARATOR
                .$this->getJobDownloadFilename();
        }

        return $downloadRelativeFilePath;
    }

    public function getUniqueFilename($filepath)
    {
        $filename = basename($filepath);

        if (!isset($this->filenames[$filename])) {
            // Filename not used yet - add to tracker
            $this->filenames[$filename] = 0;

            return $filename;
        }

        // Filename has been used - increment counter and append value to filename
        $pathinfo = pathinfo($filename);

        return sprintf(
            '%s_%s.%s',
            $pathinfo['filename'],
            $this->filenames[$filename]++,
            $pathinfo['extension']
        );
    }

    protected function addFilesToZip($path, &$zip, &$errors, $userIsAdministrator)
    {
        foreach (scandir($path) as $file) {
            if (is_dir($file)) {
                continue;
            }

            try {
                $zip->addFile($path.DIRECTORY_SEPARATOR.$file, $file);
            } catch (Exception $e) {
                if ($userIsAdministrator) {
                    $errors[] = 'Exception: '.$e->getMessage();
                } else {
                    $errors[] = $this->i18n->__(
                        'Sorry, but there was an error retrieving'
                        .' a data file. This has stopped the export process.'
                        .' Please contact an administrator.'
                    );
                }

                break;
            }
        }
    }

    private function getJobDownloadFilename()
    {
        return md5($this->jobId).'.'.$this->downloadFileExtension;
    }
}
