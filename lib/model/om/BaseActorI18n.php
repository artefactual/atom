<?php

abstract class BaseActorI18n implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'actor_i18n',

    AUTHORIZED_FORM_OF_NAME = 'actor_i18n.AUTHORIZED_FORM_OF_NAME',
    DATES_OF_EXISTENCE = 'actor_i18n.DATES_OF_EXISTENCE',
    HISTORY = 'actor_i18n.HISTORY',
    PLACES = 'actor_i18n.PLACES',
    LEGAL_STATUS = 'actor_i18n.LEGAL_STATUS',
    FUNCTIONS = 'actor_i18n.FUNCTIONS',
    MANDATES = 'actor_i18n.MANDATES',
    INTERNAL_STRUCTURES = 'actor_i18n.INTERNAL_STRUCTURES',
    GENERAL_CONTEXT = 'actor_i18n.GENERAL_CONTEXT',
    INSTITUTION_RESPONSIBLE_IDENTIFIER = 'actor_i18n.INSTITUTION_RESPONSIBLE_IDENTIFIER',
    RULES = 'actor_i18n.RULES',
    SOURCES = 'actor_i18n.SOURCES',
    REVISION_HISTORY = 'actor_i18n.REVISION_HISTORY',
    ID = 'actor_i18n.ID',
    CULTURE = 'actor_i18n.CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    $criteria->addSelectColumn(QubitActorI18n::AUTHORIZED_FORM_OF_NAME);
    $criteria->addSelectColumn(QubitActorI18n::DATES_OF_EXISTENCE);
    $criteria->addSelectColumn(QubitActorI18n::HISTORY);
    $criteria->addSelectColumn(QubitActorI18n::PLACES);
    $criteria->addSelectColumn(QubitActorI18n::LEGAL_STATUS);
    $criteria->addSelectColumn(QubitActorI18n::FUNCTIONS);
    $criteria->addSelectColumn(QubitActorI18n::MANDATES);
    $criteria->addSelectColumn(QubitActorI18n::INTERNAL_STRUCTURES);
    $criteria->addSelectColumn(QubitActorI18n::GENERAL_CONTEXT);
    $criteria->addSelectColumn(QubitActorI18n::INSTITUTION_RESPONSIBLE_IDENTIFIER);
    $criteria->addSelectColumn(QubitActorI18n::RULES);
    $criteria->addSelectColumn(QubitActorI18n::SOURCES);
    $criteria->addSelectColumn(QubitActorI18n::REVISION_HISTORY);
    $criteria->addSelectColumn(QubitActorI18n::ID);
    $criteria->addSelectColumn(QubitActorI18n::CULTURE);

    return $criteria;
  }

  protected static
    $actorI18ns = array();

  protected
    $keys = array(),
    $row = array();

  public static function getFromRow(array $row)
  {
    $keys = array();
    $keys['id'] = $row[13];
    $keys['culture'] = $row[14];

    $key = serialize($keys);
    if (!isset(self::$actorI18ns[$key]))
    {
      $actorI18n = new QubitActorI18n;

      $actorI18n->keys = $keys;
      $actorI18n->row = $row;

      $actorI18n->new = false;

      self::$actorI18ns[$key] = $actorI18n;
    }

    return self::$actorI18ns[$key];
  }

  public static function clearCache()
  {
    self::$actorI18ns = array();
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitActorI18n::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitActorI18n', $options);
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
    $criteria->add(QubitActorI18n::ID, $id);
    $criteria->add(QubitActorI18n::CULTURE, $culture);

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
    $this->tables[] = Propel::getDatabaseMap(QubitActorI18n::DATABASE_NAME)->getTable(QubitActorI18n::TABLE_NAME);
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

    if (!array_key_exists($offset, $this->row))
    {
      if ($this->new)
      {
        return;
      }

      if (!isset($options['connection']))
      {
        $options['connection'] = Propel::getConnection(QubitActorI18n::DATABASE_NAME);
      }

      $criteria = new Criteria;
      $criteria->add(QubitActorI18n::ID, $this->id);
      $criteria->add(QubitActorI18n::CULTURE, $this->culture);

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
    $criteria->add(QubitActorI18n::ID, $this->id);
    $criteria->add(QubitActorI18n::CULTURE, $this->culture);

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

  public static function addJoinactorCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitActorI18n::ID, QubitActor::ID);

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
