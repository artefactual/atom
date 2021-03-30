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
 * Add taxonomy and terms for user actions
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0162
{
    public const VERSION = 162;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        QubitMigrate::bumpTaxonomy(QubitTaxonomy::USER_ACTION_ID, $configuration);
        $taxonomy = new QubitTaxonomy();
        $taxonomy->id = QubitTaxonomy::USER_ACTION_ID;
        $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
        $taxonomy->sourceCulture = 'en';
        $taxonomy->setName('User actions', ['culture' => 'en']);
        $taxonomy->save();

        $terms = [
            QubitTerm::USER_ACTION_CREATION_ID => 'Creation',
            QubitTerm::USER_ACTION_MODIFICATION_ID => 'Modification',
        ];

        foreach ($terms as $id => $name) {
            QubitMigrate::bumpTerm($id, $configuration);
            $term = new QubitTerm();
            $term->id = $id;
            $term->parentId = QubitTerm::ROOT_ID;
            $term->taxonomyId = QubitTaxonomy::USER_ACTION_ID;
            $term->sourceCulture = 'en';
            $term->setName($name, ['culture' => 'en']);
            $term->save();
        }

        // Add table audit_log
        $sql = <<<'sql'

CREATE TABLE `audit_log`
(
  `id` INTEGER  NOT NULL AUTO_INCREMENT,
  `object_id` INTEGER  NOT NULL,
  `user_id` INTEGER,
  `user_name` VARCHAR(255),
  `action_type_id` INTEGER,
  `created_at` DATETIME,
  PRIMARY KEY (`id`),
  KEY `audit_log_FI_1`(`object_id`),
  CONSTRAINT `audit_log_FK_1`
    FOREIGN KEY (`object_id`)
    REFERENCES `object` (`id`)
    ON DELETE CASCADE,
  INDEX `audit_log_FI_2` (`user_id`),
  CONSTRAINT `audit_log_FK_2`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`id`)
    ON DELETE SET NULL,
  INDEX `audit_log_FI_3` (`action_type_id`),
  CONSTRAINT `audit_log_FK_3`
    FOREIGN KEY (`action_type_id`)
    REFERENCES `term` (`id`)
    ON DELETE SET NULL
)Engine=InnoDB;

sql;

        QubitPdo::modify($sql);

        return true;
    }
}
