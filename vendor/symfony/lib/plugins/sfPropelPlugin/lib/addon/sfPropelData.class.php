<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This class is the Propel implementation of sfData.  It interacts with the data source
 * and loads data.
 *
 * @package    symfony
 * @subpackage propel
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfPropelData.class.php 33137 2011-10-18 13:05:25Z fabien $
 */
class sfPropelData extends sfData
{
  protected
    $deletedClasses = array(),
    $con            = null;

  /**
   * Loads data from a file or directory into a Propel data source
   *
   * @see sfPropelData::loadData()
   *
   * @param mixed   $directoryOrFile  A file or directory path or an array of files or directories
   * @param string  $connectionName   The Propel connection name, default 'propel'
   *
   * @throws Exception If the database throws an error, rollback transaction and rethrows exception
   */
  public function loadData($directoryOrFile = null, $connectionName = 'propel')
  {
    $files = $this->getFiles($directoryOrFile);

    // load map classes
    $this->loadMapBuilders($connectionName);
    $this->dbMap = Propel::getDatabaseMap($connectionName);

    // wrap all database operations in a single transaction
    $this->con = Propel::getConnection($connectionName);
    try
    {
      $this->con->beginTransaction();

      $this->doLoadData($files);

      $this->con->commit();
    }
    catch (Exception $e)
    {
      $this->con->rollBack();
      throw $e;
    }
  }

  /**
   * Implements the abstract loadDataFromArray method and loads the data using the generated data model.
   *
   * @param array   $data  The data to be loaded into the data source
   *
   * @throws Exception If data is unnamed.
   * @throws sfException If an object defined in the model does not exist in the data
   * @throws sfException If a column that does not exist is referenced
   */
  public function loadDataFromArray($data)
  {
    if ($data === null)
    {
      // no data
      return;
    }

    foreach ($data as $class => $datas)
    {
      $class = trim($class);

      $tableMap = $this->dbMap->getTable(constant($class.'::TABLE_NAME'));

      // iterate through datas for this class
      // might have been empty just for force a table to be emptied on import
      if (!is_array($datas))
      {
        continue;
      }

      foreach ($datas as $key => $data)
      {
        // create a new entry in the database
        if (!class_exists($class))
        {
          throw new InvalidArgumentException(sprintf('Unknown class "%s".', $class));
        }

        $obj = new $class();

        if (!is_array($data))
        {
          throw new InvalidArgumentException(sprintf('You must give a name for each fixture data entry (class %s).', $class));
        }

        foreach ($data as $name => $value)
        {
          try
          {
            $column = $tableMap->getColumn($name);
          }
          catch (PropelException $e)
          {
          }

          // foreign key?
          $columnCheck = !empty($column) && (isset($column) ? true : false);
-         $objectCheck = !empty($value) && is_string($value) && (isset($this->object_references[$value]) ? true : false);
          if ($columnCheck && $column->isForeignKey() && $objectCheck)
          {
            $value = $this->object_references[$value]->getPrimaryKey();
          }

          if (is_callable(array($obj, $callback = 'set'.sfInflector::camelize($name))))
          {
            if (is_array($value))
            {
              foreach ($value as $culture => $value)
              {
                call_user_func(array($obj, $callback), $value, array('culture' => $culture));
              }
            }
            else
            {
              call_user_func(array($obj, $callback), $value);
            }
          }
        }
        $obj->save($this->con);

        // save the object for future reference
        if (method_exists($obj, 'getPrimaryKey'))
        {
          $this->object_references[$key] = $obj;
        }
      }
    }
  }

