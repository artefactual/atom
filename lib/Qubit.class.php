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

class Qubit
{
  public static function pathInfo($url)
  {
    // Other options to get path info from URL include parse_url() or building
    // an exact regex from the BNF in RFC 1738.  Note however that our only
    // goal is to get path info from valid URL, which the following simple
    // regex should accomplish.  Slash characters don't occur in the scheme,
    // user, password, host, or port component of valid URL
    return preg_replace('/^(?:[^:]+:\/\/[^\/]+)?'.preg_quote(sfContext::getInstance()->request->getPathInfoPrefix(), '/').'/', null, $url);
  }

  // Alternative to format_date() symfony helper
  //
  // format_date() is consistent with e.g. format_country() and supports many
  // different date formats, e.g. W3C for EAC, but doesn't support zero day or
  // zero month
  //
  // Qubit::renderDate() currently supports only one date format, but does
  // support zero day or zero month
  public static function renderDate($value)
  {
    // Natural number - This will not strip leading zeroes.
    // Note that this will NOT add leading zeroes if they are not present.
    // e.g. 2016-12-08 will be displayed as 2016-12-08
    //      2016-12-8 will be displayed as 2016-12-8
    $natural = '(0*[1-9]\d*)';

    // Zero and separator, trim leading zeros
    $zero = '0*(0[-\/])';

    // Trim trailing separator and zero
    $trim = '[-\/]0+';

    // Trim trailing zero day and month
    $pattern = "/0*(\d+)(?:([-\/])$natural(?:([-\/])$natural
      |$trim)
      |([-\/])$zero$natural
      |$trim$trim)/x";

    $replacement = '$1$2$3$4$5$6$7$8';

    return preg_replace($pattern, $replacement, $value);
  }

  public static function renderDateStartEnd($date, $start, $end)
  {
    if (!empty($date))
    {
      return $date;
    }

    if (!empty($start) || !empty($end))
    {
      $start = Qubit::renderDate($start);
      $start = empty($start) ? '?' : $start;

      $end = Qubit::renderDate($end);
      $end = empty($end) ? '?' : $end;

      if ($end == $start)
      {
        return $start;
      }

      return "$start - $end";
    }
  }

  public static function saveTemporaryFile($name, $contents)
  {
    // Set temporary directory path
    $tmpDir = sys_get_temp_dir();

    // Create temporary directory unless exists
    if (!is_writable($tmpDir))
    {
      mkdir($tmpDir);
      chmod($tmpDir, 0775);
    }

    $pathInfo = pathinfo($name);
    $extension = $pathInfo['extension'];

    // Get a unique file name (to avoid clashing file names)
    $tmpFileName = null;
    while (file_exists($tmpFileName) || null == $tmpFileName)
    {
      $uniqueString = substr(md5(time()), 0, 8);
      $tmpFileName = $tmpDir.'/QUBIT'.$uniqueString.'.'.$extension;
    }

    return false != file_put_contents($tmpFileName, $contents) ? $tmpFileName : false;
  }

  /**
   * Given a directory,
   *
   * @param string $directory path
   *
   * @return array
   */
  public static function dirTree($directory)
  {
    $path = '';
    $stack[] = $directory;
    while ($stack)
    {
      $thisdir = array_pop($stack);
      if ($dircont = scandir($thisdir))
      {
        $i=0;
        while (isset($dircont[$i]))
        {
          if ($dircont[$i] !== '.' && $dircont[$i] !== '..'
            // ignore system/hidden files
            && !preg_match('/^\..*/', $dircont[$i]))
          {
            $current_file = "{$thisdir}/{$dircont[$i]}";
            if (is_file($current_file))
            {
              $path[] = "{$thisdir}/{$dircont[$i]}";
            }
            elseif (is_dir($current_file))
            {
              $stack[] = $current_file;
            }
          }
          $i++;
        }
      }
    }

    return $path;
  }

  /**
   * Given a directory return its size
   *
   * @param string $directory path
   * @param array $options
   *
   * @return int
   */
  public static function getDirectorySize($directory, $options = array())
  {
    if (!is_dir($directory))
    {
      return -1;
    }

    // Derived from http://www.php.net/manual/en/function.filesize.php#94566
    $size = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    foreach ($iterator as $item)
    {
      $size += $item->getSize();
    }

    // Set metric units for return value
    if (isset($options['units']))
    {
      switch (strtolower($options['units']))
      {
        case 'g':
          $size /= pow(10, 3);

        case 'm':
          $size /= pow(10, 3);

        case 'k':
          $size /= pow(10, 3);
      }

      $size = round($size, 2);
    }

    return $size;
  }

  public static function likeISO8601Date($date)
  {
    $date = substr($date, 0, 19).'Z';

    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $date, $parts) == true)
    {
      $time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);

      $input_time = strtotime($date);

      if ($input_time === false)
      {
        return false;
      }

      return $input_time == $time;
    }
    else
    {
      return false;
    }
  }

  /**
   * GZIPs a file on disk (appending .gz to the name)
   *
   * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
   * Based on function by Kioob at:
   * http://www.php.net/manual/en/function.gzwrite.php#34955
   *
   * @param string $source Path to file that should be compressed
   * @param integer $level GZIP compression level (default: 9)
   * @return string New filename (with .gz appended) if success, or false if operation fails
   */
  public static function gzipFile($source, $level = 9)
  {
    $dest = $source.'.gz';
    $mode = 'wb'.$level;
    $error = false;
    if ($fpOut = gzopen($dest, $mode))
    {
      if ($fpIn = fopen($source, 'rb'))
      {
        while (!feof($fpIn))
        {
          gzwrite($fpOut, fread($fpIn, 1024 * 512));
        }

        fclose($fpIn);
      }
      else
      {
        $error = true;
      }

      gzclose($fpOut);
    }
    else
    {
      $error = true;
    }

    return $error ? false : $dest;
  }

  /**
   * Clear Qubit's class-specific, in-memory caches
   *
   * @return void
   */
  public static function clearClassCaches()
  {
    foreach (get_declared_classes() as $c)
    {
      if (strpos($c, 'Qubit') === 0 && method_exists($c, 'clearCache'))
      {
        $c::clearCache();
      }
    }
  }

  /**
   * Format poorly formatted XML strings
   */
  public static function tidyXml($xml)
  {
    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml);

    return $dom->saveXML();
  }

  /**
   * Similar to the one in sfWebRequest but will check for a list of headers
   * and return the first match or a fallback value that defaults to null.
   */
  public static function getHttpHeader($names, $default = null)
  {
    $request = sfContext::getInstance()->getRequest();

    if (!is_array($names))
    {
      $names = array($names);
    }

    foreach ($names as $item)
    {
      if (null !== $value = $request->getHttpHeader($item))
      {
        return $value;
      }
    }

    return $default;
  }

  /**
   * Move the uploaded file to a stable location so it is available post-upload.
   *
   * @param  $file array
   *
   * @return modified $file array
   */
  public static function moveUploadFile($file)
  {
    Qubit::createUploadDirsIfNeeded();
    $tmpDir = sfConfig::get('sf_upload_dir').'/tmp';

    // Get a unique file name (to avoid clashing file names).
    do
    {
      $uniqueString = substr(md5(time().$file['name']), 0, 8);
      $tmpFileName = "TMP$uniqueString";
      $tmpFilePath = "$tmpDir/$tmpFileName";
    }
    while (file_exists($tmpFilePath));

    // Move file to web/uploads/tmp directory.
    if (!move_uploaded_file($file['tmp_name'], $tmpFilePath))
    {
      $errorMessage = sfContext::getInstance()->i18n->__('Unable to complete file import. File %1% could not be moved to %2%', array('%1%' => $file['name'], '%2%' => $tmpDir));
      throw new sfException($errorMessage);
    }

    $file['tmp_name'] = $tmpFilePath;
    return $file;
  }

  /**
   * Create "uploads/" and "uploads/tmp/" if they don't exist
   */
  public static function createUploadDirsIfNeeded()
  {
    $uploadsPath = sfConfig::get('sf_upload_dir');
    $directories = array(
      $uploadsPath,
      $uploadsPath . DIRECTORY_SEPARATOR . 'tmp'
    );

    foreach ($directories as $dirPath)
    {
      if (!is_dir($dirPath))
      {
        mkdir($dirPath, 0755);
      }
    }
  }
}
