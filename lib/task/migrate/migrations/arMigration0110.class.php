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
 * Add "DACS Note" taxonomy and its temrs
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0110
{
  const
    VERSION = 110, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Add the "DACS Note" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::DACS_NOTE_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::DACS_NOTE_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'DACS Note';
    $taxonomy->note = 'Note types that occur specifically within the Society of American Archivists "Describing Archives: a Content Standard" (DACS).';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add the "DACS Note" terms
    foreach (array(
      array('en' => 'Conservation'),
      array('en' => 'Citation'),
      array('en' => 'Alphanumeric designations'),
      array('en' => 'Variant title information'),
      array('en' => 'Processing information')) as $termNames)
    {
      $term = new QubitTerm;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::DACS_NOTE_ID;
      $term->sourceCulture = 'en';
      foreach ($termNames as $key => $value)
      {
        $term->setName($value, array('culture' => $key));
      }

      $term->save();
    }

    // Add "Record-keeping activity" event type
    $term = new QubitTerm;
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::EVENT_TYPE_ID;
    $term->sourceCulture = 'en';
    $term->setName('Record-keeping activity', array('culture' => 'en'));
    $term->save();

    return true;
  }
}
