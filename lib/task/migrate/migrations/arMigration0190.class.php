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
 * Modify database for caption/subtitle/chapter support.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0190
{
    const VERSION = 190;
    const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add extra column, language
        QubitMigrate::addColumn(
            QubitDigitalObject::TABLE_NAME,
            'language VARCHAR(50)',
            ['after' => 'usage_id']
        );

        // Add chapters term
        QubitMigrate::bumpTerm(QubitTerm::CHAPTERS_ID, $configuration);
        $term = new QubitTerm();
        $term->id = QubitTerm::CHAPTERS_ID;
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID;
        $term->sourceCulture = 'en';
        $term->setName('Chapters', ['culture' => 'en']);
        $term->setName('Chapitres', ['culture' => 'fr']);
        $term->save();

        // Add subtitles term
        QubitMigrate::bumpTerm(QubitTerm::SUBTITLES_ID, $configuration);
        $term = new QubitTerm();
        $term->id = QubitTerm::SUBTITLES_ID;
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = QubitTaxonomy::DIGITAL_OBJECT_USAGE_ID;
        $term->sourceCulture = 'en';
        $term->setName('Captions/Subtitles', ['culture' => 'en']);
        $term->setName('Légendes/Sous-titres', ['culture' => 'fr']);
        $term->save();

        return true;
    }
}
