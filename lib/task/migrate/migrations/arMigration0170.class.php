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
 * Add new term to the levels of description taxonomy
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0170
{
  const
    VERSION = 170, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Check if term actually exists before adding
    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID);
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::NAME, 'External file');
    $criteria->add(QubitTermI18n::CULTURE, 'en');

    if (null === QubitTerm::get($criteria))
    {
      // Create new term for external file digital object usage
      QubitMigrate::bumpTerm(QubitTerm::EXTERNAL_FILE_ID, $configuration);
      $term = new QubitTerm;
      $term->id = QubitTerm::EXTERNAL_FILE_ID;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID;
      $term->name = 'Local file';
      $term->culture = 'en';
      $term->save();

      return true;
    }

    return true;
  }
}
