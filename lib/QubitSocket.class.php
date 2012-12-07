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
 * Socket functions
 *
 * @package    AccesstoMemory
 * @subpackage lib
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class QubitSocket
{
  var $sock;
  var $url;
  var $err_nbr;
  var $err_str;
  var $headers;
  var $status;
  var $type;

  var $protocol;
  var $host;
  var $port;
  var $path;
  var $script;
  var $query_str;
  var $error;
  var $attempts = 0;
  var $max_attempts = 3;
  var $sleep_between_attempts = 10;
  var $max_redirects = 10;
  var $agent = 'Access to Memory (AtoM)';
  var $from;

  /**
  * Public function QubitSocket, socket creator
  *
  * @param string $url, url to the resource requested
  * @param string $agent, agent
  * @param string $from, email of the connection originator
  * @param string $timeout, time to timeout of the connection
  * @return bool true for success, false for failure
  */
  public function QubitSocket($url='', $agent='Not Specified', $from='admin@somewhere .com', $timeout='1200')
  {
    //error_reporting(E_ALL);
    //ini_set('error_reporting', E_ALL);
    $this->sock = curl_init();
    $this->from = $from;
    $this->agent = $agent;
    $this->url = $url;
    $HEADER = array('FROM: '.$this->from.']');
    $HEADER[] = 'Accept-Charset: utf-8';
    curl_setopt($this->sock, CURLOPT_USERAGENT, $this->agent);
    curl_setopt($this->sock, CURLOPT_HTTPHEADER, $HEADER);
    curl_setopt($this->sock, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($this->sock, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($this->sock, CURLOPT_MAXREDIRS, $this->max_redirects);
    curl_setopt($this->sock, CURLOPT_SSL_VERIFYPEER, false);
  }

  public function get_headers()
  {
    return $this->headers;
  }

  public function get_status()
  {
    return $this->status;
  }

  public function get_err_nbr()
  {
    return $this->err_nbr;
  }

  public function get_err_str()
  {
    return $this->err_str;
  }

  public function get_error()
  {
    return $this->error;
  }

  /**
  * Public function get_data, data getter
  *
  * @param string $attribs attributes of the link
  * @return bool true for success, false for failure
  */
  public function get_data()
  {
    curl_setopt($this->sock, CURLOPT_URL, $this->url);
    curl_setopt($this->sock, CURLOPT_RETURNTRANSFER, true);
    $return_value = false;
    $requestCount = 0;
    while ($return_value == false && $requestCount < $this->max_attempts)
    {
      $return_value = curl_exec($this->sock);
      $requestCount++;
      //Pause between attempts
      if (!$return_value)
      {
        sleep($this->sleep_between_attempts);
      }
    }

    //Check status of request
    $this->status = curl_getinfo($this->sock, CURLINFO_HTTP_CODE);
    $this->type = curl_getinfo($this->sock, CURLINFO_CONTENT_TYPE);

    if ($this->status != 200)
    {
      return false;
    }

    curl_close($this->sock);
    return $return_value;
  }

  /**
  * Public function set_query_str, function used
  * to return an attribute string for the URL, based on
  * array of paired attributes/value
  *
  * @param string $key_value key values array for the string
  * @return string query string properly formated for HTTP
  */
  public function set_query_str($key_value='')
  {
    $query_str = '';
    if (is_array($key_value))
    {
      foreach ($key_value as $key => $value)
      {
        $query_str .= $key.'='.urlencode($value).'&';
      }
      $query_str = substr($query_str, 0, -1);
    }
    return $query_str;
  }

  /**
  * Public function get_file, file getter
  *
  * @param string $file filename for the target
  * @return bool true for success, false for failure
  */
  public function get_file($file='')
  {
    $file_handle = fopen($file, 'w');
    curl_setopt($this->sock, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($this->sock, CURLOPT_MAXREDIRS, $this->max_redirects);
    curl_setopt($this->sock, CURLOPT_URL, $this->url);
    curl_setopt($this->sock, CURLOPT_FILE, $file_handle);
    curl_setopt($this->sock, CURLOPT_SSL_VERIFYPEER, false);

    curl_exec($this->sock);

    $retour = curl_getinfo($this->sock, CURLINFO_HEADER_OUT);

    $errorCode = curl_getinfo($this->sock, CURLINFO_HTTP_CODE);

    // In case of error, return false and delete the created file.

    if (((int) ($errorCode / 100)) != 2)
    {
      unlink( $file );
      return false;
    }

    curl_close($this->sock);
    fclose($file_handle);
    return true;
  }

  /**
  * Public function ftp_move, uploads a file through ftp
  *
  * @param string $filePath file path of file to move
  * @param string $ftpPath ftp path of upload directory
  * @return bool true for success, false for failure
  */
  public function ftp_move($filePath, $ftpPath, $ftpUsername, $ftpPassword)
  {
    $this->sock = curl_init();
    $fileStream = fopen($filePath, 'r');
    curl_setopt($this->sock, CURLOPT_UPLOAD, 1);
    curl_setopt($this->sock, CURLOPT_INFILE, $fileStream);
    curl_setopt($this->sock, CURLOPT_USERPWD, $ftpUsername.':'.$ftpPassword);
    curl_setopt($this->sock, CURLOPT_INFILESIZE, filesize($filePath));
    curl_setopt($this->sock, CURLOPT_URL, $ftpPath);
    curl_exec($this->sock);
    fclose($fileStream);
    $error_no = curl_errno($this->sock);

    //Check status of request
    $this->status = curl_getinfo($this->sock, CURLINFO_HTTP_CODE);
    $this->type = curl_getinfo($this->sock, CURLINFO_CONTENT_TYPE);

    if ($this->status != 200)
    {
      return false;
    }

    curl_close($this->sock);
    unlink($filePath);
    return ($error_no == 0); //true if communication okay
  }
}
