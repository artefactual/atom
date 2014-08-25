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
 * Add display_standard_id column to information object and information object
 * templates taxonomy and terms
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0109
{
  const
    VERSION = 109, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Add the "Thematic Areas" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::THEMATIC_AREA_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::THEMATIC_AREA_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Thematic Area';
    $taxonomy->note = 'These themes can assist in identifying major collecting areas, but should not be taken as comprehensive. Used as optional access point for ISDIAH repository records.';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add the "Geographic Subregions" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Geographic Subregion';
    $taxonomy->note = 'Geographic subregions, based on local standards/common terminology if available. Used as optional access point for ISDIAH repository records in multi-repository databases.';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add the "Thematic Areas" terms
    foreach (array(
      array('en' => 'Aboriginal Peoples',                   'fr' => 'Peuples autochtones'),
      array('en' => 'Agriculture',                          'fr' => 'Agriculture'),
      array('en' => 'Arts and Culture',                     'fr' => 'Arts et culture'),
      array('en' => 'Communication',                        'fr' => 'Communication'),
      array('en' => 'Education',                            'fr' => 'Éducation'),
      array('en' => 'Environment',                          'fr' => 'Environnement'),
      array('en' => 'Family / Domestic Life',               'fr' => 'Vie privée'),
      array('en' => 'Genealogical',                         'fr' => 'Généalogique'),
      array('en' => 'Geography',                            'fr' => 'Géographie'),
      array('en' => 'Industry, Manufacturing and Commerce', 'fr' => 'Industries, fabrication, et commerce'),
      array('en' => 'Labour',                               'fr' => 'Travail'),
      array('en' => 'Law and Justice',                      'fr' => 'Droit et justice'),
      array('en' => 'Medicine and Health',                  'fr' => 'Médecine et santé'),
      array('en' => 'Military',                             'fr' => 'Forces armées'),
      array('en' => 'Natural Resources',                    'fr' => 'Richesses naturelles'),
      array('en' => 'Politics and Government',              'fr' => 'Politique et gouvernement'),
      array('en' => 'Populations',                          'fr' => 'Populations'),
      array('en' => 'Recreation / Leisure / Sports',        'fr' => 'Loisirs et sports'),
      array('en' => 'Religion',                             'fr' => 'Religion'),
      array('en' => 'Science and Technology',               'fr' => 'Sciences et technologie'),
      array('en' => 'Social Organizations and Activities',  'fr' => 'Vie sociale'),
      array('en' => 'Transportation',                       'fr' => 'Transport'),
      array('en' => 'Travel and Exploration',               'fr' => 'Voyages et exploration')) as $termNames)
    {
      $term = new QubitTerm;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::THEMATIC_AREA_ID;
      $term->sourceCulture = 'en';
      foreach ($termNames as $key => $value) {
        $term->setName($value, array('culture' => $key));
      }
      $term->save();
    }
    return true;
  }
}