  /**
   * Loads many to many objects.
   *
   * @param BasePropelObject $obj             A Propel object
   * @param string           $middleTableName The middle table name
   * @param array            $values          An array of values
   */
  protected function loadMany2Many($obj, $middleTableName, $values)
  {
    $middleTable = $this->dbMap->getTable($middleTableName);
    $middleClass = $middleTable->getPhpName();
    foreach ($middleTable->getColumns()  as $column)
    {
      if ($column->isForeignKey() && constant(constant(get_class($obj).'::PEER').'::TABLE_NAME') != $column->getRelatedTableName())
      {
        $relatedClass = $this->dbMap->getTable($column->getRelatedTableName())->getPhpName();
        break;
      }
    }

    if (!isset($relatedClass))
    {
      throw new InvalidArgumentException(sprintf('Unable to find the many-to-many relationship for object "%s".', get_class($obj)));
    }

    $setter = 'set'.get_class($obj);
    $relatedSetter = 'set'.$relatedClass;

    foreach ($values as $value)
    {
      if (!isset($this->object_references[$relatedClass.'_'.$value]))
      {
        throw new InvalidArgumentException(sprintf('The object "%s" from class "%s" is not defined in your data file.', $value, $relatedClass));
      }

      $middle = new $middleClass();
      $middle->$setter($obj);
      $middle->$relatedSetter($this->object_references[$relatedClass.'_'.$value]);
      $middle->save();
    }
  }

  /**
   * Clears existing data from the data source by reading the fixture files
   * and deleting the existing data for only those classes that are mentioned
   * in the fixtures.
   *
   * @param array $files The list of YAML files.
   *
   * @throws sfException If a class mentioned in a fixture can not be found
   */
  protected function doDeleteCurrentData($files)
  {
    // delete all current datas in database
    if (!$this->deleteCurrentData)
    {
      return;
    }

    rsort($files);
    foreach ($files as $file)
    {
      $data = sfYaml::load($file);

      if ($data === null)
      {
        // no data
        continue;
      }

      $classes = array_keys($data);
      foreach (array_reverse($classes) as $class)
      {
        $class = trim($class);
        if (in_array($class, $this->deletedClasses))
        {
          continue;
        }

        // Check that peer class exists before calling doDeleteAll()
        if (!class_exists(constant($class.'::PEER')))
        {
          throw new InvalidArgumentException(sprintf('Unknown class "%sPeer".', $class));
        }

        call_user_func(array(constant($class.'::PEER'), 'doDeleteAll'), $this->con);

        $this->deletedClasses[] = $class;
      }
    }
  }

  /**
   * Loads all map builders.
   *
   * @throws sfException If the class cannot be found
   */
  protected function loadMapBuilders($connectionName)
  {
    $dbMap = Propel::getDatabaseMap();
    $files = sfFinder::type('file')->name('*TableMap.php')->in(sfProjectConfiguration::getActive()->getModelDirs());
    foreach ($files as $file)
    {
      $omClass = 'Qubit'.basename($file, 'TableMap.php');
      if (class_exists($omClass) && is_subclass_of($omClass, 'BasePropelObject') && constant($omClass.'Peer::DATABASE_NAME') == $connectionName)
      {
        $tableMapClass = basename($file, '.php');
        $dbMap->addTableFromMapClass($tableMapClass);
      }
    }
  }

  /**
   * Dumps data to fixture from one or more tables.
   *
   * @param string $directoryOrFile   The directory or file to dump to
   * @param mixed  $tables            The name or names of tables to dump (or all to dump all tables)
   * @param string $connectionName    The connection name (default to propel)
   */
  public function dumpData($directoryOrFile, $tables = 'all', $connectionName = 'propel')
  {
    $dumpData = $this->getData($tables, $connectionName);

    // save to file(s)
    if (!is_dir($directoryOrFile))
    {
      file_put_contents($directoryOrFile, sfYaml::dump($dumpData, 3));
    }
    else
    {
      $i = 0;
      foreach ($tables as $tableName)
      {
        if (!isset($dumpData[$tableName]))
        {
          continue;
        }

        file_put_contents(sprintf("%s/%03d-%s.yml", $directoryOrFile, ++$i, $tableName), sfYaml::dump(array($tableName => $dumpData[$tableName]), 3));
      }
    }
  }

