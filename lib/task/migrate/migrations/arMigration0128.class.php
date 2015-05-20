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

/*
 * Add settings to hide IO slider and language menu
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0128
{
  const
    VERSION = 128, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    $settingNames = array('toggleIoSlider', 'toggleLanguageMenu');
    foreach ($settingNames as $name)
    {
      if (null === QubitSetting::getByName($name))
      {
        $setting = new QubitSetting;
        $setting->name = $name;
        $setting->value = 1;
        $setting->culture = 'en';
        $setting->save();
      }
    }

    return true;
  }
}
