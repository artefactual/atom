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
 * Move RAD General notes to global General note and remove RAD General note term
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0123
{
  const
    VERSION = 123, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Find RAD General note term (it doesn't have a fixed id)
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::RAD_NOTE_ID);
    $criteria->add(QubitTermI18n::CULTURE, 'en');
    $criteria->add(QubitTermI18n::NAME, 'General note');

    if (null !== $term = QubitTerm::getOne($criteria))
    {
      // Get all RAD General notes
      QubitPdo::prepareAndExecute('UPDATE note SET type_id=? WHERE type_id=?',
                                  array(QubitTerm::GENERAL_NOTE_ID, $term->id));

      // Remove RAD General note term
      $term->delete();
    }

    return true;
  }
}
