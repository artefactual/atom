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
class arMigration0107
{
  const
    VERSION = 107, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    $elements = array(
      'rad_title_responsibility_area',
      'rad_edition_area',
      'rad_material_specific_details_area',
      'rad_dates_of_creation_area',
      'rad_physical_description_area',
      'rad_publishers_series_area',
      'rad_archival_description_area',
      'rad_notes_area',
      'rad_standard_number_area',
      'rad_access_points_area',
      'rad_description_control_area',
      'isad_identity_area',
      'isad_context_area',
      'isad_content_and_structure_area',
      'isad_conditions_of_access_use_area',
      'isad_allied_materials_area',
      'isad_notes_area',
      'isad_access_points_area',
      'isad_description_control_area');

    // Add visibility settings
    foreach ($elements as $item)
    {
      $setting = new QubitSetting;
      $setting->name  = $item;
      $setting->scope = 'element_visibility';
      $setting->value = 1;
      $setting->culture = 'en';
      $setting->save();
    }

    return true;
  }
}
