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
 * Jobs subclass for export related methods
 *
 * @package    AccesstoMemory
 * @subpackage jobs
 * @author     Mike G <mikeg@artefactual.com>
 */

class arExportJob extends arBaseJob
{
  // Child class should set this if creating user downloads
  protected $downloadFileExtension = null;

  protected $filenames = [];
  protected $itemsExported = 0;

  public function runJob($parameters)
  {
    $this->params = $parameters;

    $tempPath = $this->createJobTempDir();

    // Export CSV to temp directory
    $this->info($this->i18n->__(
      'Starting export to %1.', array('%1' => $tempPath))
    );

    $this->doExport($tempPath);

    if (count($this->itemsExported) > 0)
    {
      $this->info($this->i18n->__(
        'Exported %1 records.', array('%1' => $this->itemsExported)
      ));

      $this->info($this->i18n->__(
        'Creating ZIP file %1.',
        array('%1' => $this->getDownloadFilePath())
      ));

      // Create ZIP file and add metadata file(s) and digital objects
      $errors = $this->createZipForDownload($tempPath);

      if (!empty($errors))
      {
        $this->error(
          $this->i18n->__('Failed to create ZIP file.') . ' : '
          . implode(' : ', $errors)
        );

        return;
      }

      $this->job->downloadPath = $this->getDownloadRelativeFilePath();
      $this->info($this->i18n->__('Export and archiving complete.'));
    }
    else
    {
      $this->info($this->i18n->__('No relevant records were found to export.'));
    }

    $this->job->setStatusCompleted();
    $this->job->save();

    // Delete temp directory contents and directory
    sfToolkit::clearDirectory($tempPath);
    rmdir($tempPath);
  }

  /**
   * Return the job's download file path (or null if job doesn't create
   * a download).
   *
   * @return string  file path
   */
  public function getDownloadFilePath()
  {
    $downloadFilePath = null;

    if (!is_null($this->downloadFileExtension))
    {
      $downloadFilePath = $this->getJobsDownloadDirectory()
        . DIRECTORY_SEPARATOR
        . $this->getJobDownloadFilename();
    }

    return $downloadFilePath;
  }

  /**
   * Return the job's download file's relative path (or null if job doesn't
   * create a download).
   *
   * @return string  file path
   */
  public function getDownloadRelativeFilePath()
  {
    $downloadRelativeFilePath = null;

    if (!is_null($this->downloadFileExtension))
    {
      $relativeBaseDir = 'downloads' . DIRECTORY_SEPARATOR . 'jobs';
      $downloadRelativeFilePath = $relativeBaseDir . DIRECTORY_SEPARATOR
        . $this->getJobDownloadFilename();
    }

    return $downloadRelativeFilePath;
  }

  /**
   * Get the jobs download directory, a subdirectory of main AtoM downloads
   * directory
   *
   * @return string  directory path
   */
  public function getJobsDownloadDirectory()
  {
    $path = sfConfig::get('sf_web_dir') . DIRECTORY_SEPARATOR . 'downloads'
      . DIRECTORY_SEPARATOR . 'jobs';

    // Create the "downloads/jobs" directory if it doesn't exist already
    if (!is_dir($path))
    {
      mkdir($path, 0755, true);
    }

    return $path;
  }

