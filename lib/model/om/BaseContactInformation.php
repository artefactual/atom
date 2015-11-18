<?php

abstract class BaseContactInformation implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'contact_information',

    ACTOR_ID = 'contact_information.ACTOR_ID',
    PRIMARY_CONTACT = 'contact_information.PRIMARY_CONTACT',
    CONTACT_PERSON = 'contact_information.CONTACT_PERSON',
    STREET_ADDRESS = 'contact_information.STREET_ADDRESS',
    WEBSITE = 'contact_information.WEBSITE',
    EMAIL = 'contact_information.EMAIL',
    TELEPHONE = 'contact_information.TELEPHONE',
    FAX = 'contact_information.FAX',
    POSTAL_CODE = 'contact_information.POSTAL_CODE',
    COUNTRY_CODE = 'contact_information.COUNTRY_CODE',
    LONGITUDE = 'contact_information.LONGITUDE',
    LATITUDE = 'contact_information.LATITUDE',
    CREATED_AT = 'contact_information.CREATED_AT',
    UPDATED_AT = 'contact_information.UPDATED_AT',
    SOURCE_CULTURE = 'contact_information.SOURCE_CULTURE',
    ID = 'contact_information.ID',
    SERIAL_NUMBER = 'contact_information.SERIAL_NUMBER';

  public static function addSelectColumns(Criteria $criteria)
  {
    $criteria->addSelectColumn(QubitContactInformation::ACTOR_ID);
    $criteria->addSelectColumn(QubitContactInformation::PRIMARY_CONTACT);
    $criteria->addSelectColumn(QubitContactInformation::CONTACT_PERSON);
    $criteria->addSelectColumn(QubitContactInformation::STREET_ADDRESS);
    $criteria->addSelectColumn(QubitContactInformation::WEBSITE);
    $criteria->addSelectColumn(QubitContactInformation::EMAIL);
    $criteria->addSelectColumn(QubitContactInformation::TELEPHONE);
    $criteria->addSelectColumn(QubitContactInformation::FAX);
    $criteria->addSelectColumn(QubitContactInformation::POSTAL_CODE);
    $criteria->addSelectColumn(QubitContactInformation::COUNTRY_CODE);
    $criteria->addSelectColumn(QubitContactInformation::LONGITUDE);
    $criteria->addSelectColumn(QubitContactInformation::LATITUDE);
    $criteria->addSelectColumn(QubitContactInformation::CREATED_AT);
    $criteria->addSelectColumn(QubitContactInformation::UPDATED_AT);
    $criteria->addSelectColumn(QubitContactInformation::SOURCE_CULTURE);
    $criteria->addSelectColumn(QubitContactInformation::ID);
    $criteria->addSelectColumn(QubitContactInformation::SERIAL_NUMBER);

    return $criteria;
  }

  protected static
    $contactInformations = array();

  protected
    $keys = array(),
    $row = array();

  public static function getFromRow(array $row)
  {
    $keys = array();
    $keys['id'] = $row[15];

    $key = serialize($keys);
    if (!isset(self::$contactInformations[$key]))
    {
      $contactInformation = new QubitContactInformation;

      $contactInformation->keys = $keys;
      $contactInformation->row = $row;

      $contactInformation->new = false;

      self::$contactInformations[$key] = $contactInformation;
    }

    return self::$contactInformations[$key];
  }

  public static function clearCache()
  {
    self::$contactInformations = array();
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitContactInformation::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitContactInformation', $options);
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
    $criteria->add(QubitContactInformation::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public static function doDelete(Criteria $criteria, $connection = null)
  {
    if (!isset($connection))
    {
      $connection = QubitTransactionFilter::getConnection(QubitContactInformation::DATABASE_NAME);
    }

    $affectedRows = 0;

    $affectedRows += BasePeer::doDelete($criteria, $connection);

    return $affectedRows;
  }

  protected
    $tables = array();

  public function __construct()
  {
    $this->tables[] = Propel::getDatabaseMap(QubitContactInformation::DATABASE_NAME)->getTable(QubitContactInformation::TABLE_NAME);
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
        $options['connection'] = Propel::getConnection(QubitContactInformation::DATABASE_NAME);
      }

      $criteria = new Criteria;
      $criteria->add(QubitContactInformation::ID, $this->id);

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

    if ('contactInformationI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentcontactInformationI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentcontactInformationI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
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

    if ('contactInformationI18ns' == $name)
    {
      if (!isset($this->refFkValues['contactInformationI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['contactInformationI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['contactInformationI18ns'] = self::getcontactInformationI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['contactInformationI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentcontactInformationI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentcontactInformationI18n(array('sourceCulture' => true) + $options), '__get'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
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

    call_user_func_array(array($this->getCurrentcontactInformationI18n($options), '__set'), $args);

    return $this;
  }

  public function offsetSet($offset, $value)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__set'), $args);
  }

  public function __unset($name)
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
          $this->values[$name] = null;
        }

        if ("{$name}Id" == $column->getPhpName())
        {
          $this->values["{$name}Id"] = null;
        }

        $offset++;
      }
    }

    call_user_func_array(array($this->getCurrentcontactInformationI18n($options), '__unset'), $args);

    return $this;
  }

  public function offsetUnset($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__unset'), $args);
  }

  public function clear()
  {
    foreach ($this->contactInformationI18ns as $contactInformationI18n)
    {
      $contactInformationI18n->clear();
    }

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

    foreach ($this->contactInformationI18ns as $contactInformationI18n)
    {
      $contactInformationI18n->id = $this->id;

      $contactInformationI18n->save($connection);
    }

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
      $connection = QubitTransactionFilter::getConnection(QubitContactInformation::DATABASE_NAME);
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
      $connection = QubitTransactionFilter::getConnection(QubitContactInformation::DATABASE_NAME);
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
    $criteria->add(QubitContactInformation::ID, $this->id);

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

  public static function addJoinactorCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitContactInformation::ACTOR_ID, QubitActor::ID);

    return $criteria;
  }

  public static function addcontactInformationI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitContactInformationI18n::ID, $id);

    return $criteria;
  }

  public static function getcontactInformationI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addcontactInformationI18nsCriteriaById($criteria, $id);

    return QubitContactInformationI18n::get($criteria, $options);
  }

  public function addcontactInformationI18nsCriteria(Criteria $criteria)
  {
    return self::addcontactInformationI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentcontactInformationI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $contactInformationI18ns = $this->contactInformationI18ns->indexBy('culture');
    if (!isset($contactInformationI18ns[$options['culture']]))
    {
      $contactInformationI18ns[$options['culture']] = new QubitContactInformationI18n;
    }

    return $contactInformationI18ns[$options['culture']];
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
