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
    // Natural number, trim leading zeros
    $natural = '0*([1-9]\d*)';

    // Zero and separator, trim leading zeros
    $zero = '0*(0[-\/])';

    // Trim trailing separator and zero
    $trim = '[-\/]0+';

    // Trim leading zeros from year, month, and day, and trailing zero day and
    // month
    $pattern = "/0*(\d+)(?:([-\/])$natural(?:([-\/])$natural
          |$trim)
        |([-\/])$zero$natural
        |$trim$trim)/x";

    $replacement = '$1$2$3$4$5$6$7$8';

    return preg_replace($pattern, $replacement, $value);
  }

  public static function renderDateStartEnd($date, $start, $end)
  {
    if (isset($date))
    {
      return $date;
    }

    if (isset($start) || isset($end))
    {
      $start = Qubit::renderDate($start);
      $end = Qubit::renderDate($end);

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
   * Check if the app is in read only mode looking at the
   * env. variable "ATOM_READ_ONLY" and the setting
   * "read_only" in app.yml, giving priority to the env. variable
   *
   * @return boolean
   */
  public static function isReadOnly()
  {
    // The env. variable is set to 'true', '1', 'on' or 'yes'
    if (filter_var(getenv('ATOM_READ_ONLY'), FILTER_VALIDATE_BOOLEAN))
    {
      return true;
    }

    // The env. variable doesn't exist and the setting is set to 'true'
    if (false === getenv('ATOM_READ_ONLY')
      && sfConfig::get('app_read_only', false))
    {
      return true;
    }

    // Otherwise:
    // - The env. variable and the setting don't exist
    // - The env. variable is set to anything different than 'true', '1', 'on' or 'yes'
    // - The env. varibale doesn't exist and the setting is set to anything different than 'true'
    return false;
  }
}