  private function getJobDownloadFilename()
  {
    return md5($this->job->id) .'.'. $this->downloadFileExtension;
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
   * @return string  Temporary directory path
   */
  protected function createJobTempDir()
  {
    $name = md5(
      sfConfig::get('sf_root_dir') .
      sfConfig::get('app_workers_key', '') .
      $this->job->id .
      date_timestamp_get()
    );
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $name;
    mkdir($path);

    return $path;
  }

  /**
   * Create ZIP file from exported files
   *
   * @param string   Path of file to write CSV data to
   * @param boolean  Optional: Whether to include digital objects
   *
   * @return array   Error messages
   */
  protected function createZipForDownload($tempDir)
  {
    $errors = array();

    if (!is_writable($this->getJobsDownloadDirectory()))
    {
      return [$this->i18n->__('Cannot write to directory')];
    }

    $zip = new ZipArchive();

    if (!$zip->open(
      $this->getDownloadFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE
    ))
    {
      return [$this->i18n->__('Cannot create zip file')];
    }

    // Add exported files
    $this->addFilesToZip($tempDir, $zip, $errors);

    $zip->close();

    return $errors;
  }

  protected function addFilesToZip($path, &$zip, &$errors)
  {
    foreach (scandir($path) as $file)
    {
      if (is_dir($file))
      {
        continue;
      }

      try
      {
        $zip->addFile($path . DIRECTORY_SEPARATOR . $file, $file);
      }
      catch (Exception $e)
      {
        if ($this->user->isAdministrator())
        {
          $errors[] = 'Exception: '.$e->getMessage();
        }
        else
        {
          $errors[] = $this->i18n->__('Sorry, but there was an error retrieving
            a data file. This has stopped the export process. Please contact an
            administrator.');
        }

        break;
      }
    }
  }

  /**
   * Copy a digital object to the temporary job directory for export
   *
   * @param mixed $resource the object to which the digital object is attached
   * @param string $tempDir the temporary export job directory
   */
  protected function addDigitalObject($resource, $tempDir)
  {
    // Skip if the includeDigitalObjects option is not set, or it is disabled
    if (
      !isset($this->params['includeDigitalObjects'])
      || !$this->params['includeDigitalObjects']
    )
    {
      return;
    }

    $digitalObject = $resource->getDigitalObject();

    // Skip this digital object if it doesn't exist in the database or export
    // is not authorized
    if (
      null === $digitalObject
      || !$this->allowDigitalObjectExport($resource, $digitalObject)
    )
    {
      return false;
    }

    // Don't try to export an external digital object
    if (!$digitalObject->isLocalFile())
    {
      $this->info($this->i18n->__(
        'Skipping external digital object "%1%"',
        ['%1%' => $digitalObject->getPath()]
      ));

      return false;
    }

    $filepath = $digitalObject->getAbsolutePath();

    if (!file_exists($filepath))
    {
      $this->info($this->i18n->__(
        'Digital object "%1%" not found',
        ['%1%' => $filepath]
      ));

      return false;
    }

    $filename = $this->getUniqueFilename($filepath);
    $dest = $tempDir . DIRECTORY_SEPARATOR . $filename;

    if (!copy($filepath, $dest))
    {
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
    // If we need to add in check for images only, then use:
    // $digitalObject->isImage() or
    // $digitalObject->isWebCompatibleImageFormat()
    // ----------
    // Do appropriate ACL check(s). Master copy of text objects are always
    // allowed for reading. QubitActor does not have a ACL check for
    // readMaster - so only enable for authenticated users.
    if (
      $digitalObject->masterAccessibleViaUrl()
      && (
        QubitTerm::TEXT_ID == $digitalObject->mediaTypeId
        || (
          'actor' == $this->params['objectType']
          && $this->user->isAuthenticated()
          && QubitAcl::check($resource, 'read')
        ) || (
          'informationObject' == $this->params['objectType']
          && QubitAcl::check($resource, 'readMaster')
          && QubitGrantedRight::checkPremis($resource->id, 'readMaster')
          && !$digitalObject->hasConditionalCopyright()
        )
      )
    )
    {
      // Export is allowed
      return true;
    }

    return false;
  }

  protected function getUniqueFilename($filepath)
  {
    $filename = basename($filepath);

    if (!isset($this->filenames[$filename]))
    {
      // Filename not used yet - add to tracker
      $this->filenames[$filename] = 0;

      return $filename;
    }

    // Filename has been used - increment counter and append value to filename
    $pathinfo = pathinfo($filename);

    return sprintf(
      "%s_%s.%s",
      $pathinfo['filename'],
      $this->filenames[$filename]++,
      $pathinfo['extension']
    );
  }
}
