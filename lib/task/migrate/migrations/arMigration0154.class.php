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
 * Add new settings for identifier mask.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0154
{
  const
    VERSION = 154, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $identifierSettings = array(
      array('identifier_mask', '%Y-%m-%d/#i'),
      array('identifier_mask_enabled', 0),
      array('identifier_counter', 0)
    );

    foreach ($identifierSettings as $setting)
    {
      list($name, $val) = $setting;

      if (null === QubitSetting::getByName($name))
      {
        $setting = new QubitSetting;
        $setting->name = $name;
        $setting->value = $val;
        $setting->editable = 1;
        $setting->culture = 'en';
        $setting->save();
      }
    }

    return true;
  }
}
