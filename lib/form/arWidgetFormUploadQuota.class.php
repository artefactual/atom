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

class arWidgetFormUploadQuota extends sfWidgetFormInput
{
  protected function configure($options = array(), $attributes = array())
  {
    parent::configure($options, $attributes);
  }

  public function render($name, $value = null, $attributes = array(), $errors = array())
  {
    $uploadLimit = (int)sfConfig::get('app_upload_limit');

    if ($uploadLimit === 0)
    {
      return '<label>' . __('%1% upload is disabled', array('%1%' => sfConfig::get('app_ui_label_digitalobject'))) . '</label>';
    }
    else if ($uploadLimit === -1)
    {
      return '<label>'.__('Unlimited').'</label>';
    }

    $size = Qubit::getDirectorySize(sfConfig::get('sf_upload_dir'));
    if ($size < 0)
    {
      return '<label>'.__('The uploads directory has not been created yet.').'</label>';
    }

    return '<label>' . __('%1% used of %2%', array(
      '%1%' => hr_filesize($size),
      '%2%' => $uploadLimit.' GB')).'</label>';
  }
}
