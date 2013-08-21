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
 * Activate DACS plugin
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0100
{
  const
    VERSION = 100, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Enable arDacsPlugin
    if (null !== $setting = QubitSetting::getByName('plugins'))
    {
      $settings = unserialize($setting->getValue(array('sourceCulture' => true)));
      $settings[] = 'arDacsPlugin';

      $setting->setValue(serialize($settings), array('sourceCulture' => true));
      $setting->save();
    }

    // Add the "dacs" template to its taxonomy
    $term = new QubitTerm;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID;
    $term->code = 'dacs';
    $term->name = 'DACS, 2nd ed. Society of American Archivists';
    $term->culture = 'en';
    $term->save();

    return true;
  }
}
