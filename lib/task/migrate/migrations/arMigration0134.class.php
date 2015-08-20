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
 * Introduce PREMIS Rights Statutes taxonomy, refs #8768
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0134
{
  const
    VERSION = 134, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Create taxonomy PREMIS Rights Statutes
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::RIGHTS_STATUTES_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::RIGHTS_STATUTES_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'PREMIS Rights Statutes';
    $taxonomy->note = 'Used to store Statute values when defining PREMIS rights statements with a statute Basis.';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Fixes for the changes incorrectly applied in arMigration0113.
    $sql = "SHOW COLUMNS FROM `rights` LIKE 'act_id';";
    if (false !== QubitPdo::fetchOne($sql))
    {
      //
      // Get rid of act_id for once!
      //

      $sql = "ALTER TABLE `rights` DROP FOREIGN KEY `rights_FK_3`;";
      QubitPdo::modify($sql);

      $sql = "DROP INDEX `rights_FI_3` ON `rights`;";
      QubitPdo::modify($sql);

      $sql = "ALTER TABLE `rights` DROP COLUMN `act_id`;";
      QubitPdo::modify($sql);

      //
      // Rename (rights_FI_4 => rights_FI_3) AND (rights_FK_4 => rights_FK_3)
      //

      $sql = "ALTER TABLE `rights` DROP FOREIGN KEY `rights_FK_4`;";
      QubitPdo::modify($sql);

      $sql = "DROP INDEX `rights_FI_4` ON `rights`;";
      QubitPdo::modify($sql);

      $sql = "ALTER TABLE `rights` ADD CONSTRAINT `rights_FK_3` FOREIGN KEY (`rights_holder_id`) REFERENCES `actor` (`id`) ON DELETE SET NULL;";
      QubitPdo::modify($sql);

      $sql = "CREATE INDEX `rights_FI_3` ON `rights` (`rights_holder_id`)";
      QubitPdo::modify($sql);

      //
      // Rename (rights_FI_5 => rights_FI_4)  AND (rights_FK_5 => rights_FK_4)
      //

      $sql = "ALTER TABLE `rights` DROP FOREIGN KEY `rights_FK_5`;";
      QubitPdo::modify($sql);

      $sql = "DROP INDEX `rights_FI_5` ON `rights`;";
      QubitPdo::modify($sql);

      $sql = "ALTER TABLE `rights` ADD CONSTRAINT `rights_FK_4` FOREIGN KEY (`copyright_status_id`) REFERENCES `term` (`id`) ON DELETE SET NULL;";
      QubitPdo::modify($sql);

      $sql = "CREATE INDEX `rights_FI_4` ON `rights` (`copyright_status_id`)";
      QubitPdo::modify($sql);
    }

    // Add column `rights`.`statute_citation_id`
    $sql = "ALTER TABLE `rights` ADD COLUMN `statute_citation_id` INTEGER AFTER `statute_determination_date`";
    QubitPdo::modify($sql);

    // Add index for column `rights`.`statute_citation_id`
    $sql = "CREATE INDEX `rights_FI_5` ON `rights`(`statute_citation_id`)";
    QubitPdo::modify($sql);

    // Add constraint for column `rights`.`statute_citation_id`
    $sql = "ALTER TABLE `rights` ADD CONSTRAINT `rights_FK_5` FOREIGN KEY (`statute_citation_id`) REFERENCES `term` (`id`) ON DELETE SET NULL;";
    QubitPdo::modify($sql);

    // Populate terms based on the values found in the old `rights_i18n`.`statute_citation`
    $defaultCulture = sfConfig::get('sf_default_culture');
    // Query 1: list the values in `rights_i18n`.`statute_citation` without duplicates
    $sql1 = "SELECT `id`, `statute_citation` FROM `rights_i18n` WHERE `culture` = ? GROUP BY `statute_citation` ORDER BY NULL";
    $stmt1 = QubitPdo::prepare($sql1);
    // Query 2: obtain the translations for a given right
    $sql2 = "SELECT `statute_citation`, `culture` FROM `rights_i18n` WHERE `id` = ? AND `culture` != ?";
    $stmt2 = QubitPdo::prepare($sql2);
    // Query 3: set the column `rights`.`statute_citation_id`
    $sql3 = "UPDATE `rights`, `rights_i18n` SET `rights`.`statute_citation_id` = ? WHERE `rights`.`id` = `rights_i18n`.`id` AND `rights_i18n`.`statute_citation` = ? AND `rights_i18n`.`culture` = ?";
    $stmt3 = QubitPdo::prepare($sql3);
    if ($stmt1->execute(array($defaultCulture)))
    {
      while ($row = $stmt1->fetch())
      {
        // Create new term
        $term = new QubitTerm;
        $term->parentId = QubitTerm::ROOT_ID;
        $term->taxonomyId = QubitTaxonomy::RIGHTS_STATUTES_ID;
        $term->setName($row['statute_citation'], array('culture' => $defaultCulture));
        if ($stmt2->execute(array($row['id'], $defaultCulture)))
        {
          while ($i18nRow = $stmt2->fetch())
          {
            $term->setName($i18nRow['statute_citation'], array('culture' => $i18nRow['culture']));
          }
        }
        $term->save();

        // We have to link the new term to its rights
        $stmt3->execute(array($term->id, $row['statute_citation'], $defaultCulture));
      }
    }

    // Drop column rights_i18n.statute_citation
    $sql = "ALTER TABLE `rights_i18n` DROP COLUMN `statute_citation`";
    QubitPdo::modify($sql);

    return true;
  }
}
