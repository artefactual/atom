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
 * Add tables, taxonomy, and terms that relate to accession events
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0185
{
    public const VERSION = 185;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add accession_event table
        $sql = <<<'sql'

CREATE TABLE IF NOT EXISTS `accession_event`
(
        `id` INTEGER  NOT NULL,
        `type_id` INTEGER,
        `accession_id` INTEGER,
        `date` DATE,
        `source_culture` VARCHAR(16)  NOT NULL,
        PRIMARY KEY (`id`),
        CONSTRAINT `accession_event_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `object` (`id`)
                ON DELETE CASCADE,
        INDEX `accession_event_FI_2` (`type_id`),
        CONSTRAINT `accession_event_FK_2`
                FOREIGN KEY (`type_id`)
                REFERENCES `term` (`id`)
                ON DELETE SET NULL,
        INDEX `accession_event_FI_3` (`accession_id`),
        CONSTRAINT `accession_event_FK_3`
                FOREIGN KEY (`accession_id`)
                REFERENCES `accession` (`id`)
                ON DELETE CASCADE
)Engine=InnoDB;

CREATE TABLE IF NOT EXISTS `accession_event_i18n`
(
        `agent` VARCHAR(255),
        `id` INTEGER  NOT NULL,
        `culture` VARCHAR(16)  NOT NULL,
        PRIMARY KEY (`id`,`culture`),
        CONSTRAINT `accession_event_i18n_FK_1`
                FOREIGN KEY (`id`)
                REFERENCES `accession_event` (`id`)
                ON DELETE CASCADE
)Engine=InnoDB;

sql;

        QubitPdo::modify($sql);

        // Add accession event type taxonomy if it doesn't exist
        if (null == QubitTaxonomy::getById(QubitTaxonomy::ACCESSION_EVENT_TYPE_ID)) {
            QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESSION_EVENT_TYPE_ID, $configuration);
            $taxonomy = new QubitTaxonomy();
            $taxonomy->id = QubitTaxonomy::ACCESSION_EVENT_TYPE_ID;
            $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
            $taxonomy->sourceCulture = 'en';
            $taxonomy->setName('Accession event type', ['culture' => 'en']);
            $taxonomy->save();
        }

        // Add physical transfer accession event type if it doesn't exist
        $sql = 'SELECT * FROM '.QubitTerm::TABLE_NAME.' t
            INNER JOIN '.QubitTermI18n::TABLE_NAME." ti
            WHERE ti.name='Physical transfer'
            AND t.taxonomy_id=?
            AND ti.culture='en'
            AND t.id=?";

        if (null == QubitPdo::fetchOne($sql, [QubitTaxonomy::ACCESSION_EVENT_TYPE_ID, QubitTerm::ACCESSION_EVENT_PHYSICAL_TRANSFER_ID])) {
            // Add physical transfer accession event type term
            QubitMigrate::bumpTerm(QubitTerm::ACCESSION_EVENT_PHYSICAL_TRANSFER_ID, $configuration);
            $term = new QubitTerm();
            $term->id = QubitTerm::ACCESSION_EVENT_PHYSICAL_TRANSFER_ID;
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::ACCESSION_EVENT_TYPE_ID;
            $term->sourceCulture = 'en';
            $term->setName('Physical transfer', ['culture' => 'en']);
            $term->save();
        }

        // Add accession event note type term if it doesn't exist
        $sql = 'SELECT * FROM '.QubitTerm::TABLE_NAME.' t
            INNER JOIN '.QubitTermI18n::TABLE_NAME." ti
            WHERE ti.name='Accession event note'
            AND t.taxonomy_id=?
            AND ti.culture='en'
            AND t.id=?";

        if (null == QubitPdo::fetchOne($sql, [QubitTaxonomy::NOTE_TYPE_ID, QubitTerm::ACCESSION_EVENT_NOTE_ID])) {
            // Add accession event note type term
            QubitMigrate::bumpTerm(QubitTerm::ACCESSION_EVENT_NOTE_ID, $configuration);
            $term = new QubitTerm();
            $term->id = QubitTerm::ACCESSION_EVENT_NOTE_ID;
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::NOTE_TYPE_ID;
            $term->sourceCulture = 'en';
            $term->setName('Accession event note', ['culture' => 'en']);
            $term->save();
        }

        return true;
    }
}
