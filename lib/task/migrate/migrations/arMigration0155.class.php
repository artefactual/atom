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
 * Adds taxonomy and note type term for actor occupations
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0155
{
  const
    VERSION = 155, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACTOR_OCCUPATION_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::ACTOR_OCCUPATION_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->sourceCulture = 'en';
    $taxonomy->setName('Actor occupations', array('culture' => 'en'));
    $taxonomy->save();

    QubitMigrate::bumpTerm(QubitTerm::ACTOR_OCCUPATION_NOTE_ID, $configuration);
    $term = new QubitTerm;
    $term->id = QubitTerm::ACTOR_OCCUPATION_NOTE_ID;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::NOTE_TYPE_ID;
    $term->sourceCulture = 'en';
    $term->setName('Actor occupation note', array('culture' => 'en'));
    $term->save();

    return true;
  }
}
