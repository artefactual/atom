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

    $this->object = QubitObject::getById($request->objectId);

    if (!isset($this->object))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->object, 'update'))
    {
      throw new sfException;
    }

    // Check if uploads are allowed
    if (!QubitDigitalObject::isUploadAllowed())
    {
      QubitAcl::forwardToSecureAction();
    }

    $repo = $this->object->getRepository(array('inherit' => true));

    if (isset($repo))
    {
      $uploadLimit = $repo->uploadLimit;
      if (0 < $uploadLimit)
      {
        $uploadLimit *= pow(10,9); // Convert to bytes
      }

      $diskUsage = $repo->getDiskUsage();
    }

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

      try
      {
        $file = Qubit::moveUploadFile($file);
      }
      catch (Exception $e)
      {
        $uploadFile = array('error' => $e->getMessage());

        continue;
      }

      // Temp file characteristics
      $tmpFilePath = $file['tmp_name'];
      $tmpFileName = basename($tmpFilePath);
      $tmpFileMimeType = QubitDigitalObject::deriveMimeType($tmpFileName);

      // Thumbnail name and path
      $thumbName = pathinfo('THUMB'. $tmpFileName, PATHINFO_FILENAME) .".jpg";
      $thumbPath = dirname($tmpFilePath) ."/". $thumbName;

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
        'md5sum' => md5_file($tmpFilePath),
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
