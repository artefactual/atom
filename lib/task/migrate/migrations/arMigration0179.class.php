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
 * Add taxonomy and terms for accession alternative identifier types
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0179
{
    public const VERSION = 179;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add accession alternative identifier type taxonomy if it doesn't exist
        if (null == QubitTaxonomy::getById(QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID)) {
            QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID, $configuration);
            $taxonomy = new QubitTaxonomy();
            $taxonomy->id = QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID;
            $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
            $taxonomy->sourceCulture = 'en';
            $taxonomy->setName('Accession alternative identifier type', ['culture' => 'en']);
            $taxonomy->save();
        }

        // Add default accession alternative identifier type term if it doesn't exist
        $sql = 'SELECT * FROM '.QubitTerm::TABLE_NAME.' t
            INNER JOIN '.QubitTermI18n::TABLE_NAME." ti
            WHERE ti.name='Accession alternative identifier'
            AND t.taxonomy_id=?
            AND ti.culture='en'
            AND t.id=?";

        if (null == QubitPdo::fetchOne($sql, [QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID, QubitTerm::ACCESSION_ALTERNATIVE_IDENTIFIER_DEFAULT_TYPE_ID])) {
            QubitMigrate::bumpTerm(QubitTerm::ACCESSION_ALTERNATIVE_IDENTIFIER_DEFAULT_TYPE_ID, $configuration);
            $term = new QubitTerm();
            $term->id = QubitTerm::ACCESSION_ALTERNATIVE_IDENTIFIER_DEFAULT_TYPE_ID;
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::ACCESSION_ALTERNATIVE_IDENTIFIER_TYPE_ID;
            $term->sourceCulture = 'en';
            $term->setName('Accession alternative identifier', ['culture' => 'en']);
            $term->save();
        }

        return true;
    }
}
