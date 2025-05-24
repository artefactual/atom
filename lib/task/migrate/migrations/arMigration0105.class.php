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
 * Add converse_term_id column to terms table and new actor relation type terms
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0105
{
    public const VERSION = 105;
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
        // Add extra column, term.converse_term_id
        QubitMigrate::addColumn(
            QubitTerm::TABLE_NAME,
            'converse_term_id INT NULL',
            [
                'after' => 'code',
                'idx' => true,
                'fk' => [
                    'referenceTable' => 'term',
                    'referenceColumn' => 'id',
                    'onDelete' => 'SET NULL',
                    'onUpdate' => 'RESTRICT',
                ],
            ]
        );

        // Add new actor relation type terms
        foreach (
            [
                QubitTerm::HIERARCHICAL_RELATION_ID => [
                    'is the superior of' => 'is the subordinate of',
                    'controls' => 'is controlled by',
                    'is the owner of' => 'is owned by',
                ],
                QubitTerm::TEMPORAL_RELATION_ID => [
                    'is the predecessor of' => 'is the successor of',
                ],
                QubitTerm::FAMILY_RELATION_ID => [
                    'is the parent of' => 'is the child of',
                    'is the sibling of' => 'itself',
                    'is the spouse of' => 'itself',
                    'is the cousin of' => 'itself',
                    'is the grandparent of' => 'is the grandchild of',
                ],
                QubitTerm::ASSOCIATIVE_RELATION_ID => [
                    'is the provider of' => 'is the client of',
                    'is the business partner of' => 'itself',
                    'is the associate of' => 'itself',
                    'is the friend of' => 'itself',
                ],
            ] as $parentId => $terms
        ) {
            foreach ($terms as $termName => $converseTermName) {
                $term = new QubitTerm();
                $term->parentId = $parentId;
                $term->taxonomyId = QubitTaxonomy::ACTOR_RELATION_TYPE_ID;
                $term->name = $termName;
                $term->culture = 'en';
                $term->save();

                if ('itself' == $converseTermName) {
                    $term->converseTermId = $term->id;
                } else {
                    $converseTerm = new QubitTerm();
                    $converseTerm->parentId = $parentId;
                    $converseTerm->taxonomyId = QubitTaxonomy::ACTOR_RELATION_TYPE_ID;
                    $converseTerm->name = $converseTermName;
                    $converseTerm->culture = 'en';
                    $converseTerm->converseTermId = $term->id;
                    $converseTerm->save();

                    $term->converseTermId = $converseTerm->id;
                }

                $term->save();
            }
        }

        return true;
    }
}
