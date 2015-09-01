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
 * Populate new premisAccessRightValues using basis
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0135
{
  const
    VERSION = 135, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Extract existing value of premisAccessRightValues
    $setting = QubitSetting::getByName('premisAccessRightValues');
    if (null === $setting)
    {
      throw new sfException('Setting premisAccessRightValues cannot be found');
    }

    // Convert premisAccessRightValues into a multidimensional array where each basis
    // has its own permissions (allow_master, allow_reference, allow_thumb, etc...)
    $premisAccessRightValues = array();
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      $premisAccessRightValues[$item->slug] = unserialize($setting->value);
    }

    // Serialize and save
    $setting->setValue(serialize($premisAccessRightValues), array('sourceCulture' => true));
    $setting->save();

    return true;
  }
}
