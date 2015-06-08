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
 * Add settings to hide IO slider and language menu
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0129
{
  const
    VERSION = 129, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    $newRadNotes = array(
      'Signatures note',
      'Cast note',
      'Credits note'
    );

    // Create RAD note types if they don't already exist
    foreach($newRadNotes as $note)
    {
      $criteria = new Criteria;
      $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::RAD_NOTE_ID);
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $note);
      $criteria->add(QubitTermI18n::CULTURE, 'en');

      if (QubitTerm::getOne($criteria) == null)
      {
        $term = new QubitTerm;
        $term->name = $note;
        $term->culture = 'en';
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = QubitTaxonomy::RAD_NOTE_ID;
        $term->save();
      }
    }

    return true;
  }
}
