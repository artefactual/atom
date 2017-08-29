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

class DigitalObjectUploadAction extends sfAction
{
  public function execute($request)
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    $uploadLimt = -1;
    $diskUsage = 0;
    $uploadFiles = array();
    $warning = null;

    $this->informationObject = QubitInformationObject::getById($request->informationObjectId);

    if (!isset($this->informationObject))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->informationObject, 'update'))
    {
      throw new sfException;
    }

    // Check if uploads are allowed
    if (!QubitDigitalObject::isUploadAllowed())
    {
      QubitAcl::forwardToSecureAction();
    }

    $repo = $this->informationObject->getRepository(array('inherit' => true));

    if (isset($repo))
    {
      $uploadLimit = $repo->uploadLimit;
      if (0 < $uploadLimit)
      {
        $uploadLimit *= pow(10,9); // Convert to bytes
      }

      $diskUsage = $repo->getDiskUsage();
    }

    Qubit::createUploadDirsIfNeeded();
    $tmpDir = sfConfig::get('sf_upload_dir').'/tmp';

    foreach ($_FILES as $file)
    {
      if (null != $repo && 0 <= $uploadLimit && $uploadLimit < $diskUsage + $file['size'])
      {
        $uploadFiles = array('error' => $this->context->i18n->__(
          '%1% upload limit of %2% GB exceeded for %3%', array(
            '%1%' => sfConfig::get('app_ui_label_digitalobject'),
            '%2%' => $repo->uploadLimit,
            '%4%' => $this->context->routing->generate(null, array($repo, 'module' => 'repository')),
            '%3%' => $repo->__toString())
        ));

        continue;
      }

      // Get file extension
      $extension = substr($file['name'], strrpos($file['name'], '.'));

      // Get a unique file name (to avoid clashing file names)
      do
      {
        $uniqueString = substr(md5(time().$file['name']), 0, 8);
        $tmpFileName = "TMP$uniqueString$extension";
        $tmpFilePath = "$tmpDir/$tmpFileName";
      }
      while (file_exists($tmpFilePath));

      // Thumbnail name
      $thumbName = "THB$uniqueString.jpg";
      $thumbPath = "$tmpDir/$thumbName";

      // Move file to web/uploads/tmp directory
      if (!move_uploaded_file($file['tmp_name'], $tmpFilePath))
      {
        $errorMessage = $this->context->i18n->__('File %1% could not be moved to %2%', array('%1%' => $file['name'], '%2%' => $tmpDir));
        $uploadFiles = array('error' => $errorMessage);

        continue;
      }

      $tmpFileMd5sum = md5_file($tmpFilePath);
      $tmpFileMimeType = QubitDigitalObject::deriveMimeType($tmpFileName);

      if ($canThumbnail = QubitDigitalObject::canThumbnailMimeType($tmpFileMimeType) || QubitDigitalObject::isVideoFile($tmpFilePath))
      {
        if (QubitDigitalObject::isImageFile($tmpFilePath) || 'application/pdf' == $tmpFileMimeType)
        {
          $resizedObject = QubitDigitalObject::resizeImage($tmpFilePath, 150, 150);
        }
        else if (QubitDigitalObject::isVideoFile($tmpFilePath))
        {
          $resizedObject = QubitDigitalObject::createThumbnailFromVideo($tmpFilePath, 150, 150);
        }

        if (0 < strlen($resizedObject))
        {
          file_put_contents($thumbPath, $resizedObject);
          chmod($thumbPath, 0644);
        }

        // Show a warning message if object couldn't be thumbnailed when it is
        // supposed to be possible
        if (!file_exists($thumbPath) && 0 >= filesize($thumbPath))
        {
          $warning = $this->context->i18n->__('File %1% could not be thumbnailed', array('%1%' => $file['name']));
        }
      }
      else
      {
        $thumbName = '../../images/'.QubitDigitalObject::getGenericIconPath($tmpFileMimeType, QubitTerm::THUMBNAIL_ID);
      }

      $uploadFiles = array(
        'canThumbnail' => $canThumbnail,
        'name' => $file['name'],
        'md5sum' => $tmpFileMd5sum,
        'size' => hr_filesize($file['size']),
        'thumb' => $thumbName,
        'tmpName' => $tmpFileName,
        'warning' => $warning);

      // Keep running total of disk usage
      $diskUsage += $file['size'];
    }

    // Pass file data back to caller for processing on form submit
    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    return $this->renderText(json_encode($uploadFiles));
  }
}
