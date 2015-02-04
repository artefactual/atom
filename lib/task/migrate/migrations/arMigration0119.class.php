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
 * Add Finding Aid model and public Finding Aid settings
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0119
{
  const
    VERSION = 119, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $setting = new QubitSetting;
    $setting->setName('findingAidModel');
    $setting->setSourceCulture('en');
    $setting->setValue('inventory-summary', array('culture' => 'en'));
    $setting->setEditable(1);
    $setting->setDeleteable(0);
    $setting->save();

    $setting = new QubitSetting;
    $setting->setName('publicFindingAid');
    $setting->setValue(1);
    $setting->setEditable(1);
    $setting->setDeleteable(0);
    $setting->save();

    return true;
  }
}
