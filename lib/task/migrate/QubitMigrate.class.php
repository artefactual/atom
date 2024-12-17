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

/**
 * Base class for migrating qubit data.
 *
 * @author     David Juhasz <david@artefactual.com
 */
class QubitMigrate
{
    public $data;
    public $version;

    public function __construct($data, $version)
    {
        $this->data = $data;
        $this->version = $version;
    }

    /**
     * Do migration of data.
     *
     * @return array modified data
     */
    public function execute()
    {
        $this->alterData();
        $this->sortData();

        return $this->getData();
    }

    /**
     * Getter for migration data.
     *
     * @return array arrayized yaml data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Try to match a row when the search key may be the row key or the object id
     * - as is often the case with foreign key relations in $this->data.
     *
     * @param string classname name of Qubit class (e.g. QubitInformationObject)
     * @param string keyOrId row key or 'id' column value
     * @param mixed $classname
     * @param mixed $keyOrId
     *
     * @return array the found row, or NULL for no match
     */
    public function getRowByKeyOrId($classname, $keyOrId)
    {
        $row = null;

        if (isset($this->data[$classname][$keyOrId])) {
            $row = $this->data[$classname][$keyOrId];
            $row['_key'] = $keyOrId;
        } elseif ($key = $this->getRowKey($this->data[$classname], 'id', $keyOrId)) {
            $row = $this->data[$classname][$key];
            $row['_key'] = $key;
        }

        return $row;
    }

    /*
     * ------------------
     * STATIC METHODS
     * ------------------
     */

