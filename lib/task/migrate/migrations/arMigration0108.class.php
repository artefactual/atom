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
 * Create AIP table, add AIP types taxonomy and terms
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0108
{
    public const VERSION = 108;
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
        // Create AIP table
        $sql = <<<'sql'

CREATE TABLE `aip`
(
        `id` INTEGER  NOT NULL,
        `type_id` INTEGER,
        `uuid` VARCHAR(36),
        `filename` VARCHAR(1024),
        `size_on_disk` BIGINT,
        `digital_object_count` INTEGER,
        `created_at` DATETIME,
        `part_of` INTEGER,
        PRIMARY KEY (`id`),
        CONSTRAINT `aip_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `object` (`id`)
                ON DELETE CASCADE,
        INDEX `aip_FI_2` (`type_id`),
        CONSTRAINT `aip_FK_2`
                FOREIGN KEY (`type_id`)
                REFERENCES `term` (`id`)
                ON DELETE SET NULL,
       INDEX `aip_FI_3` (`part_of`),
       CONSTRAINT `aip_FK_3`
               FOREIGN KEY (`part_of`)
               REFERENCES `object` (`id`)
               ON DELETE SET NULL
)Engine=InnoDB;

sql;

        QubitPdo::modify($sql);

        // Create new term for the AIP relation type
        QubitMigrate::bumpTerm(QubitTerm::AIP_RELATION_ID, $configuration);
        $term = new QubitTerm();
        $term->id = QubitTerm::AIP_RELATION_ID;
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = QubitTaxonomy::RELATION_TYPE_ID;
        $term->name = 'AIP relation';
        $term->culture = 'en';
        $term->save();

        // Add "AIP types" taxonomy
        QubitMigrate::bumpTaxonomy(QubitTaxonomy::AIP_TYPE_ID, $configuration);
        $taxonomy = new QubitTaxonomy();
        $taxonomy->id = QubitTaxonomy::AIP_TYPE_ID;
        $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
        $taxonomy->name = 'AIP types';
        $taxonomy->culture = 'en';
        $taxonomy->save();

        // Add "AIP types" terms
        foreach (
            [
                QubitTerm::ARTWORK_COMPONENT_ID => 'Artwork component',
                QubitTerm::ARTWORK_MATERIAL_ID => 'Artwork material',
                QubitTerm::SUPPORTING_DOCUMENTATION_ID => 'Supporting documentation',
                QubitTerm::SUPPORTING_TECHNOLOGY_ID => 'Supporting technology',
            ] as $id => $value
        ) {
            QubitMigrate::bumpTerm($id, $configuration);
            $term = new QubitTerm();
            $term->id = $id;
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::AIP_TYPE_ID;
            $term->name = $value;
            $term->culture = 'en';
            $term->save();
        }

        return true;
    }
}
