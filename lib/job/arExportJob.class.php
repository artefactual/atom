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
  protected function createZipForDownload($path)
  {
    $errors = array();

    if (!is_writable($this->getJobsDownloadDirectory()))
    {
      return [$this->i18n->__('Cannot write to directory')];
    }

    $zip = new ZipArchive();

    if (!$zip->open(
      $this->getDownloadFilePath(), ZipArchive::CREATE | ZipArchive::OVERWRITE)
    ) {
      return [$this->i18n->__('Cannot create zip file')];
    }

    // Check if we need to include digital objects
    if (
      array_key_exists('includeDigitalObjects', $this->params)
      && $this->params['includeDigitalObjects']
    ) {
      $this->addDigitalObjects($zip, $errors);
    }

    // Add exported data (files)
    $this->addExportDataFiles($path, $zip, $errors);

    $zip->close();

    return $errors;
  }

  protected function addDigitalObjects(&$zip, &$errors)
  {
    // Keep track of digital object file names so we can append a bracketed
    // number if any are duplicated
    $fileNames = array();

    // Get permitted digital object ids (if any) and iterate
    foreach($this->getDigitalObjects() as $id)
    {
      $do = QubitDigitalObject::getById($id);

      if (null == $do)
      {
        continue;
      }

      $doPath = $do->getAbsolutePath();

      if (!file_exists($doPath))
      {
        continue;
      }

      $fileName = basename($doPath);

      if (!array_key_exists($fileName, $fileNames))
      {
        // Filename not used yet - add to tracker
        $fileNames[$fileName] = 0;
      }
      else
      {
        // Filename has been used - increment counter and add to filename
        $doPathInfo = pathinfo($doPath);
        $fileName = sprintf("%s_%s.%s", [
          $doPathInfo['filename'],
          $fileNames[$fileName]++,
          $doPathInfo['extension']
        ]);
      }

      try
      {
        $zip->addFile($doPath, $fileName);
      }
      catch (Exception $e)
      {
        if ($this->user->isAdministrator())
        {
          $errors[] = 'Exception: '.$e->getMessage();
        }
        else {
          $errors[] = $this->i18n->__(
            'Sorry, but there was an error locating a digital object (#%1%). ' .
            'This has prevented any further digital objects from being ' .
            'exported. Please contact an administrator.' ,
            array(
              '%1%' => $id
            )
          );
        }

        break;
      }
    }
  }

  /**
   * Return an array of digital object ids if any are attached to clipboard items
   * and current user has permission to view masters
   *
   * @return array
   */
  protected function getDigitalObjects()
  {
    // Prepare array for digital object ids
    $digitalObjects = array();

    // Process if export option is set and this is a description or actor export
    if (
      sfConfig::get('app_clipboard_export_digitalobjects_enabled', false)
      && (
        'informationObject' == $this->params['objectType']
        || 'actor' == $this->params['objectType']
      )
    ) {
      // Get clipboard objects
      $criteria = new Criteria;

      // Filter on clipboard slugs
      $criteria->add(QubitSlug::SLUG, $this->params['params']['slugs'],
        Criteria::IN);

      switch ($this->params['objectType'])
      {
        case 'informationObject':
          $criteria->addJoin(QubitInformationObject::ID, QubitSlug::OBJECT_ID);

          // Hide drafts if necessary
          if($this->params['public'])
          {
            $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
          }
          $items = QubitInformationObject::get($criteria);

          break;

        case 'actor':
          $criteria->addJoin(QubitActor::ID, QubitSlug::OBJECT_ID);
          $items = QubitActor::get($criteria);

          break;
      }

      // Iterate filtered clipboard objects
      foreach ($items as $item)
      {
        $a = $item->digitalObjectsRelatedByobjectId;

        // Look for digital objects attached to each clipboard item
        if (0 != count($a))
        {
          // Get master object
          $digitalObject = $a[0];

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
                && QubitAcl::check($item, 'read')
              )
              || (
                'informationObject' == $this->params['objectType']
                && QubitAcl::check($item, 'readMaster')
                && QubitGrantedRight::checkPremis($item->id, 'readMaster')
                && !$digitalObject->hasConditionalCopyright()
              )
            )
          )
          {
            // Add master image id to array
            $digitalObjects[] = $digitalObject->id;
          }
        }
      }
    }

    return $digitalObjects;
  }

  protected function addExportDataFiles($path, &$zip, &$errors)
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
}