    /**
     * Loop through row searching for $searchColumn value = $searchValue.
     * Return row key for first matched object.
     *
     * @param string $searchRow    row array to search
     * @param string $searchColumn Name of column to check for $searchValue
     * @param mixed  $searchValue  Value to find - can be string or array
     *
     * @return array row key
     */
    public static function findRowKeyForColumnValue($searchRow, $searchColumn, $searchValue)
    {
        foreach ($searchRow as $key => $columns) {
            if (is_array($searchValue)) {
                // Try and match key/value pair passed in searchValue (e.g. the english
                // value for an i18n column)
                $searchKey = key($searchValue);

                if (isset($columns[$searchColumn][$searchKey]) && $columns[$searchColumn][$searchKey] == $searchValue[$searchKey]) {
                    return $key;
                }
            } elseif (isset($columns[$searchColumn]) && $columns[$searchColumn] == $searchValue) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Splice two associative arrays.
     *
     * From http://ca3.php.net/manual/en/function.array-splice.php
     *
     * @author weikard at gmx dot de (15-Sep-2005 08:53)
     *
     * @param array $array        Primary array
     * @param int   $position     insert index
     * @param array $insert_array spliced array
     */
    public static function array_insert(&$array, $position, $insert_array)
    {
        $first_array = array_splice($array, 0, $position);
        $array = array_merge($first_array, $insert_array, $array);
    }

    /**
     * Get the index for a given key of an associative array.
     *
     * @param array  $arr     array to search
     * @param string $findKey key to search for
     *
     * @return mixed integer on success, false (bool) if key does not exist
     */
    public static function getArrayKeyIndex($arr, $findKey)
    {
        $index = 0;
        foreach ($arr as $key => $value) {
            if ($key == $findKey) {
                return $index;
            }
            ++$index;
        }

        return false;
    }

    /**
     * Sort the given objectList by left value.
     *
     * @param $objectList array of data objects
     */
    public static function sortByLft(&$objectList)
    {
        $newList = [];
        $highLft = 0;
        foreach ($objectList as $key => $row) {
            // If this left value is higher than any previous value, or there is no
            // left value, then add the current row to the end of $newList
            if (false === isset($row['lft']) || $row['lft'] > $highLft) {
                $newList[$key] = $row;
                $highLft = (isset($row['lft'])) ? $row['lft'] : $highLft;
            }

            // Else, find the right place in $newList to insert the current row
            // (sorted by lft values)
            else {
                $i = 0;
                foreach ($newList as $newKey => $newRow) {
                    if ($newRow['lft'] > $row['lft']) {
                        self::array_insert($newList, $i, [$key => $row]);

                        break;
                    }
                    ++$i;
                }
            }
        }

        $objectList = $newList;
    }

    /**
     * Recursively delete a hierarchical data tree.
     *
     * @param $objectList      array full dataset
     * @param $deleteObjectKey string key of array object to delete
     */
    public static function cascadeDelete($objectList, $deleteObjectKey)
    {
        $deleteObjectId = null;
        if (isset($objectList[$deleteObjectKey]['id'])) {
            $deleteObjectId = $objectList[$deleteObjectKey]['id'];
        }

        foreach ($objectList as $key => $row) {
            if (isset($row['parent_id'])) {
                if ($deleteObjectKey == $row['parent_id'] || (null !== $deleteObjectId && $deleteObjectId == $row['parent_id'])) {
                    $objectList = self::cascadeDelete($objectList, $key);
                }
            }
        }

        unset($objectList[$deleteObjectKey]);

        return $objectList;
    }

    public static function findForeignKeys(array $tables, $configuration)
    {
        $finder = sfFinder::type('file')->name('*schema.yml')->prune('doctrine');
        $dirs = array_merge([sfConfig::get('sf_config_dir')], $configuration->getPluginSubPaths('/config'));
        $schemas = $finder->in($dirs);
        if (!count($schemas)) {
            throw new sfCommandException('You must create a schema.yml file.');
        }

        $dbSchema = new sfPropelDatabaseSchema();

        foreach ($schemas as $schema) {
            $schemaArray = sfYaml::load($schema);

            if (!is_array($schemaArray)) {
                continue; // No defined schema here, skipping
            }

            if (!isset($schemaArray['classes'])) {
                // Old schema syntax: we convert it
                $schemaArray = $dbSchema->convertOldToNewYaml($schemaArray);
            }

            foreach ($schemaArray['classes'] as $classKey => $class) {
                foreach ($class['columns'] as $columnKey => $column) {
                    if ('id' == $columnKey) {
                        continue;
                    }

                    if (!is_array($column)) {
                        continue;
                    }

                    if ('integer' != $column['type']) {
                        continue;
                    }

                    if (!in_array($column['foreignTable'], $tables)) {
                        continue;
                    }

                    $className = 'Qubit'.$classKey;
                    $tableName = $className::TABLE_NAME;

                    // Ignore table if it's not available yet in the db
                    $query = QubitPdo::prepare('SHOW TABLES LIKE :table');
                    $query->bindParam(':table', $tableName, PDO::PARAM_STR);
                    if ($query->execute() && false === $query->fetch(PDO::FETCH_NUM)) {
                        continue;
                    }

                    $columns[] = [
                        'table' => $tableName,
                        'column' => $columnKey,
                    ];
                }
            }
        }

        foreach ($tables as $item) {
            switch ($item) {
                case 'object':
                    $columns[] = ['table' => 'object', 'column' => 'id'];

                    break;

                case 'term':
                case 'taxonomy':
                case 'menu':
                    $columns[] = ['table' => $item, 'column' => 'id'];
                    $columns[] = ['table' => $item.'_i18n', 'column' => 'id'];
            }
        }

        return $columns;
    }

    public static function updateAutoNumeric()
    {
        $last = QubitPdo::fetchOne('SELECT (MAX(id) + 1) AS last FROM object')->last;

        QubitPdo::modify("ALTER TABLE object AUTO_INCREMENT = {$last}");
    }

    public static function bumpTerm($id, $configuration)
    {
        if (!isset($configuration)) {
            throw new sfException('Missing parameter');
        }

        // Stop execution if there is no record
        if (null === QubitTerm::getById($id)) {
            return;
        }

        $connection = Propel::getConnection();

        $connection->beginTransaction();

        try {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 0');

            // Get new autonumeric
            $last = QubitPdo::fetchOne('SELECT (MAX(id) + 1) AS last FROM object')->last;

            $foreignKeys = self::findForeignKeys([QubitObject::TABLE_NAME, QubitTerm::TABLE_NAME], $configuration);

            foreach ($foreignKeys as $item) {
                // From the list of columns that the codebase is giving us, it may happen that some of them are
                // not available yet in the database since we are still running the migration. If this is the case,
                // ignore it, otherwise the UPDATE will fail.
                if (false === QubitPdo::fetchOne("SHOW COLUMNS FROM {$item['table']} LIKE ?", [$item['column']])) {
                    continue;
                }

                QubitPdo::modify(
                    "UPDATE {$item['table']} SET {$item['column']} = ? WHERE {$item['column']} = ?",
                    [$last, $id]
                );
            }

            $connection->exec('SET FOREIGN_KEY_CHECKS = 1');

            self::updateAutoNumeric();
        } catch (Exception $e) {
            $connection->rollback();

            throw $e;
        }

        $connection->commit();
    }

    public static function bumpTaxonomy($id, $configuration)
    {
        if (!isset($configuration)) {
            throw new sfException('Missing parameter');
        }

        // Stop execution if there is no record
        if (null === QubitTaxonomy::getById($id)) {
            return;
        }

        $connection = Propel::getConnection();

        $connection->beginTransaction();

        try {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 0');

            // Get new autonumeric
            $last = QubitPdo::fetchOne('SELECT (MAX(id) + 1) AS last FROM object')->last;

            $foreignKeys = self::findForeignKeys([QubitObject::TABLE_NAME, QubitTaxonomy::TABLE_NAME], $configuration);

            foreach ($foreignKeys as $item) {
                QubitPdo::modify(
                    "UPDATE {$item['table']} SET {$item['column']} = ? WHERE {$item['column']} = ?",
                    [$last, $id]
                );
            }

            $connection->exec('SET FOREIGN_KEY_CHECKS = 1');

            self::updateAutoNumeric();
        } catch (Exception $e) {
            $connection->rollback();

            throw $e;
        }

        $connection->commit();
    }

    public static function bumpMenu($id, $configuration)
    {
        if (!isset($configuration)) {
            throw new sfException('Missing parameter');
        }

        // Stop execution if there is no record
        if (null === QubitMenu::getById($id)) {
            return;
        }

        $connection = Propel::getConnection();

        $connection->beginTransaction();

        try {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 0');

            // Get new autonumeric
            $last = QubitPdo::fetchOne('SELECT (MAX(id) + 1) AS last FROM object')->last;

            $foreignKeys = self::findForeignKeys([QubitMenu::TABLE_NAME], $configuration);

            foreach ($foreignKeys as $item) {
                QubitPdo::modify(
                    "UPDATE {$item['table']} SET {$item['column']} = ? WHERE {$item['column']} = ?",
                    [$last, $id]
                );
            }

            self::updateAutoNumeric();
        } catch (Exception $e) {
            $connection->rollback();

            throw $e;
        }

        $connection->commit();
    }

    public static function addColumn($table, $column, array $options = [])
    {
        $connection = Propel::getConnection();
        $connection->beginTransaction();

        $queries = [];

        $sql = "ALTER TABLE {$table} ADD {$column}";

        // Position of the new column
        if (isset($options['after'])) {
            $sql .= " AFTER {$options['after']}";
        } elseif (isset($options['before'])) {
            $sql .= " BEFORE {$options['before']}";
        } elseif (isset($options['first'])) {
            $sql .= ' FIRST';
        }

        $queries[] = $sql;

        // "columnName INT NULL" => "columnName"
        $column = array_shift(preg_split('/ /', $column));

        // Index
        if (isset($options['idx'])) {
            $queries[] = "ALTER TABLE {$table} ADD INDEX ({$column});";
        }

        // Foreign key
        if (isset($options['fk'])) {
            $sql = sprintf(
                'ALTER TABLE %s ADD FOREIGN KEY (%s) REFERENCES %s (%s)',
                $table,
                $column,
                $options['fk']['referenceTable'],
                $options['fk']['referenceColumn']
            );

            if (isset($options['fk']['onDelete'])) {
                $sql .= ' ON DELETE '.$options['fk']['onDelete'];
            }

            if (isset($options['fk']['onUpdate'])) {
                $sql .= ' ON UPDATE '.$options['fk']['onUpdate'];
            }

            $queries[] = $sql;
        }

        try {
            foreach ($queries as $query) {
                $connection->exec($query);
            }
        } catch (Exception $e) {
            if ($connection->inTransaction()) {
                $connection->rollback();
            }

            throw $e;
        }

        if ($connection->inTransaction()) {
            $connection->commit();
        }
    }

    public static function dropColumn($table, $column)
    {
        $connection = Propel::getConnection();

        $connection->beginTransaction();

        try {
            $stmt = $connection->prepare('SHOW CREATE TABLE '.$table);
            $stmt->execute();

            $data = $stmt->fetchAll();

            foreach (explode("\n", $data[0][1]) as $line) {
                $line = explode(' ', trim($line));

                switch ($line[0]) {
                    // Indexes
                    case 'KEY':
                        // Build array with DROP INDEX commands
                        if ('(`'.$column.'`),' == $line[2]) {
                            $keys[] = 'DROP INDEX '.$line[1].' ON '.$table;
                        } else {
                            continue 2;
                        }

                        break;

                    // Foreign keys
                    case 'CONSTRAINT':
                        // Build array with DROP FOREIGN KEY commands
                        if ('FOREIGN' == $line[2] && '(`'.$column.'`)' == $line[4]) {
                            $foreignKeys[] = 'ALTER TABLE '.$table.' DROP FOREIGN KEY '.$line[1];
                        } else {
                            continue 2;
                        }

                        break;
                }
            }

            // The order matters, foreign keys must be removed first
            foreach (array_merge($foreignKeys, $keys) as $sqlCommand) {
                $connection->exec($sqlCommand);
            }

            // Drop column
            $connection->exec('ALTER TABLE `'.$table.'` DROP COLUMN `'.$column.'`');
        } catch (Exception $e) {
            $connection->rollback();

            throw $e;
        }

        $connection->commit();
    }

    public static function dropTable($table)
    {
        $connection = Propel::getConnection();

        $connection->beginTransaction();

        try {
            $connection->exec('SET FOREIGN_KEY_CHECKS = 0');

            $connection->exec("DROP TABLE IF EXISTS {$table}");

            $connection->exec('SET FOREIGN_KEY_CHECKS = 1');
        } catch (Exception $e) {
            $connection->rollback();

            throw $e;
        }

        $connection->commit();
    }

    public static function addNewFixtureI18ns()
    {
        $conn = Propel::getConnection();

        $fixtures = [];

        foreach (sfFinder::type('file')->name('*.yml')->in(sfConfig::get('sf_data_dir').'/fixtures/') as $yaml) {
            foreach (sfYaml::load($yaml) as $classname => $data) {
                $fixtures[$classname] = $data;
            }
        }

        foreach ($fixtures as $classname => $row) {
            // Don't overwrite static page text and make sure there's an I18n table
            if ('QubitStaticPage' == $classname || !class_exists($classname.'I18n')) {
                continue;
            }

            $table = constant($classname.'I18n::TABLE_NAME');

            switch ($classname) {
                case 'QubitMenu':
                    $colname = 'label';

                    break;

                case 'QubitSetting':
                    $colname = 'value';

                    break;

                default:
                    $colname = 'name';
            }

            $query = "INSERT INTO {$table} ({$colname}, id, culture) VALUES (?, ?, ?);";
            $insertStmt = $conn->prepare($query);

            $query = "SELECT target.culture, source.id FROM {$table} source
                JOIN {$table} target ON source.id = target.id
                WHERE source.culture = 'en'
                AND target.culture <> 'en'
                AND source.{$colname} = ?";
            $selectStmt = $conn->prepare($query);

            foreach ($row as $key => $columns) {
                $id = null;
                $existingCultures = [];

                if (!is_array($columns[$colname]) || !isset($columns['id'])) {
                    continue;
                }

                // Build array of existing cultures, so we don't stomp user values
                $selectStmt->execute([$columns[$colname]['en']]);

                while ($item = $selectStmt->fetch(PDO::FETCH_OBJ)) {
                    $existingCultures[] = $item->culture;

                    if (!isset($id)) {
                        $id = $item->id;
                    }
                }

                // Get primary key for insert
                foreach ($columns as $column => $values) {
                    foreach ($values as $culture => $value) {
                        if (in_array($culture, $existingCultures)) {
                            continue;
                        }

                        // Insert new culture values
                        try {
                            $insertStmt->execute([
                                $value,
                                $id,
                                $culture,
                            ]);
                        } catch (PDOException $e) {
                            // Ignore insert errors
                            continue;
                        }
                    }
                }
            }
        }
    }

    public static function updateIndexes($indexes)
    {
        foreach ($indexes as $data) {
            // Get actual index name
            $sql = 'SHOW INDEX FROM %s WHERE Column_name=:column;';
            $result = QubitPdo::fetchOne(
                sprintf($sql, $data['table']),
                [':column' => $data['column']]
            );

            // Stop if the index is missing
            if (!$result || !$result->Key_name) {
                throw new Exception(sprintf(
                    "Could not find index for '%s' column on '%s' table.",
                    $data['column'],
                    $data['table']
                ));
            }

            // Skip if the index already has the expected name
            if ($result->Key_name == $data['index']) {
                continue;
            }

            QubitPdo::modify(sprintf(
                'ALTER TABLE %s RENAME INDEX %s TO %s;',
                $data['table'],
                $result->Key_name,
                $data['index']
            ));
        }
    }

    public static function updateForeignKeys($foreignKeys)
    {
        $dbname = QubitPdo::fetchColumn('select database();');

        foreach ($foreignKeys as $foreignKey) {
            // Get actual contraint name
            $sql = 'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE ';
            $sql .= 'WHERE TABLE_NAME=:table AND COLUMN_NAME=:column ';
            $sql .= 'AND REFERENCED_TABLE_NAME=:refTable ';
            $sql .= 'AND CONSTRAINT_SCHEMA=:dbname';
            $oldConstraintName = QubitPdo::fetchColumn($sql, [
                ':table' => $foreignKey['table'],
                ':column' => $foreignKey['column'],
                ':refTable' => $foreignKey['refTable'],
                ':dbname' => $dbname,
            ]);

            // Stop if the foreign key is missing
            if (!$oldConstraintName) {
                throw new Exception(sprintf(
                    "Could not find foreign key for '%s' column on '%s' table.",
                    $foreignKey['column'],
                    $foreignKey['table']
                ));
            }

            // Update/delete rows with foreign keys pointing to non existing rows
            if ('ON DELETE SET NULL' == strtoupper(trim($foreignKey['onDelete']))) {
                $sql = "UPDATE {$foreignKey['table']} tb1
                    LEFT JOIN {$foreignKey['refTable']} tb2
                    ON tb1.{$foreignKey['column']}=tb2.id
                    SET tb1.{$foreignKey['column']}=NULL";
            } else {
                $sql = "DELETE tb1
                    FROM {$foreignKey['table']} tb1
                    LEFT JOIN {$foreignKey['refTable']} tb2
                    ON tb1.{$foreignKey['column']}=tb2.id";
            }

            $sql .= " WHERE tb1.{$foreignKey['column']} IS NOT NULL AND tb2.id IS NULL";

            QubitPdo::modify($sql);

            // Having the same name requires to drop and add in two statements
            $sql = 'ALTER TABLE %s DROP FOREIGN KEY %s;';
            QubitPdo::modify(sprintf(
                $sql,
                $foreignKey['table'],
                $oldConstraintName
            ));

            try {
                $sql = 'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) ';
                $sql .= 'REFERENCES %s (id) %s;';
                QubitPdo::modify(sprintf(
                    $sql,
                    $foreignKey['table'],
                    $foreignKey['constraint'],
                    $foreignKey['column'],
                    $foreignKey['refTable'],
                    $foreignKey['onDelete']
                ));
            } catch (Exception $e) {
                throw new Exception(sprintf(
                    "Could not alter foreign key for '%s' column on '%s' table.\n%s",
                    $foreignKey['column'],
                    $foreignKey['table'],
                    $e->getMessage()
                ));
            }
        }
    }

    /**
     * Wrapper for findRowKeyForColumnValue() method.
     *
     * @param string $className
     * @param string $searchColumn
     * @param string $searchKey
     *
     * @return string key for matched row
     */
    protected function getRowKey($className, $searchColumn, $searchKey)
    {
        if (isset($this->data[$className])) {
            return self::findRowKeyForColumnValue($this->data[$className], $searchColumn, $searchKey);
        }
    }

    /**
     * Convienience method for grabbing a QubitTerm row key based on the value of
     * the 'id' column.
     *
     * @param string $searchKey
     *
     * @return string key for matched row
     */
    protected function getTermKey($searchKey)
    {
        return $this->getRowKey('QubitTerm', 'id', $searchKey);
    }

    /**
     * @return unknown_type
     */
    protected function deleteStubObjects()
    {
        // Delete "stub" QubitEvent objects that have no valid "event type"
        if (isset($this->data['QubitEvent'])) {
            foreach ($this->data['QubitEvent'] as $key => $row) {
                if (!isset($row['type_id'])) {
                    unset($this->data['QubitEvent'][$key]);

                    // Also delete related QubitObjectTermRelation object (if any)
                    while ($objectTermRelationKey = $this->getRowKey('QubitObjectTermRelation', 'object_id', $key)) {
                        unset($this->data['QubitObjectTermRelation'][$objectTermRelationKey]);
                    }
                }
            }
        }

        // Remove blank "stub" QubitObjectTermRelation objects
        if (isset($this->data['QubitObjectTermRelation'])) {
            foreach ($this->data['QubitObjectTermRelation'] as $key => $row) {
                if (!isset($row['object_id']) || !isset($row['term_id'])) {
                    unset($this->data['QubitObjectTermRelation'][$key]);
                }
            }
        }

        // Remove blank "stub" QubitRelation objects
        if (isset($this->data['QubitRelation'])) {
            foreach ($this->data['QubitRelation'] as $key => $row) {
                if (!isset($row['object_id']) || !isset($row['subject_id'])) {
                    unset($this->data['QubitRelation'][$key]);
                }
            }
        }

        return $this;
    }

    /**
     * Insert a non-hierarchical $newData into an existing dataset ($originalData),
     * which contains nested set columns (but is also non-hierarchical in
     * structure), before the row specified by $pivotKey.  Update lft and rgt
     * values appropriately.
     *
     * @param array  $originalData The existing YAML dataset array
     * @param string $pivotKey     key of row that should follow the inserted data
     * @param array  $newData      data to insert in $originalData
     */
    protected static function insertBeforeNestedSet(array &$originalData, $pivotKey, array $newData)
    {
        // If pivotKey doesn't exist, then just return a simple array merge
        if (!isset($originalData[$pivotKey])) {
            return array_merge($originalData, $newData);
        }

        $pivotIndex = null;
        $pivotLft = null;
        $width = count($newData) * 2;

        // Get index ($i) of pivot row and it's left value (if any)
        $i = 0;
        foreach ($originalData as $key => $row) {
            if ($pivotKey == $key) {
                $pivotIndex = $i;
                if (isset($originalData[$key]['lft'])) {
                    $pivotLft = $originalData[$key]['lft'];
                }

                break;
            }
            ++$i;
        }

        // If a left value was found, then set merged values for lft & rgt columns
        if (null !== $pivotIndex) {
            // Loop through $newData and assign lft & rgt values
            $j = 0;
            foreach ($newData as &$row) {
                $row['lft'] = $pivotLft + ($j * 2);
                $row['rgt'] = $pivotLft + ($j * 2) + 1;
                ++$j;
            }

            // Bump existing lft & rgt values
            foreach ($originalData as &$row) {
                if (isset($row['lft']) && $pivotLft <= $row['lft']) {
                    $row['lft'] += $width;
                }

                if (isset($row['rgt']) && $pivotLft < $row['rgt']) {
                    $row['rgt'] += $width;
                }
            }
        }

        // Merge $newData into $originalData
        QubitMigrate::array_insert($originalData, $i, $newData);
    }
}