  /**
   * Returns data from one or more tables.
   *
   * @param  mixed  $tables           name or names of tables to dump (or all to dump all tables)
   * @param  string $connectionName   connection name
   *
   * @return array  An array of database data
   */
  public function getData($tables = 'all', $connectionName = 'propel')
  {
    $this->loadMapBuilders($connectionName);
    $this->con = Propel::getConnection($connectionName);
    $this->dbMap = Propel::getDatabaseMap($connectionName);

    // get tables
    if ('all' === $tables || null === $tables)
    {
      $tables = array();
      foreach ($this->dbMap->getTables() as $table)
      {
        $tables[] = $table->getPhpName();
      }
    }
    else if (!is_array($tables))
    {
      $tables = array($tables);
    }

    // Maintain an index of foreign keys to class names, e.g. QubitActor_123 =>
    // QubitRepository.  This is used in the case of multi-table inheritance to
    // build symbolic foreign keys which refer to the primary class of an
    // object.  This is important because the dump data contains entries only
    // for the primary classes of objects.  Another alternative would be to
    // resolve symbols when the data is loaded, by creating an entry in the
    // symbol table for each of an object's subclasses.
    $classNames = array();

    $dumpData = array();
$dumpData['QubitTaxonomy'] = array();

    $tables = $this->fixOrderingOfForeignKeyData($tables);
    foreach ($tables as $tableName)
    {
      $tableMap = $this->dbMap->getTable(constant('Qubit'.$tableName.'::TABLE_NAME'));
      $hasParent = false;
      $haveParents = false;
      $fixColumn = null;
      foreach ($tableMap->getColumns() as $column)
      {
        $col = strtolower($column->getName());
        if ($column->isForeignKey())
        {
          $relatedTable = $this->dbMap->getTable($column->getRelatedTableName());
          if ($tableName === $relatedTable->getPhpName())
          {
            if ($hasParent)
            {
              $haveParents = true;
            }
            else
            {
              $fixColumn = $column;
              $hasParent = true;
            }
          }
        }
      }

      if ($haveParents)
      {
        // unable to dump tables having multi-recursive references
        continue;
      }

      // get db info
      $resultsSets = array();
      if ($hasParent)
      {
        $resultsSets[] = $this->fixOrderingOfForeignKeyDataInSameTable($resultsSets, $tableName, $fixColumn);
      }
      else
      {
        $in = array();
        foreach ($tableMap->getColumns() as $column)
        {
          $in[] = '`'.strtolower($column->getName()).'`';
        }
        $stmt = $this->con->query(sprintf('SELECT %s FROM %s', implode(',', $in), constant('Qubit'.$tableName.'::TABLE_NAME')));

        $resultsSets[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        unset($stmt);
      }

      foreach ($resultsSets as $rows)
      {
        foreach ($rows as $row)
        {
          // Initialize the class name for each row because each row may have a
          // different primary class
          $className = 'Qubit'.ucfirst($tableName);

          $primaryKeys = array();
          $foreignKeys = array();
          $values = array();

          foreach ($tableMap->getColumns() as $column)
          {
            $col = strtolower($column->getName());

            if (null === $row[$col])
            {
              continue;
            }

            // Hack: Rely on an explicitly named 'class_name' column of the
            // base class for multi-table inheritance
            if ('class_name' == $col)
            {
              $className = $row[$col];
            }

            if ($column->isForeignKey())
            {
              $relatedTable = $this->dbMap->getTable($column->getRelatedTableName());

              // If we are a subclass of the foreign table then a foreign row
              // will be inserted when we are created.  Merge the foreign
              // values into our values to avoid inserting two rows.
              if ($column->isPrimaryKey() && is_subclass_of($className, 'Qubit'.$relatedTable->getPhpName()))
              {
                $className = $classNames['Qubit'.$relatedTable->getPhpName().'_'.$row[$col]];

                $values += $dumpData[$className][$className.'_'.$row[$col]];
              }
              else
              {
                // Build symbolic foreign keys using the primary class of the
                // related object
                $foreignKeys[$col] = $classNames['Qubit'.$relatedTable->getPhpName().'_'.$row[$col]].'_'.$row[$col];
              }
            }

            if ($column->isPrimaryKey())
            {
              $primaryKeys[$col] = $row[$col];

              // Note: Reuse of $relatedTable outside the above if statement.
              // Consequently it must follow !$column->isForeignKey() ||
              if (!$column->isForeignKey() || is_subclass_of('Qubit'.$tableName, 'Qubit'.$relatedTable->getPhpName()))
              {
                // Set primary keys to explicit values only if the value
                // matches a constant defined by the corresponding class.  We
                // use the first constant whose value matches the value of the
                // primary key.  This is not a rigorous test.
                $class = new ReflectionClass('Qubit'.ucfirst($tableName));
                $constants = array_flip($class->getConstants());
                if (isset($constants[$row[$col]]))
                {
                  // Use a custom ReflectionCode class for wrapping PHP code.
                  // Otherwise it is impossible to distinguish code strings
                  // from literal strings.  If the PHP reflection API supported
                  // a ReflectionConstant, we might be able to use that:
                  // http://ca.php.net/manual/en/language.oop5.reflection.php#82383
                  //
                  // Instead, we use our custom, more general class for
                  // representing PHP code.
                  $values[$col] = new ReflectionCode('echo Qubit'.ucfirst($tableName).'::'.$constants[$row[$col]].'."\n"');
                }
              }
            }

            if (!$column->isForeignKey() && !$column->isPrimaryKey())
            {
              $values[$col] = $row[$col];
            }
          }

          // Roll i18n values into an array in the non-i18n object, for clarity
          if ('I18n' == substr($tableName, -4))
          {
            foreach ($values as $key => $value)
            {
              // Dirty hack: Reuse last value of $relatedTable outside columns
              // loop.  This works because i18n tables are related to exactly
              // one other table.
              $dumpData[$classNames['Qubit'.$relatedTable->getPhpName().'_'.$primaryKeys['id']]][$foreignKeys['id']][$key][$primaryKeys['culture']] = $value;
            }
          }
          else
          {
            $classNames['Qubit'.$tableName.'_'.implode('_', $primaryKeys)] = $className;

            $dumpData[$className][$className.'_'.implode('_', $primaryKeys)] = $foreignKeys + $values;
          }
        }
      }
    }

    return $dumpData;
  }

  /**
   * Fixes the ordering of foreign key data, by outputting data a foreign key depends on before the table with the foreign key.
   *
   * @param array $classes The array with the class names.
   */
  public function fixOrderingOfForeignKeyData($classes)
  {
    // reordering classes to take foreign keys into account
    for ($i = 0, $count = count($classes); $i < $count; $i++)
    {
      $class = $classes[$i];
      $tableMap = $this->dbMap->getTable(constant('Qubit'.$class.'::TABLE_NAME'));
      foreach ($tableMap->getColumns() as $column)
      {
        if ($column->isForeignKey())
        {
          $relatedTable = $this->dbMap->getTable($column->getRelatedTableName());
          $relatedTablePos = array_search($relatedTable->getPhpName(), $classes);

          // check if relatedTable is after the current table
          if ($relatedTablePos > $i)
          {
            // move related table 1 position before current table
            $classes = array_merge(
              array_slice($classes, 0, $i),
              array($classes[$relatedTablePos]),
              array_slice($classes, $i, $relatedTablePos - $i),
              array_slice($classes, $relatedTablePos + 1)
            );

            // we have moved a table, so let's see if we are done
            return $this->fixOrderingOfForeignKeyData($classes);
          }
        }
      }
    }

    return $classes;
  }

  protected function fixOrderingOfForeignKeyDataInSameTable($resultsSets, $tableName, $column, $in = null)
  {
    $sql = sprintf('SELECT * FROM %s WHERE %s %s',
                   constant('Qubit'.$tableName.'::TABLE_NAME'),
                   strtolower($column->getName()),
                   null === $in ? 'IS NULL' : 'IN ('.$in.')');
    $stmt = $this->con->prepare($sql);

    $stmt->execute();

    $in = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
      $in[] = "'".$row[strtolower($column->getRelatedColumnName())]."'";
      $resultsSets[] = $row;
    }

    if ($in = implode(', ', $in))
    {
      $resultsSets = $this->fixOrderingOfForeignKeyDataInSameTable($resultsSets, $tableName, $column, $in);
    }

    return $resultsSets;
  }
}
