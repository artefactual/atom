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
 * Add VRA core taxonomies
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0111
{
  const
    VERSION = 111, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Add the "VRA Core Agent Role" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::VRA_CORE_AGENT_ROLE, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::VRA_CORE_AGENT_ROLE;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Agent Role';
    $taxonomy->note = 'VRA core agent role.';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add the "VRA Core Date Type" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::VRA_CORE_DATE_TYPE, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::VRA_CORE_DATE_TYPE;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Date Type';
    $taxonomy->note = 'VRA core date type.';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add the "VRA Core Location Type" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::VRA_CORE_LOCATION_TYPE, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::VRA_CORE_LOCATION_TYPE;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Location Type';
    $taxonomy->note = 'VRA core location type.';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    return true;
  }
}
