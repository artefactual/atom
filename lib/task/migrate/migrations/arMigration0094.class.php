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
class arMigration0094
{
    public const VERSION = 94;
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
        // Add extra column, information_object.display_standard_id
        QubitMigrate::addColumn(
            QubitInformationObject::TABLE_NAME,
            'display_standard_id INT NULL',
            [
                'after' => 'source_standard',
                'idx' => true,
                'fk' => [
                    'referenceTable' => 'term',
                    'referenceColumn' => 'id',
                    'onDelete' => 'SET NULL',
                    'onUpdate' => 'RESTRICT',
                ],
            ]
        );

        // Add the "Information object templates" taxonomy
        QubitMigrate::bumpTaxonomy(QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID, $configuration);
        $taxonomy = new QubitTaxonomy();
        $taxonomy->id = QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID;
        $taxonomy->name = 'Information object templates';
        $taxonomy->culture = 'en';
        $taxonomy->save();

        // Add also the available templates
        foreach (
            [
                'isad' => 'ISAD(G), 2nd ed. International Council on Archives',
                'dc' => 'Dublin Core, Version 1.1. Dublin Core Metadata Initiative',
                'mods' => 'MODS, Version 3.3. U.S. Library of Congress',
                'rad' => 'RAD, July 2008 version. Canadian Council of Archives',
            ] as $key => $value
        ) {
            $term = new QubitTerm();
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID;
            $term->code = $key;
            $term->name = $value;
            $term->culture = 'en';
            $term->save();
        }

        return true;
    }
}
