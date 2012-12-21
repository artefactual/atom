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
 * This class extends the save method in sfValidatedFile so the logo or banner
 * uploaded in repository/editThemeAction.class.php can be cropped to fit the
 * application requirements
 *
 * @package AccesstoMemory
 * @subpackage form
 */
class arRepositoryThemeCropValidatedFile extends sfValidatedFile
{
  // Max dimensions in pixels
  const
    LOGO_MAX_WIDTH = 270,
    LOGO_MAX_HEIGHT = 270,
    BANNER_MAX_WIDTH = 800,
    BANNER_MAX_HEIGHT = 300;

  public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    $file = parent::save($file, $fileMode, $create, $dirMode);

    // Check if mogrify is available in the system
    exec('which mogrify', $output, $status);
    if (0 < $status)
    {
      return $file;
    }

    // Figure out necessary dimensions from the filename
    $pathInfo = pathinfo($this->savedName);
    switch ($pathInfo['filename'])
    {
      case 'logo':
        $width = self::LOGO_MAX_WIDTH;
        $height = self::LOGO_MAX_HEIGHT;

        break;

      case 'banner':
        $width = self::BANNER_MAX_WIDTH;
        $height = self::BANNER_MAX_HEIGHT;

        break;
    }

    // Stop execution if dimensions were not set
    if (!isset($width, $height))
    {
      return $file;
    }

    // mogrify overwrites the original image file
    $command = sprintf('mogrify -crop %sx%s+0+0 %s',
      $width,
      $height,
      $this->savedName);
    exec($command, $output, $status);

    return $file;
  }
}
