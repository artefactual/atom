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

class QubitApiStorageServiceClient
{
  function __construct($urlPath)
  {
    $this->setConfig();
  }

  private function setConfig()
  {
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

    $this->config = $ssConfig;
  }

  public function get($urlPath)
  {
    // Assemble storage server URL
    $storageServiceUrl = 'http://'. $this->config['ARCHIVEMATICA_SS_HOST'];
    $storageServiceUrl .= ':'. $this->config['ARCHIVEMATICA_SS_PORT'];
    $url = $storageServiceUrl .'/'. $urlPath;

    file_put_contents('/tmp/goat.txt', $url);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // allow redirects
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $result = curl_exec($ch);

    // handle possible errors
    if ($result === false)
    {
      $error = curl_error($ch);
      curl_close($ch);
      sfContext::getInstance()->getLogger()->error('Error getting storage service data: '. $error);
      sfContext::getInstance()->getLogger()->error('URL: '. $url);
      throw new QubitApiException('Error: '. $error, 500);
    }
    curl_close($ch);

    return $result;
  }
}
