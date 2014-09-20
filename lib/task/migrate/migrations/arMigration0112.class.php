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
class arMigration0112
{
  const
    VERSION = 112, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Add VRA core agent role terms
    foreach (array(
      array('en' => 'Architect'),
      array('en' => 'Associated'),
      array('en' => 'Author'),
      array('en' => 'Creator'),
      array('en' => 'Contributor'),
      array('en' => 'Designer'),
      array('en' => 'Director'),
      array('en' => 'Donor'),
      array('en' => 'Editor'),
      array('en' => 'Founder'),
      array('en' => 'Funder'),
      array('en' => 'Owner'),
      array('en' => 'Photographer'),
      array('en' => 'Subject')) as $termNames)
    {
      $term = new QubitTerm;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::VRA_CORE_AGENT_ROLE;
      $term->sourceCulture = 'en';
      foreach ($termNames as $key => $value) {
        $term->setName($value, array('culture' => $key));
      }
      $term->save();
    }

    return true;
  }
}
