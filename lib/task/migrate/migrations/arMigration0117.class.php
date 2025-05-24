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
    public const VERSION = 117;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add genre search label setting
        $setting = new QubitSetting();
        $setting->name = 'genre';
        $setting->scope = 'ui_label';
        $setting->editable = 1;
        $setting->deleteable = 0;
        $setting->source_culture = 'en';
        $setting->setValue('Genre', ['culture' => 'en']);
        $setting->save();

        // Add the genre taxonomy
        QubitMigrate::bumpTaxonomy(QubitTaxonomy::GENRE_ID, $configuration);
        $taxonomy = new QubitTaxonomy();
        $taxonomy->id = QubitTaxonomy::GENRE_ID;
        $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
        $taxonomy->name = 'Genre';
        $taxonomy->note = 'Genre terms drawn from appropriate vocabularies; used as an optional access point in the RAD template. Default terms are from "Basic Genre Terms for Cultural Heritage Materials" (Library of Congress).';
        $taxonomy->culture = 'en';
        $taxonomy->save();

        // Add genre terms
        foreach (
            [
                ['en' => 'Advertisements'],
                ['en' => 'Albums'],
                ['en' => 'Architecture'],
                ['en' => 'Blank forms'],
                ['en' => 'Books'],
                ['en' => 'Broadsides'],
                ['en' => 'Cartoons (Commentary)'],
                ['en' => 'Catalogs'],
                ['en' => 'Cityscapes'],
                ['en' => 'Clippings'],
                ['en' => 'Correspondence'],
                ['en' => 'Diaries'],
                ['en' => 'Drawings'],
                ['en' => 'Ephemera'],
                ['en' => 'Essays'],
                ['en' => 'Ethnography'],
                ['en' => 'Fieldnotes'],
                ['en' => 'Illustrations'],
                ['en' => 'Interviews'],
                ['en' => 'Landscapes'],
                ['en' => 'Leaflets'],
                ['en' => 'Manuscripts'],
                ['en' => 'Maps'],
                ['en' => 'Miscellaneous Documents'],
                ['en' => 'Motion Pictures'],
                ['en' => 'Music'],
                ['en' => 'Narratives'],
                ['en' => 'Paintings'],
                ['en' => 'Pamphlets'],
                ['en' => 'Periodicals'],
                ['en' => 'Petitions'],
                ['en' => 'Photographs'],
                ['en' => 'Physical Objects'],
                ['en' => 'Poetry'],
                ['en' => 'Portraits'],
                ['en' => 'Postcards'],
                ['en' => 'Posters'],
                ['en' => 'Prints'],
                ['en' => 'Programs'],
                ['en' => 'Recording logs'],
                ['en' => 'Scores'],
                ['en' => 'Sheet Music'],
                ['en' => 'Timetables'],
                ['en' => 'Transcriptions'],
            ] as $termNames
        ) {
            $term = new QubitTerm();
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::GENRE_ID;
            $term->sourceCulture = 'en';
            foreach ($termNames as $key => $value) {
                $term->setName($value, ['culture' => $key]);
            }

            $term->save();
        }

        return true;
    }
}
