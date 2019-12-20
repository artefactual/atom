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
 * Normalize upgraded and default databases as much as possible
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
    // Remove old tables present, at least, on the demo data database
    $oldTables = array(
      'historical_event',
      'system_event',
      'place_map_relation',
      'rights_actor_relation',
      'rights_term_relation',
      'map_i18n',
      'map',
      'place_i18n',
      'place',
      'right_i18n',
      'right',
    );

    foreach ($oldTables as $table)
    {
      QubitPdo::modify(sprintf('DROP TABLE IF EXISTS `%s`;', $table));
    }

    // Rename indexes
    // - IO `display_standard_id` (unnamed on migration 94)
    // - Job `user_id` and `object_id` (misnamed on migration 111)
    $indexes = array(
      array(
        'table' => 'information_object',
        'column' => 'display_standard_id',
        'index' => 'information_object_FI_8',
      ),
      array(
        'table' => 'job',
        'column' => 'user_id',
        'index' => 'job_FI_2',
      ),
      array(
        'table' => 'job',
        'column' => 'object_id',
        'index' => 'job_FI_3',
      ),
    );

    foreach ($indexes as $data)
    {
      // Get actual index name
      $sql = 'SHOW INDEX FROM %s WHERE Column_name=:column_name;';
      $result = QubitPdo::fetchOne(
        sprintf($sql, $data['table']),
        array(':column_name' => $data['column'])
      );

      // Stop if the index is missing
      if (!$result || !$result->Key_name)
      {
        throw new Exception(sprintf(
          "Could not find index for '%s' column on '%s' table.",
          $data['column'],
          $data['table']
        ));
      }

      // Skip if the index already has the expected name
      if ($result->Key_name == $data['index'])
      {
        continue;
      }

      QubitPdo::modify(sprintf(
        'ALTER TABLE %s RENAME INDEX %s TO %s;',
        $data['table'],
        $result->Key_name,
        $data['index']
      ));
    }

    // Fix foreign keys:
    // - Restore ON DELETE CASCADE on DO `object_id` (missing on migration 169).
    // - Rename IO `display_standard_id` constraint (unnamed on migration 94)
    $foreignKeys = array(
      array(
        'tableName' => 'digital_object',
        'columnName' => 'object_id',
        'refTableName' => 'object',
        'newConstraintName' => 'digital_object_FK_2',
        'onDelete' => 'ON DELETE CASCADE',
      ),
      array(
        'tableName' => 'information_object',
        'columnName' => 'display_standard_id',
        'refTableName' => 'term',
        'newConstraintName' => 'information_object_FK_8',
        'onDelete' => 'ON DELETE SET NULL',
      ),
    );

    foreach ($foreignKeys as $foreignKey)
    {
      // Get actual contraint name
      $sql = 'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ';
      $sql .= 'WHERE TABLE_NAME=:table_name AND COLUMN_NAME=:column_name ';
      $sql .= 'AND REFERENCED_TABLE_NAME=:ref_table_name;';
      $oldConstraintName = QubitPdo::fetchColumn($sql, array(
        ':table_name' => $foreignKey['tableName'],
        ':column_name' => $foreignKey['columnName'],
        ':ref_table_name' => $foreignKey['refTableName'],
      ));

      // Stop if the foreign key is missing
      if (!$oldConstraintName)
      {
        throw new Exception(sprintf(
          "Could not find foreign key for '%s' column on '%s' table.",
          $foreignKey['columnName'],
          $foreignKey['tableName']
        ));
      }

      // Having the same name requires to drop and add in two statements
      $sql = 'ALTER TABLE %s DROP FOREIGN KEY %s;';
      QubitPdo::modify(sprintf(
        $sql,
        $foreignKey['tableName'],
        $oldConstraintName
      ));

      $sql = 'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) ';
      $sql .= 'REFERENCES %s (id) %s;';
      QubitPdo::modify(sprintf(
        $sql,
        $foreignKey['tableName'],
        $foreignKey['newConstraintName'],
        $foreignKey['columnName'],
        $foreignKey['refTableName'],
        $foreignKey['onDelete']
      ));
    }

    // Restore NOT NULL constraint on culture columns (removed on migration 172)
    $i18nTables = array(
      'accession_i18n',
      'acl_group_i18n',
      'actor_i18n',
      'contact_information_i18n',
      'deaccession_i18n',
      'event_i18n',
      'function_object_i18n',
      'information_object_i18n',
      'menu_i18n',
      'note_i18n',
      'other_name_i18n',
      'physical_object_i18n',
      'property_i18n',
      'relation_i18n',
      'repository_i18n',
      'rights_i18n',
      'setting_i18n',
      'static_page_i18n',
      'taxonomy_i18n',
      'term_i18n'
    );

    foreach($i18nTables as $i18nTable)
    {
      $baseTable = str_replace('_i18n', '', $i18nTable);

      // Update possible NULL values added in between migrations.
      $sql = 'UPDATE %s SET source_culture=:cul WHERE source_culture IS NULL;';
      QubitPdo::modify(
        sprintf($sql, $baseTable),
        array(':cul' => sfConfig::get('sf_default_culture', 'en'))
      );

      // The culture and id together are a unique key on the i18n tables so
      // there shouldn't be more than a NULL value per object id. Use the
      // base table source_culture (fixed above) to populate missing values.
      $sql = 'UPDATE %s i18n, %s base SET i18n.culture=base.source_culture ';
      $sql .= 'WHERE i18n.culture IS NULL AND i18n.id=base.id;';
      QubitPdo::modify(sprintf($sql, $i18nTable, $baseTable));

      // Modify columns
      $sql = 'ALTER TABLE %s MODIFY culture VARCHAR(16) NOT NULL;';
      QubitPdo::modify(sprintf($sql, $i18nTable));
      $sql = 'ALTER TABLE %s MODIFY source_culture VARCHAR(16) NOT NULL;';
      QubitPdo::modify(sprintf($sql, $baseTable));
    }

    // Restore NOT NULL constraint on slug column (removed on migration 159):
    // - Get NULL slugs and generate new ones.
    // - Modify column.
    $sql = 'SELECT id FROM slug WHERE slug IS NULL;';

    foreach (QubitPdo::fetchAll($sql) as $slug)
    {
      $sql = 'UPDATE slug SET slug=:slug WHERE id=:id';
      QubitPdo::modify($sql, array(
        ':slug' => QubitSlug::getUnique(),
        ':id' => $slug->id
      ));
    }

    $sql = 'ALTER TABLE slug MODIFY slug VARCHAR(255) ';
    $sql .= 'CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;';
    QubitPdo::modify($sql);

    return true;
  }
}
