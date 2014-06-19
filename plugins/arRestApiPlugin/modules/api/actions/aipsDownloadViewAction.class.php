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
 * Example:
 *
 *  $ curl -v \
 *    http://HOSTNAME:8001/api/aips/UUID/download?reason=REASON&file_id=FILE_INFORMATION_OBJECT_ID
 *
 */
class ApiAipsDownloadViewAction extends QubitApiAction
{
  protected function get($request)
  {
    if (!isset($request->reason))
    {
      throw new QubitApiBadRequestException('Missing parameter: reason');
    }

    if (strlen($request->reason) < 10)
    {
      throw new QubitApiBadRequestException('Parameter reason is not long enough');
    }

    // Get AIP data from ES to verify it exists and to log access
    $aip = QubitApiAip::getResults($request);

    // Get configuration needed to access storage service
    $ssConfig = array();
    $ssEnvVars = array(
      'ARCHIVEMATICA_SS_HOST' => '127.0.0.1',
      'ARCHIVEMATICA_SS_PORT' => '8000'
    );

    // Determine configuration based on environment variable settings
    foreach ($ssEnvVars as $var => $default)
    {
      // Get Archivematica storage service host
      $value = getenv($var);

      if (!$value && !$default)
      {
        throw new QubitApiException($var + ' not configured', 500);
      }

      $ssConfig[$var] = ($value) ? $value : $default;
    }

    // Assemble storage server URL
    $storageServiceUrl = 'http://'. $ssConfig['ARCHIVEMATICA_SS_HOST'];
    $storageServiceUrl .= ':'. $ssConfig['ARCHIVEMATICA_SS_PORT'];
    $aipUrl = $storageServiceUrl .'/api/v2/file';

    // Determine filename of AIP via REST call to storage server
    $aipInfoUrl = $aipUrl .'/'. $request->uuid .'?format=json';
    $ch = curl_init($aipInfoUrl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // storage server redirects
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $aipInfoJson = curl_exec($ch);

    // handle possible errors
    if ($aipInfoJson === false)
    {
      $error = curl_error($ch);
      curl_close($ch);

      sfContext::getInstance()->getLogger()->err('METSArchivematicaDIP - Error getting storage service data: '. $error);
      sfContext::getInstance()->getLogger()->err('METSArchivematicaDIP - URL: '. $aipInfoUrl);

      throw new QubitApiException('Error: '. $error, 500);
    }
    curl_close($ch);

    $aipInfo = json_decode($aipInfoJson);
    $filename = basename($aipInfo->current_full_path);

    // Formalate URL depending on whether a single file is being extracted
    $downloadUrl = $aipUrl .'/'. $request->uuid .'/';
    $downloadUrl .= ($request->file_id) ? 'extract_file/' : 'download/';

    // If a single file is being extracted, augment with relative path to file
    if (isset($request->file_id))
    {
      // Retrieve relative path to file
      $criteria = new Criteria;
      $criteria->add(QubitProperty::NAME, 'original_relative_path_within_aip');
      $criteria->add(QubitProperty::OBJECT_ID, $request->file_id);
      $property = QubitProperty::getOne($criteria);

      $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
      $relativePathToFile = $filenameWithoutExtension .'/data/' . $property->value;
      $downloadUrl .= '?relative_path_to_file='. urlencode($relativePathToFile);
      $filename = basename($relativePathToFile);
    }

    // Log access to AIP/file
    $this->logAccessAttempt($request, $aip['id']);

    if ($request->show_url)
    {
      // Optionally return URL for debugging purposes
      return array('url' => $downloadUrl);
    }
    else
    {
      // Proxy download
      $this->proxyDownload($downloadUrl, $filename);
    }
  }

  /*
    Data is stored in property_i18n table as character-delimited (|) fields
    (data, user ID, reason, path to file)

    Example log retrieval for AIP:

    SELECT * FROM property p INNER JOIN property_i18n pi ON p.id=pi.id WHERE p.object_id=457 ORDER BY pi.value;
  */
  protected function logAccessAttempt($request, $aipId)
  {
    // Log access to AIP
    $logEntry = new QubitAccessLog;
    $logEntry->objectId = ($request->file_id) ? $request->file_id : $aipId;
    $logEntry->userId = $this->getUser()->getUserID();

    // Access type can either by a full AIP or an AIP file
    $accessTypeId = ($request->file_id)
      ? QubitTerm::ACCESS_LOG_AIP_FILE_DOWNLOAD_ENTRY : QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY;
    $logEntry->accessTypeId = $accessTypeId;
    $logEntry->reason = $request->reason;
    $logEntry->accessDate = date('Y-m-d H:i:s');

    $logEntry->save();
  }

  protected function proxyDownload($url, $filename)
  {
    header('Content-Description: File Transfer');
    header('Content-Disposition: attachment; filename='.$filename);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    ob_clean();
    flush();

    // Proxy file from storage server
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // storage server redirects
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $response = curl_exec($ch);

    // handle possible errors
    if ($response === false)
    {
      $error = curl_error($ch);
      curl_close($ch);
      sfContext::getInstance()->getLogger()->error('METSArchivematicaDIP - Error proxying file from storage service data: '. $error);
      sfContext::getInstance()->getLogger()->error('METSArchivematicaDIP - URL: '. $aipInfoUrl);
      throw new QubitApiException('Error: '. $error, 500);
    }
    curl_close($ch);

    exit;
  }
}
