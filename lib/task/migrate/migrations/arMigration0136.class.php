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
 * Update access statements
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0136
{
  const
    VERSION = 136, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    $setting = QubitSetting::getByNameAndScope('access_disallow_warning', 'ui_label');
    if (null !== $setting)
    {
      $setting->delete();
    }

    $setting = QubitSetting::getByNameAndScope('access_conditional_warning', 'ui_label');
    if (null !== $setting)
    {
      $setting->delete();
    }

    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      // TODO: Add disallow statement
      $setting = new QubitSetting;
      $setting->name = "{$item->slug}_disallow";
      $setting->scope = 'access_statement';
      $setting->setValue('...', array('culture' => 'en'));
      $setting->save();

      // TODO: Add conditional statement
      $setting = new QubitSetting;
      $setting->name = "{$item->slug}_conditional";
      $setting->scope = 'access_statement';
      $setting->setValue('...', array('culture' => 'en'));
      $setting->save();
    }

    return true;
  }
}
