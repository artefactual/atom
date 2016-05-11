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
 * Add setting for treeview type
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0127
{
  const
    VERSION = 127, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    $name = 'treeview_type';
    if (null === QubitSetting::getByName($name))
    {
      $setting = new QubitSetting;
      $setting->name = $name;
      $setting->editable = 1;
      $setting->deleteable = 0;
      $setting->value = 'sidebar';
      $setting->culture = 'en';
      $setting->save();
    }

    return true;
  }
}
