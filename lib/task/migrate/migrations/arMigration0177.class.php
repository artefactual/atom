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
 * Convert database tables character set to `utf8mb4` and
 * collation to `utf8mb4_0900_ai_ci` for MySQL 8.0 and higher.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0177
{
  const
    VERSION = 177, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Reduce DO path index size for the extra byte
    $sql = 'ALTER TABLE `digital_object` ';
    $sql .= 'DROP INDEX `path`, ADD INDEX `path`(`path`(768));';
    QubitPdo::modify($sql);

    // Get all tables
    $tables = QubitPdo::fetchAll(
      'SHOW TABLES;',
      array(),
      array('fetchMode' => PDO::FETCH_COLUMN)
    );

    // Change charset and collation on all tables
    foreach ($tables as $table)
    {
      $sql = 'ALTER TABLE `%s` CONVERT TO ';
      $sql .= 'CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;';
      QubitPdo::modify(sprintf($sql, $table));
    }

    // TODO: check and update VARCHAR and TEXT type columns length

    // Restore binary collation on slug column
    $sql = 'ALTER TABLE `slug` MODIFY `slug` VARCHAR(255) ';
    $sql .= 'CHARACTER SET utf8mb4 COLLATE utf8mb4_bin;';
    QubitPdo::modify($sql);

    return true;
  }
}
