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
 * Add the milestone setting to the database.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0093
{
  const
    VERSION = 93, // The new database version
    MIN_MILESTONE = 1; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Get current codebase milestone
    $substrings = preg_split('/\./', qubitConfiguration::VERSION);
    $milestone = array_shift($substrings);

    // Store it
    $setting = new QubitSetting;
    $setting->name = 'milestone';
    $setting->setValue($milestone, array('sourceCulture' => true));
    $setting->save();

    return true;
  }
}
