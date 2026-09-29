<?php

abstract class BaseOtherNameI18n implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'other_name_i18n',

    NAME = 'other_name_i18n.NAME',
    NOTE = 'other_name_i18n.NOTE',
    DATES = 'other_name_i18n.DATES',
    ID = 'other_name_i18n.ID',
    CULTURE = 'other_name_i18n.CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    $criteria->addSelectColumn(QubitOtherNameI18n::NAME);
    $criteria->addSelectColumn(QubitOtherNameI18n::NOTE);
    $criteria->addSelectColumn(QubitOtherNameI18n::DATES);
    $criteria->addSelectColumn(QubitOtherNameI18n::ID);
    $criteria->addSelectColumn(QubitOtherNameI18n::CULTURE);

    return $criteria;
  }

  protected static
    $otherNameI18ns = array();

  protected
    $keys = array(),
    $row = array();

  public static function getFromRow(array $row)
  {
    $keys = array();
    $keys['id'] = $row[3];
    $keys['culture'] = $row[4];

    $key = serialize($keys);
    if (!isset(self::$otherNameI18ns[$key]))
    {
      $otherNameI18n = new QubitOtherNameI18n;

      $otherNameI18n->keys = $keys;
      $otherNameI18n->row = $row;

      $otherNameI18n->new = false;

      self::$otherNameI18ns[$key] = $otherNameI18n;
    }

    return self::$otherNameI18ns[$key];
  }

  public static function clearCache()
  {
    self::$otherNameI18ns = array();
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitOtherNameI18n::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitOtherNameI18n', $options);
  }

  public static function getAll(array $options = array())
  {
    return self::get(new Criteria, $options);
  }

  public static function getOne(Criteria $criteria, array $options = array())
  {
    $criteria->setLimit(1);

    return self::get($criteria, $options)->__get(0, array('defaultValue' => null));
  }

  public static function getByIdAndCulture($id, $culture, array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitOtherNameI18n::ID, $id);
    $criteria->add(QubitOtherNameI18n::CULTURE, $culture);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public static function doDelete(Criteria $criteria, $connection = null)
  {
    if (!isset($connection))
    {
      $connection = Propel::getConnection();
    }

    $affectedRows = 0;

    $affectedRows += BasePeer::doDelete($criteria, $connection);

    return $affectedRows;
  }

  protected
    $tables = array();

  public function __construct()
  {
    $this->tables[] = Propel::getDatabaseMap(QubitOtherNameI18n::DATABASE_NAME)->getTable(QubitOtherNameI18n::TABLE_NAME);
  }

  protected
    $values = array(),
    $refFkValues = array();

  protected function rowOffsetGet($name, $offset, $options)
  {
    if (empty($options['clean']) && array_key_exists($name, $this->values))
    {
      return $this->values[$name];
    }

    if (array_key_exists($name, $this->keys))
    {
      return $this->keys[$name];
    }

    if (is_array($this->row) && !array_key_exists($offset, $this->row))
    {
      if ($this->new)
      {
        return;
      }

      if (!isset($options['connection']))
      {
        $options['connection'] = Propel::getConnection(QubitOtherNameI18n::DATABASE_NAME);
      }

      $criteria = new Criteria;
      $criteria->add(QubitOtherNameI18n::ID, $this->id);
      $criteria->add(QubitOtherNameI18n::CULTURE, $this->culture);

      call_user_func(array(get_class($this), 'addSelectColumns'), $criteria);

      $statement = BasePeer::doSelect($criteria, $options['connection']);
      $this->row = $statement->fetch();
    }

    return $this->row[$offset];
  }

  public function __isset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    $offset = 0;
    foreach ($this->tables as $table)
    {
      foreach ($table->getColumns() as $column)
      {
        if ($name == $column->getPhpName())
        {
          return null !== $this->rowOffsetGet($name, $offset, $options);
        }

        if ("{$name}Id" == $column->getPhpName())
        {
          return null !== $this->rowOffsetGet("{$name}Id", $offset, $options);
        }

        $offset++;
      }
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  #[\ReturnTypeWillChange]
  public function offsetExists($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__isset'), $args);
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    $offset = 0;
    foreach ($this->tables as $table)
    {
      foreach ($table->getColumns() as $column)
      {
        if ($name == $column->getPhpName())
        {
          return $this->rowOffsetGet($name, $offset, $options);
        }

        if ("{$name}Id" == $column->getPhpName())
        {
          $relatedTable = $column->getTable()->getDatabaseMap()->getTable($column->getRelatedTableName());

          return call_user_func(array($relatedTable->getClassName(), 'getBy'.ucfirst($relatedTable->getColumn($column->getRelatedColumnName())->getPhpName())), $this->rowOffsetGet("{$name}Id", $offset, $options));
        }

        $offset++;
      }
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  #[\ReturnTypeWillChange]
  public function offsetGet($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__get'), $args);
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    $offset = 0;
    foreach ($this->tables as $table)
    {
      foreach ($table->getColumns() as $column)
      {
        // Foreign key column name
        $nameId = $name.'Id';

        // Set local column values
        if ($name === $column->getPhpName())
        {
          $this->values[$name] = $value;
        }

        // If this is a foreign key column then get primary key from related table
        else if ($nameId === $column->getPhpName())
        {
          if(!empty($value))
          {
            $relatedTable = $column->getTable()->getDatabaseMap()->getTable($column->getRelatedTableName());

            $this->values[$nameId] = $value->__get($relatedTable->getColumn($column->getRelatedColumnName())->getPhpName(), $options);
          }
          else
          {
            // If $value is null, then don't try and fetch related object for primary key
            $this->values[$nameId] = null;
          }
        }

        $offset++;
      }
    }

    return $this;
  }

  #[\ReturnTypeWillChange]
  public function offsetSet($offset, $value)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__set'), $args);
  }

  public function __unset($name)
  {
    $offset = 0;
    foreach ($this->tables as $table)
    {
      foreach ($table->getColumns() as $column)
      {
        if ($name == $column->getPhpName())
        {
          $this->values[$name] = null;
        }

        if ("{$name}Id" == $column->getPhpName())
        {
          $this->values["{$name}Id"] = null;
        }

        $offset++;
      }
    }

    return $this;
  }

  #[\ReturnTypeWillChange]
  public function offsetUnset($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__unset'), $args);
  }

  public function clear()
  {
    $this->row = $this->values = array();

    return $this;
  }

  protected
    $new = true;

  protected
    $deleted = false;

  /**
   * Insert new translations in groups that share the same populated columns.
   *
   * A multi-row INSERT reduces database round trips when a new record contains
   * several translations. If any translation already exists, use save() for
   * every object so updates retain their existing behavior. Grouping by column
   * set also preserves database defaults for fields omitted from sparse rows.
   */
  public static function bulkSave(array $objects, $connection = null)
  {
    if (0 == count($objects))
    {
      return;
    }

    if (!isset($connection))
    {
      $connection = Propel::getConnection();
    }

    $hasExistingObjects = false;
    $newObjects = array();
    foreach ($objects as $object)
    {
      if ($object->deleted)
      {
        throw new PropelException('You cannot save an object that has been deleted.');
      }

      if ($object->new)
      {
        $newObjects[] = $object;
      }
      else
      {
        $hasExistingObjects = true;
      }
    }

    if ($hasExistingObjects)
    {
      foreach ($objects as $object)
      {
        $object->save($connection);
      }

      return;
    }

    if (0 == count($newObjects))
    {
      return;
    }

    $databaseMap = Propel::getDatabaseMap(self::DATABASE_NAME);
    $database = Propel::getDB(self::DATABASE_NAME);
    $table = $databaseMap->getTable(self::TABLE_NAME);
    $columns = $table->getColumns();
    $insertGroups = array();
    foreach ($newObjects as $object)
    {
      $insertColumns = array();
      $insertParameters = array();
      foreach ($columns as $column)
      {
        if (!array_key_exists($column->getPhpName(), $object->values))
        {
          if ('createdAt' == $column->getPhpName() || 'updatedAt' == $column->getPhpName())
          {
            $object->values[$column->getPhpName()] = new DateTime;
          }

          if ('sourceCulture' == $column->getPhpName())
          {
            $object->values['sourceCulture'] = sfPropel::getDefaultCulture();
          }
        }

        if (array_key_exists($column->getPhpName(), $object->values))
        {
          $param = $object->param($column);
          if (null !== $param)
          {
            $insertColumns[$column->getPhpName()] = $column;
            $insertParameters[$column->getPhpName()] = $param;
          }
        }
      }

      $groupKey = implode("\0", array_keys($insertColumns));
      if (!isset($insertGroups[$groupKey]))
      {
        $insertGroups[$groupKey] = array(
          'columns' => $insertColumns,
          'rows' => array(),
        );
      }

      $insertGroups[$groupKey]['rows'][] = array(
        'object' => $object,
        'parameters' => $insertParameters,
      );
    }

    foreach ($insertGroups as $insertGroup)
    {
      if (0 == count($insertGroup['columns']))
      {
        foreach ($insertGroup['rows'] as $row)
        {
          $row['object']->save($connection);
        }

        continue;
      }

      $columnNames = array();
      foreach ($insertGroup['columns'] as $column)
      {
        $columnName = $column->getName();
        if ($database->useQuoteIdentifier())
        {
          $columnName = $database->quoteIdentifier($columnName);
        }

        $columnNames[] = $columnName;
      }

      $parameterIndex = 1;
      $placeholders = array();
      $parameters = array();
      foreach ($insertGroup['rows'] as $row)
      {
        $rowPlaceholders = array();
        foreach ($insertGroup['columns'] as $column)
        {
          $rowPlaceholders[] = ':p'.$parameterIndex++;
          $parameters[] = array(
            'column' => $column->getName(),
            'table' => self::TABLE_NAME,
            'value' => $row['parameters'][$column->getPhpName()],
          );
        }

        $placeholders[] = '('.implode(', ', $rowPlaceholders).')';
      }

      $sql = 'INSERT INTO '.self::TABLE_NAME.' ('.implode(', ', $columnNames).') VALUES '.implode(', ', $placeholders);

      try
      {
        $statement = $connection->prepare($sql);
        BasePeer::populateStmtValues($statement, $parameters, $databaseMap, $database);
        $statement->execute();
      }
      catch (Exception $e)
      {
        Propel::log($e->getMessage(), Propel::LOG_ERR);

        throw new PropelException('Unable to execute INSERT statement.', $e);
      }

      foreach ($insertGroup['rows'] as $row)
      {
        $object = $row['object'];
        $offset = 0;
        foreach ($object->tables as $table)
        {
          foreach ($table->getColumns() as $column)
          {
            if (array_key_exists($column->getPhpName(), $object->values))
            {
              $object->row[$offset] = $object->values[$column->getPhpName()];
            }

            if ($object->new && $column->isPrimaryKey())
            {
              $object->keys[$column->getPhpName()] = $object->values[$column->getPhpName()];
            }

            $offset++;
          }
        }

        $object->new = false;
        $object->values = array();
      }
    }
  }

  public function save($connection = null)
  {
    if ($this->deleted)
    {
      throw new PropelException('You cannot save an object that has been deleted.');
    }

    if ($this->new)
    {
      $this->insert($connection);
    }
    else
    {
      $this->update($connection);
    }

    $offset = 0;
    foreach ($this->tables as $table)
    {
      foreach ($table->getColumns() as $column)
      {
        if (array_key_exists($column->getPhpName(), $this->values))
        {
          $this->row[$offset] = $this->values[$column->getPhpName()];
        }

        if ($this->new && $column->isPrimaryKey())
        {
          $this->keys[$column->getPhpName()] = $this->values[$column->getPhpName()];
        }

        $offset++;
      }
    }

    $this->new = false;
    $this->values = array();

    return $this;
  }

  protected function param($column)
  {
    $value = $this->values[$column->getPhpName()];

    // Convert to DateTime or SQL zero special case
    if (isset($value) && $column->isTemporal() && !$value instanceof DateTime)
    {
      // Year only: one or more digits.  Convert to SQL zero special case
      if (preg_match('/^\d+$/', $value))
      {
        $value .= '-0-0';
      }

      // Year and month only: one or more digits, plus separator, plus
      // one or more digits.  Convert to SQL zero special case
      else if (preg_match('/^\d+[-\/]\d+$/', $value))
      {
        $value .= '-0';
      }

      // Convert to DateTime if not SQL zero special case: year plus
      // separator plus zero to twelve (possibly zero padded) plus
      // separator plus one or more zeros
      if (!preg_match('/^\d+[-\/]0*(?:1[0-2]|\d)[-\/]0+$/', $value))
      {
        try
        {
          $value = new DateTime($value);
        }
        catch (Exception $e)
        {
          return null;
        }
      }
    }

    return $value;
  }

  protected function insert($connection = null)
  {
    if (!isset($connection))
    {
      $connection = Propel::getConnection();
    }

    $offset = 0;
    foreach ($this->tables as $table)
    {
      $criteria = new Criteria;
      foreach ($table->getColumns() as $column)
      {
        if (!array_key_exists($column->getPhpName(), $this->values))
        {
          if ('createdAt' == $column->getPhpName() || 'updatedAt' == $column->getPhpName())
          {
            $this->values[$column->getPhpName()] = new DateTime;
          }

          if ('sourceCulture' == $column->getPhpName())
          {
            $this->values['sourceCulture'] = sfPropel::getDefaultCulture();
          }
        }

        if (array_key_exists($column->getPhpName(), $this->values))
        {
          if (null !== $param = $this->param($column))
          {
            $criteria->add($column->getFullyQualifiedName(), $param);
          }
        }

        $offset++;
      }

      if (null !== $id = BasePeer::doInsert($criteria, $connection))
      {
        // Guess that the first primary key of the first table is auto
        // incremented
        if ($this->tables[0] == $table)
        {
          $columns = $table->getPrimaryKeyColumns();
          $this->values[$columns[0]->getPhpName()] = $this->keys[$columns[0]->getPhpName()] = $id;
        }
      }
    }

    return $this;
  }

  protected function update($connection = null)
  {
    if (!isset($connection))
    {
      $connection = Propel::getConnection();
    }

    $offset = 0;
    foreach ($this->tables as $table)
    {
      $criteria = new Criteria;
      $selectCriteria = new Criteria;
      foreach ($table->getColumns() as $column)
      {
        if (!array_key_exists($column->getPhpName(), $this->values))
        {
          if ('updatedAt' == $column->getPhpName())
          {
            $this->values['updatedAt'] = new DateTime;
          }
        }

        if (array_key_exists($column->getPhpName(), $this->values))
        {
          if ('serialNumber' == $column->getPhpName())
          {
            $selectCriteria->add($column->getFullyQualifiedName(), $this->values[$column->getPhpName()]++);
          }

          $criteria->add($column->getFullyQualifiedName(), $this->param($column));
        }

        if ($column->isPrimaryKey())
        {
          $selectCriteria->add($column->getFullyQualifiedName(), $this->keys[$column->getPhpName()]);
        }

        $offset++;
      }

      if (0 < $criteria->size())
      {
        BasePeer::doUpdate($selectCriteria, $criteria, $connection);
      }
    }

    return $this;
  }

  public function delete($connection = null)
  {
    if ($this->deleted)
    {
      throw new PropelException('This object has already been deleted.');
    }

    $criteria = new Criteria;
    $criteria->add(QubitOtherNameI18n::ID, $this->id);
    $criteria->add(QubitOtherNameI18n::CULTURE, $this->culture);

    self::doDelete($criteria, $connection);

    $this->deleted = true;

    return $this;
  }

	/**
	 * Returns the composite primary key for this object.
	 * The array elements will be in same order as specified in XML.
	 * @return     array
	 */
	public function getPrimaryKey()
	{
		$pks = array();

		$pks[0] = $this->getid();

		$pks[1] = $this->getculture();

		return $pks;
	}

	/**
	 * Set the [composite] primary key.
	 *
	 * @param      array $keys The elements of the composite key (order must match the order in XML file).
	 * @return     void
	 */
	public function setPrimaryKey($keys)
	{

		$this->setid($keys[0]);

		$this->setculture($keys[1]);

	}

  public static function addJoinotherNameCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitOtherNameI18n::ID, QubitOtherName::ID);

    return $criteria;
  }

  public function __call($name, $args)
  {
    if ('get' == substr($name, 0, 3) || 'set' == substr($name, 0, 3))
    {
      $args = array_merge(array(strtolower(substr($name, 3, 1)).substr($name, 4)), $args);

      return call_user_func_array(array($this, '__'.substr($name, 0, 3)), $args);
    }

    throw new sfException('Call to undefined method '.get_class($this)."::$name");
  }
}
