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
 * Activate Dominion theme
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0095
{
  const
    VERSION = 95, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Disable this upgrade step since we can now automatically
    // remove missing plugins and set new themes, see #4557
    return true;

    // Retrieve QubitSetting object
    $criteria = new Criteria;
    $criteria->add(QubitSetting::NAME, 'plugins');
    if (null === $setting = QubitSetting::getOne($criteria))
    {
      return false;
    }

    // Unserialize
    $plugins = array_values(unserialize($setting->getValue(array('sourceCulture' => true))));

    // Define list of plugins that will be disabled
    $disable = array('qtTrilliumPlugin', 'sfAlouettePlugin', 'sfCaribouPlugin', 'sfColumbiaPlugin');

    // Remove them
    $plugins = array_diff($plugins, $disable);

    // Add arDominionPlugin
    $plugins[] = 'arDominionPlugin';

    // Save
    $setting->setValue(serialize(array_unique($plugins)), array('sourceCulture' => true));
    $setting->save();
  }
}
