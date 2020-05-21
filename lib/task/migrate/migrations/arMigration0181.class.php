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
 * Remove nested set from some models.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0181
{
  const
    VERSION = 181, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  /**
   * Upgrade
   *
   * @return bool True if the upgrade succeeded, False otherwise
   */
  public function up($configuration)
  {
    // Drop parent_id, lft and rgt columns
    QubitMigrate::dropColumn(QubitFunctionObject::TABLE_NAME, 'parent_id');
    QubitMigrate::dropColumn(QubitFunctionObject::TABLE_NAME, 'lft');
    QubitMigrate::dropColumn(QubitFunctionObject::TABLE_NAME, 'rgt');
    QubitMigrate::dropColumn(QubitPhysicalObject::TABLE_NAME, 'parent_id');
    QubitMigrate::dropColumn(QubitPhysicalObject::TABLE_NAME, 'lft');
    QubitMigrate::dropColumn(QubitPhysicalObject::TABLE_NAME, 'rgt');

    // Drop lft and rgt columns
    QubitMigrate::dropColumn(QubitActor::TABLE_NAME, 'lft');
    QubitMigrate::dropColumn(QubitActor::TABLE_NAME, 'rgt');
    QubitMigrate::dropColumn(QubitTaxonomy::TABLE_NAME, 'lft');
    QubitMigrate::dropColumn(QubitTaxonomy::TABLE_NAME, 'rgt');
    QubitMigrate::dropColumn(QubitAclGroup::TABLE_NAME, 'lft');
    QubitMigrate::dropColumn(QubitAclGroup::TABLE_NAME, 'rgt');

    // Fix function_object table indexes and foreign keys.
    // Needed after removing the parent_id column to keep them
    // in sync. with the ones from a new install.
    $indexes = array(
      array(
        'column' => 'type_id',
        'index' => 'function_object_FI_2',
      ),
      array(
        'column' => 'description_status_id',
        'index' => 'function_object_FI_3',
      ),
      array(
        'column' => 'description_detail_id',
        'index' => 'function_object_FI_4',
      ),
    );

    foreach ($indexes as $data)
    {
      // Get actual index name
      $sql = 'SHOW INDEX FROM %s WHERE Column_name=:column_name;';
      $result = QubitPdo::fetchOne(
        sprintf($sql, 'function_object'),
        array(':column_name' => $data['column'])
      );

      // Stop if the index is missing
      if (!$result || !$result->Key_name)
      {
        throw new Exception(sprintf(
          "Could not find index for '%s' column on '%s' table.",
          $data['column'],
          'function_object'
        ));
      }

      // Skip if the index already has the expected name
      if ($result->Key_name == $data['index'])
      {
        continue;
      }

      QubitPdo::modify(sprintf(
        'ALTER TABLE %s RENAME INDEX %s TO %s;',
        'function_object',
        $result->Key_name,
        $data['index']
      ));
    }

    $foreignKeys = array(
      array(
        'columnName' => 'id',
        'refTableName' => 'object',
        'newConstraintName' => 'function_object_FK_1',
        'onDelete' => ' ON DELETE CASCADE',
      ),
      array(
        'columnName' => 'type_id',
        'refTableName' => 'term',
        'newConstraintName' => 'function_object_FK_2',
        'onDelete' => '',
      ),
      array(
        'columnName' => 'description_status_id',
        'refTableName' => 'term',
        'newConstraintName' => 'function_object_FK_3',
        'onDelete' => '',
      ),
      array(
        'columnName' => 'description_detail_id',
        'refTableName' => 'term',
        'newConstraintName' => 'function_object_FK_4',
        'onDelete' => '',
      ),
    );

    foreach ($foreignKeys as $foreignKey)
    {
      // Get actual contraint name
      $sql = 'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ';
      $sql .= 'WHERE TABLE_NAME=:table_name AND COLUMN_NAME=:column_name ';
      $sql .= 'AND REFERENCED_TABLE_NAME=:ref_table_name;';
      $oldConstraintName = QubitPdo::fetchColumn($sql, array(
        ':table_name' => 'function_object',
        ':column_name' => $foreignKey['columnName'],
        ':ref_table_name' => $foreignKey['refTableName'],
      ));

      // Stop if the foreign key is missing
      if (!$oldConstraintName)
      {
        throw new Exception(sprintf(
          "Could not find foreign key for '%s' column on '%s' table.",
          $foreignKey['columnName'],
          'function_object'
        ));
      }

      // Having the same name requires to drop and add in two statements
      $sql = 'ALTER TABLE %s DROP FOREIGN KEY %s;';
      QubitPdo::modify(sprintf(
        $sql,
        'function_object',
        $oldConstraintName
      ));

      $sql = 'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) ';
      $sql .= 'REFERENCES %s (id)%s;';
      QubitPdo::modify(sprintf(
        $sql,
        'function_object',
        $foreignKey['newConstraintName'],
        $foreignKey['columnName'],
        $foreignKey['refTableName'],
        $foreignKey['onDelete']
      ));
    }

    return true;
  }
}
