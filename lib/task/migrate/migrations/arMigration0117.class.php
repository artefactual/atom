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
 * Add genre taxonomy and its terms
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0117
{
  const
    VERSION = 117, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Add genre search label setting
    $setting = new QubitSetting;
    $setting->name  = 'genre';
    $setting->scope = 'ui_label';
    $setting->editable = 1;
    $setting->deleteable = 0;
    $setting->source_culture = 'en';
    $setting->setValue('Genre', array('culture' => 'en'));
    $setting->save();

    // Add the genre taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::GENRE_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::GENRE_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Genre';
    $taxonomy->note = 'Genre terms drawn from appropriate vocabularies; used as an optional access point in the RAD template. Default terms are from "Basic Genre Terms for Cultural Heritage Materials" (Library of Congress).';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add genre terms
    foreach (array(
      array('en' => 'Advertisements'),
      array('en' => 'Albums'),
      array('en' => 'Architecture'),
      array('en' => 'Blank forms'),
      array('en' => 'Books'),
      array('en' => 'Broadsides'),
      array('en' => 'Cartoons (Commentary)'),
      array('en' => 'Catalogs'),
      array('en' => 'Cityscapes'),
      array('en' => 'Clippings'),
      array('en' => 'Correspondence'),
      array('en' => 'Diaries'),
      array('en' => 'Drawings'),
      array('en' => 'Ephemera'),
      array('en' => 'Essays'),
      array('en' => 'Ethnography'),
      array('en' => 'Fieldnotes'),
      array('en' => 'Illustrations'),
      array('en' => 'Interviews'),
      array('en' => 'Landscapes'),
      array('en' => 'Leaflets'),
      array('en' => 'Manuscripts'),
      array('en' => 'Maps'),
      array('en' => 'Miscellaneous Documents'),
      array('en' => 'Motion Pictures'),
      array('en' => 'Music'),
      array('en' => 'Narratives'),
      array('en' => 'Paintings'),
      array('en' => 'Pamphlets'),
      array('en' => 'Periodicals'),
      array('en' => 'Petitions'),
      array('en' => 'Photographs'),
      array('en' => 'Physical Objects'),
      array('en' => 'Poetry'),
      array('en' => 'Portraits'),
      array('en' => 'Postcards'),
      array('en' => 'Posters'),
      array('en' => 'Prints'),
      array('en' => 'Programs'),
      array('en' => 'Recording logs'),
      array('en' => 'Scores'),
      array('en' => 'Sheet Music'),
      array('en' => 'Timetables'),
      array('en' => 'Transcriptions')) as $termNames)
    {
      $term = new QubitTerm;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::GENRE_ID;
      $term->sourceCulture = 'en';
      foreach ($termNames as $key => $value)
      {
        $term->setName($value, array('culture' => $key));
      }

      $term->save();
    }

    return true;
  }
}
