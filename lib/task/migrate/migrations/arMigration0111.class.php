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
 * Add columns to access_log to allow it to be used to track AIP downloads
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0111
{
  const
    VERSION = 111, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    /**
     * Add type_id column, index and constraint
     */

    $sql = "ALTER TABLE access_log ADD COLUMN type_id INTEGER NOT NULL";
    QubitPdo::modify($sql);

    $sql = "CREATE INDEX access_log_FI_2 ON access_log (type_id)";
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `access_log`
ADD CONSTRAINT `access_log_FK_2`
FOREIGN KEY (`type_id`)
REFERENCES `term` (`id`);
sql;
    QubitPdo::modify($sql);

    /**
     * Add user_id column, index and constraint
     */

    $sql = "ALTER TABLE access_log ADD COLUMN user_id INTEGER NOT NULL";
    QubitPdo::modify($sql);

    $sql = "CREATE INDEX access_log_FI_3 ON access_log (user_id)";
    QubitPdo::modify($sql);

    $sql = <<<sql
ALTER TABLE `access_log`
ADD CONSTRAINT `access_log_FK_3`
FOREIGN KEY (`user_id`)
REFERENCES `term` (`id`);
sql;
    QubitPdo::modify($sql);

    /**
     * TODO
     * Rename column, s/access_date/date
     */

    // Add reason column
    $sql = "ALTER TABLE access_log ADD COLUMN reason VARCHAR(1024) DEFAULT NULL";
    QubitPdo::modify($sql);

    // Add "AIP types" taxonomy
    QubitMigrate::bumpTaxonomy(QubitTaxonomy::ACCESS_LOG_ENTRY_TYPE_ID, $configuration);
    $taxonomy = new QubitTaxonomy;
    $taxonomy->id = QubitTaxonomy::ACCESS_LOG_ENTRY_TYPE_ID;
    $taxonomy->parentId = QubitTaxonomy::ROOT_ID;
    $taxonomy->name = 'Access log entry types';
    $taxonomy->culture = 'en';
    $taxonomy->save();

    // Add "AIP types" terms
    foreach (array(
      QubitTerm::ACCESS_LOG_STANDARD_ENTRY => 'Access',
      QubitTerm::ACCESS_LOG_AIP_DOWNLOAD_ENTRY => 'AIP download',
      QubitTerm::ACCESS_LOG_AIP_FILE_DOWNLOAD_ENTRY => 'AIP file download') as $id => $value)
    {
      QubitMigrate::bumpTerm($id, $configuration);
      $term = new QubitTerm;
      $term->id = $id;
      $term->parentId = QubitTerm::ROOT_ID;
      $term->taxonomyId = QubitTaxonomy::AIP_TYPE_ID;
      $term->name = $value;
      $term->culture = 'en';
      $term->save();
    }

    // Update existing access log
    $sql = "UPDATE access_log SET type_id = ? WHERE type_id IS NULL";
    QubitPdo::modify($sql, array(QubitTerm::ACCESS_LOG_STANDARD_ENTRY));

    return true;
  }
}
