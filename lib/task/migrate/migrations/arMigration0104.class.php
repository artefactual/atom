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
 * Add element visibility RAD and ISAD settings for archival history field
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0104
{
  const
    VERSION = 104, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $setting = new QubitSetting;
    $setting->name  = 'isad_archival_history';
    $setting->scope = 'element_visibility';
    $setting->value = 1;
    $setting->culture = 'en';
    $setting->save();

    $setting = new QubitSetting;
    $setting->name  = 'rad_archival_history';
    $setting->scope = 'element_visibility';
    $setting->value = 1;
    $setting->culture = 'en';
    $setting->save();

    return true;
  }
}
