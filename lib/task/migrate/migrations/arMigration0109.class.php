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
 * Add thematic area and geographic subregion taxonomies
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0109
{
    public const VERSION = 109;
    public const MIN_MILESTONE = 2;

    /**
     * Upgrade.
     *
     * @param mixed $configuration
     *
     * @return bool True if the upgrade succeeded, False otherwise
     */
    public function up($configuration)
    {
        // Add the "Thematic Areas" taxonomy
        QubitMigrate::bumpTaxonomy(QubitTaxonomy::THEMATIC_AREA_ID, $configuration);
        $taxonomy = new QubitTaxonomy();
        $taxonomy->id = QubitTaxonomy::THEMATIC_AREA_ID;
        $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
        $taxonomy->name = 'Thematic Area';
        $taxonomy->note = 'These themes can assist in identifying major collecting areas, but should not be taken as comprehensive. Used as optional access point for ISDIAH repository records.';
        $taxonomy->culture = 'en';
        $taxonomy->save();

        // Add the "Geographic Subregions" taxonomy
        QubitMigrate::bumpTaxonomy(QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID, $configuration);
        $taxonomy = new QubitTaxonomy();
        $taxonomy->id = QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID;
        $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
        $taxonomy->name = 'Geographic Subregion';
        $taxonomy->note = 'Geographic subregions, based on local standards/common terminology if available. Used as optional access point for ISDIAH repository records in multi-repository databases.';
        $taxonomy->culture = 'en';
        $taxonomy->save();

        // Add the "Thematic Areas" terms
        foreach (
            [
                ['en' => 'Aboriginal Peoples', 'fr' => 'Peuples autochtones'],
                ['en' => 'Agriculture', 'fr' => 'Agriculture'],
                ['en' => 'Arts and Culture', 'fr' => 'Arts et culture'],
                ['en' => 'Communication', 'fr' => 'Communication'],
                ['en' => 'Education', 'fr' => 'Éducation'],
                ['en' => 'Environment', 'fr' => 'Environnement'],
                ['en' => 'Family / Domestic Life', 'fr' => 'Vie privée'],
                ['en' => 'Genealogical', 'fr' => 'Généalogique'],
                ['en' => 'Geography', 'fr' => 'Géographie'],
                ['en' => 'Industry, Manufacturing and Commerce', 'fr' => 'Industries, fabrication, et commerce'],
                ['en' => 'Labour', 'fr' => 'Travail'],
                ['en' => 'Law and Justice', 'fr' => 'Droit et justice'],
                ['en' => 'Medicine and Health', 'fr' => 'Médecine et santé'],
                ['en' => 'Military', 'fr' => 'Forces armées'],
                ['en' => 'Natural Resources', 'fr' => 'Richesses naturelles'],
                ['en' => 'Politics and Government', 'fr' => 'Politique et gouvernement'],
                ['en' => 'Populations', 'fr' => 'Populations'],
                ['en' => 'Recreation / Leisure / Sports', 'fr' => 'Loisirs et sports'],
                ['en' => 'Religion', 'fr' => 'Religion'],
                ['en' => 'Science and Technology', 'fr' => 'Sciences et technologie'],
                ['en' => 'Social Organizations and Activities', 'fr' => 'Vie sociale'],
                ['en' => 'Transportation', 'fr' => 'Transport'],
                ['en' => 'Travel and Exploration', 'fr' => 'Voyages et exploration'],
            ] as $termNames
        ) {
            $term = new QubitTerm();
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::THEMATIC_AREA_ID;
            $term->sourceCulture = 'en';
            foreach ($termNames as $key => $value) {
                $term->setName($value, ['culture' => $key]);
            }
            $term->save();
        }

        return true;
    }
}
