<?php

abstract class BaseObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'object',

    CLASS_NAME = 'object.CLASS_NAME',
    CREATED_AT = 'object.CREATED_AT',
    UPDATED_AT = 'object.UPDATED_AT',
    ID = 'object.ID',
    SERIAL_NUMBER = 'object.SERIAL_NUMBER';

  public static function addSelectColumns(Criteria $criteria)
  {
    $criteria->addSelectColumn(QubitObject::CLASS_NAME);
    $criteria->addSelectColumn(QubitObject::CREATED_AT);
    $criteria->addSelectColumn(QubitObject::UPDATED_AT);
    $criteria->addSelectColumn(QubitObject::ID);
    $criteria->addSelectColumn(QubitObject::SERIAL_NUMBER);

    return $criteria;
  }

  protected static
    $objects = array();

  protected
    $keys = array(),
    $row = array();

  public static function getFromRow(array $row)
  {
    $keys = array();
    $keys['id'] = $row[3];

    $key = serialize($keys);
    if (!isset(self::$objects[$key]))
    {
      $object = new $row[0];

      $object->keys = $keys;
      $object->row = $row;

      $object->new = false;

      self::$objects[$key] = $object;
    }

    return self::$objects[$key];
  }

  public static function clearCache()
  {
    self::$objects = array();
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitObject::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitObject', $options);
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

  public static function getById($id, array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitObject::ID, $id);

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
    $this->values['className'] = get_class($this);

    $this->tables[] = Propel::getDatabaseMap(QubitObject::DATABASE_NAME)->getTable(QubitObject::TABLE_NAME);
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
        $options['connection'] = Propel::getConnection(QubitObject::DATABASE_NAME);
      }

      $criteria = new Criteria;
      $criteria->add(QubitObject::ID, $this->id);

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

    if ('accessLogs' == $name)
    {
      return true;
    }

    if ('aipsRelatedBypartOf' == $name)
    {
      return true;
    }

    if ('jobsRelatedByobjectId' == $name)
    {
      return true;
    }

    if ('eventsRelatedByobjectId' == $name)
    {
      return true;
    }

    if ('notes' == $name)
    {
      return true;
    }

    if ('objectTermRelationsRelatedByobjectId' == $name)
    {
      return true;
    }

    if ('otherNames' == $name)
    {
      return true;
    }

    if ('propertys' == $name)
    {
      return true;
    }

    if ('relationsRelatedBysubjectId' == $name)
    {
      return true;
    }

    if ('relationsRelatedByobjectId' == $name)
    {
      return true;
    }

    if ('slugs' == $name)
    {
      return true;
    }

    if ('statuss' == $name)
    {
      return true;
    }

    if ('aclPermissions' == $name)
    {
      return true;
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

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

    if ('accessLogs' == $name)
    {
      if (!isset($this->refFkValues['accessLogs']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessLogs'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessLogs'] = self::getaccessLogsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessLogs'];
    }

    if ('aipsRelatedBypartOf' == $name)
    {
      if (!isset($this->refFkValues['aipsRelatedBypartOf']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['aipsRelatedBypartOf'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['aipsRelatedBypartOf'] = self::getaipsRelatedBypartOfById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['aipsRelatedBypartOf'];
    }

    if ('jobsRelatedByobjectId' == $name)
    {
      if (!isset($this->refFkValues['jobsRelatedByobjectId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['jobsRelatedByobjectId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['jobsRelatedByobjectId'] = self::getjobsRelatedByobjectIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['jobsRelatedByobjectId'];
    }

    if ('eventsRelatedByobjectId' == $name)
    {
      if (!isset($this->refFkValues['eventsRelatedByobjectId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['eventsRelatedByobjectId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['eventsRelatedByobjectId'] = self::geteventsRelatedByobjectIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['eventsRelatedByobjectId'];
    }

    if ('notes' == $name)
    {
      if (!isset($this->refFkValues['notes']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['notes'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['notes'] = self::getnotesById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['notes'];
    }

    if ('objectTermRelationsRelatedByobjectId' == $name)
    {
      if (!isset($this->refFkValues['objectTermRelationsRelatedByobjectId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['objectTermRelationsRelatedByobjectId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['objectTermRelationsRelatedByobjectId'] = self::getobjectTermRelationsRelatedByobjectIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['objectTermRelationsRelatedByobjectId'];
    }

    if ('otherNames' == $name)
    {
      if (!isset($this->refFkValues['otherNames']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['otherNames'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['otherNames'] = self::getotherNamesById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['otherNames'];
    }

    if ('propertys' == $name)
    {
      if (!isset($this->refFkValues['propertys']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['propertys'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['propertys'] = self::getpropertysById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['propertys'];
    }

    if ('relationsRelatedBysubjectId' == $name)
    {
      if (!isset($this->refFkValues['relationsRelatedBysubjectId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['relationsRelatedBysubjectId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['relationsRelatedBysubjectId'] = self::getrelationsRelatedBysubjectIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['relationsRelatedBysubjectId'];
    }

    if ('relationsRelatedByobjectId' == $name)
    {
      if (!isset($this->refFkValues['relationsRelatedByobjectId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['relationsRelatedByobjectId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['relationsRelatedByobjectId'] = self::getrelationsRelatedByobjectIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['relationsRelatedByobjectId'];
    }

    if ('slugs' == $name)
    {
      if (!isset($this->refFkValues['slugs']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['slugs'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['slugs'] = self::getslugsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['slugs'];
    }

    if ('statuss' == $name)
    {
      if (!isset($this->refFkValues['statuss']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['statuss'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['statuss'] = self::getstatussById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['statuss'];
    }

    if ('aclPermissions' == $name)
    {
      if (!isset($this->refFkValues['aclPermissions']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['aclPermissions'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['aclPermissions'] = self::getaclPermissionsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['aclPermissions'];
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

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
    $criteria->add(QubitObject::ID, $this->id);

    self::doDelete($criteria, $connection);

    $this->deleted = true;

    return $this;
  }

	/**
	 * Returns the primary key for this object (row).
	 * @return     int
	 */
	public function getPrimaryKey()
	{
		return $this->getid();
	}

	/**
	 * Generic method to set the primary key (id column).
	 *
	 * @param      int $key Primary key.
	 * @return     void
	 */
	public function setPrimaryKey($key)
	{
		$this->setid($key);
	}

  public static function addaccessLogsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccessLog::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getaccessLogsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessLogsCriteriaById($criteria, $id);

    return QubitAccessLog::get($criteria, $options);
  }

  public function addaccessLogsCriteria(Criteria $criteria)
  {
    return self::addaccessLogsCriteriaById($criteria, $this->id);
  }

  public static function addaipsRelatedBypartOfCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAip::PART_OF, $id);

    return $criteria;
  }

  public static function getaipsRelatedBypartOfById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaipsRelatedBypartOfCriteriaById($criteria, $id);

    return QubitAip::get($criteria, $options);
  }

  public function addaipsRelatedBypartOfCriteria(Criteria $criteria)
  {
    return self::addaipsRelatedBypartOfCriteriaById($criteria, $this->id);
  }

  public static function addjobsRelatedByobjectIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitJob::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getjobsRelatedByobjectIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addjobsRelatedByobjectIdCriteriaById($criteria, $id);

    return QubitJob::get($criteria, $options);
  }

  public function addjobsRelatedByobjectIdCriteria(Criteria $criteria)
  {
    return self::addjobsRelatedByobjectIdCriteriaById($criteria, $this->id);
  }

  public static function addeventsRelatedByobjectIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitEvent::OBJECT_ID, $id);

    return $criteria;
  }

  public static function geteventsRelatedByobjectIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addeventsRelatedByobjectIdCriteriaById($criteria, $id);

    return QubitEvent::get($criteria, $options);
  }

  public function addeventsRelatedByobjectIdCriteria(Criteria $criteria)
  {
    return self::addeventsRelatedByobjectIdCriteriaById($criteria, $this->id);
  }

  public static function addnotesCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitNote::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getnotesById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addnotesCriteriaById($criteria, $id);

    return QubitNote::get($criteria, $options);
  }

  public function addnotesCriteria(Criteria $criteria)
  {
    return self::addnotesCriteriaById($criteria, $this->id);
  }

  public static function addobjectTermRelationsRelatedByobjectIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitObjectTermRelation::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getobjectTermRelationsRelatedByobjectIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addobjectTermRelationsRelatedByobjectIdCriteriaById($criteria, $id);

    return QubitObjectTermRelation::get($criteria, $options);
  }

  public function addobjectTermRelationsRelatedByobjectIdCriteria(Criteria $criteria)
  {
    return self::addobjectTermRelationsRelatedByobjectIdCriteriaById($criteria, $this->id);
  }

  public static function addotherNamesCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitOtherName::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getotherNamesById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addotherNamesCriteriaById($criteria, $id);

    return QubitOtherName::get($criteria, $options);
  }

  public function addotherNamesCriteria(Criteria $criteria)
  {
    return self::addotherNamesCriteriaById($criteria, $this->id);
  }

  public static function addpropertysCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitProperty::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getpropertysById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addpropertysCriteriaById($criteria, $id);

    return QubitProperty::get($criteria, $options);
  }

  public function addpropertysCriteria(Criteria $criteria)
  {
    return self::addpropertysCriteriaById($criteria, $this->id);
  }

  public static function addrelationsRelatedBysubjectIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRelation::SUBJECT_ID, $id);

    return $criteria;
  }

  public static function getrelationsRelatedBysubjectIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrelationsRelatedBysubjectIdCriteriaById($criteria, $id);

    return QubitRelation::get($criteria, $options);
  }

  public function addrelationsRelatedBysubjectIdCriteria(Criteria $criteria)
  {
    return self::addrelationsRelatedBysubjectIdCriteriaById($criteria, $this->id);
  }

  public static function addrelationsRelatedByobjectIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRelation::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getrelationsRelatedByobjectIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrelationsRelatedByobjectIdCriteriaById($criteria, $id);

    return QubitRelation::get($criteria, $options);
  }

  public function addrelationsRelatedByobjectIdCriteria(Criteria $criteria)
  {
    return self::addrelationsRelatedByobjectIdCriteriaById($criteria, $this->id);
  }

  public static function addslugsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitSlug::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getslugsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addslugsCriteriaById($criteria, $id);

    return QubitSlug::get($criteria, $options);
  }

  public function addslugsCriteria(Criteria $criteria)
  {
    return self::addslugsCriteriaById($criteria, $this->id);
  }

  public static function addstatussCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitStatus::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getstatussById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addstatussCriteriaById($criteria, $id);

    return QubitStatus::get($criteria, $options);
  }

  public function addstatussCriteria(Criteria $criteria)
  {
    return self::addstatussCriteriaById($criteria, $this->id);
  }

  public static function addaclPermissionsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAclPermission::OBJECT_ID, $id);

    return $criteria;
  }

  public static function getaclPermissionsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaclPermissionsCriteriaById($criteria, $id);

    return QubitAclPermission::get($criteria, $options);
  }

  public function addaclPermissionsCriteria(Criteria $criteria)
  {
    return self::addaclPermissionsCriteriaById($criteria, $this->id);
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
