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

class ApiAipsDownloadViewAction extends QubitApiAction
{
  /*
    Example:

    curl -v http://127.0.0.1:8001/api/aips/5d20ba4c-5d13-417f-912b-97f724ec726d/
      download?reason=Study&relative_path_to_file=objects/lion.svg

  */
  protected function get($request)
  {
    // get AIP data from ES to verify it exists and to log access
    $aip = QubitApiAip::getResults($request);

    // get configuration needed to access storage service
    $ssConfig = array();
    $ssEnvVars  = array(
      'ARCHIVEMATICA_SS_HOST' => '127.0.0.1',
      'ARCHIVEMATICA_SS_PORT' => '8000'
    );

    // determine configuration based on environment variable settings
    foreach($ssEnvVars as $var => $default)
    {
      // get Archivematica storage service host
      $value = getenv($var);

      if (!$value && !$default)
      {
        throw new QubitApiException($var + ' not configured', 500);
      }

      $ssConfig[$var] = ($value) ? $value : $default;
    }

    // assemble storage server URL
    $storageServiceUrl = 'http://'. $ssConfig['ARCHIVEMATICA_SS_HOST'];
    $storageServiceUrl .= ':'. $ssConfig['ARCHIVEMATICA_SS_PORT'];
    $aipUrl = $storageServiceUrl .'/api/v2/file';

    // determine filename of AIP via REST call to storage server
    $aipInfoUrl = $aipUrl .'/'. $request->uuid .'?format=json';
    $aipInfo = json_decode(file_get_contents($aipInfoUrl));
    $filename = basename($aipInfo->current_full_path);

    // formalate URL depending on whether a single file is being extracted
    $downloadUrl = $aipUrl .'/'. $request->uuid .'/';
    $downloadUrl .= ($request->relative_path_to_file) ? 'extract_file/' : 'download/';

    // if a single file is being extracted, augment with relative path to file
    if ($request->relative_path_to_file)
    {
      $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
      $relativePathToFile = $filenameWithoutExtension .'/data/' . $request->relative_path_to_file;
      $downloadUrl .= '?relative_path_to_file='. urlencode($relativePathToFile);
      $filename = basename($relativePathToFile);
    }

    // log access to AIP/file
    $this->logAccessAttempt($request, $aip['id']);

    if ($request->show_url)
    {
      // optionally return URL for debugging purposes
      return array('url' => $downloadUrl);
    } else {
      // proxy download
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
    // use property to augment AIP with access info
    $property = new QubitProperty;
    $property->objectId = $aipId;

    // access type can either by a full AIP or an AIP file
    $accessType = ($request->relative_path_to_file) ? 'aip_file_download' : 'aip_download';

    // create array representing access details
    $logMessageData = array();
    $logMessageData[] = date('Y-m-d H:i:s');
    $logMessageData[] = $this->getUser()->getUserID();
    $logMessageData[] = str_replace('|', ' ', $request->reason);
    $logMessageData[] = $request->relative_path_to_file;

    // save access details
    $property->setName($accessType);
    $property->setValue(implode('|', $logMessageData));
    $property->save();
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
    readfile($url);
    exit;
  }
}
