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
 * Remove converse_term_id column and add terms converse relations
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0106
{
    public const VERSION = 106;
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
        // Remove the converse_term_id column
        QubitMigrate::dropColumn(QubitTerm::TABLE_NAME, 'converse_term_id');

        // Create new term for the converse relations type
        QubitMigrate::bumpTerm(QubitTerm::CONVERSE_TERM_ID, $configuration);
        $term = new QubitTerm();
        $term->id = QubitTerm::CONVERSE_TERM_ID;
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = QubitTaxonomy::TERM_RELATION_TYPE_ID;
        $term->name = 'Converse term';
        $term->culture = 'en';
        $term->save();

        // Add converse term relations
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
                // Obtain/create object term
                $criteria = new Criteria();
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                $criteria->add(QubitTermI18n::NAME, $termName);

                if (null === $objectTerm = QubitTerm::getOne($criteria)) {
                    $objectTerm = new QubitTerm();
                    $objectTerm->name = $termName;
                    $objectTerm->culture = 'en';
                }

                // Make sure that the term is on the right taxonomy and parent
                $objectTerm->parentId = $parentId;
                $objectTerm->taxonomyId = QubitTaxonomy::ACTOR_RELATION_TYPE_ID;
                $objectTerm->save();

                if ('itself' == $converseTermName) {
                    $subjectTerm = $objectTerm;
                } else {
                    // Obtain/create subject term
                    $criteria = new Criteria();
                    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                    $criteria->add(QubitTermI18n::NAME, $converseTermName);

                    if (null === $subjectTerm = QubitTerm::getOne($criteria)) {
                        $subjectTerm = new QubitTerm();
                        $subjectTerm->name = $converseTermName;
                        $subjectTerm->culture = 'en';
                    }

                    // Make sure that the term is on the right taxonomy and parent
                    $subjectTerm->parentId = $parentId;
                    $subjectTerm->taxonomyId = QubitTaxonomy::ACTOR_RELATION_TYPE_ID;
                    $subjectTerm->save();
                }

                // Create relation
                $relation = new QubitRelation();
                $relation->object = $objectTerm;
                $relation->subject = $subjectTerm;
                $relation->typeId = QubitTerm::CONVERSE_TERM_ID;
                $relation->save();
            }
        }

        return true;
    }
}
