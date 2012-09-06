<?php

abstract class BaseDeaccession extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'deaccession',

    ID = 'deaccession.ID',
    ACCESSION_ID = 'deaccession.ACCESSION_ID',
    DATE = 'deaccession.DATE',
    IDENTIFIER = 'deaccession.IDENTIFIER',
    SCOPE_ID = 'deaccession.SCOPE_ID',
    CREATED_AT = 'deaccession.CREATED_AT',
    UPDATED_AT = 'deaccession.UPDATED_AT',
    SOURCE_CULTURE = 'deaccession.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitDeaccession::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitDeaccession::ID);
    $criteria->addSelectColumn(QubitDeaccession::ACCESSION_ID);
    $criteria->addSelectColumn(QubitDeaccession::DATE);
    $criteria->addSelectColumn(QubitDeaccession::IDENTIFIER);
    $criteria->addSelectColumn(QubitDeaccession::SCOPE_ID);
    $criteria->addSelectColumn(QubitDeaccession::CREATED_AT);
    $criteria->addSelectColumn(QubitDeaccession::UPDATED_AT);
    $criteria->addSelectColumn(QubitDeaccession::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitDeaccession::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitDeaccession', $options);
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
    $criteria->add(QubitDeaccession::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitDeaccession::DATABASE_NAME)->getTable(QubitDeaccession::TABLE_NAME);
  }

  public function __isset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    try
    {
      return call_user_func_array(array($this, 'QubitObject::__isset'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('deaccessionI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentdeaccessionI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentdeaccessionI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    try
    {
      return call_user_func_array(array($this, 'QubitObject::__get'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('deaccessionI18ns' == $name)
    {
      if (!isset($this->refFkValues['deaccessionI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['deaccessionI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['deaccessionI18ns'] = self::getdeaccessionI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['deaccessionI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentdeaccessionI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentdeaccessionI18n(array('sourceCulture' => true) + $options), '__get'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    call_user_func_array(array($this, 'QubitObject::__set'), $args);

    call_user_func_array(array($this->getCurrentdeaccessionI18n($options), '__set'), $args);

    return $this;
  }

  public function __unset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    call_user_func_array(array($this, 'QubitObject::__unset'), $args);

    call_user_func_array(array($this->getCurrentdeaccessionI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->deaccessionI18ns as $deaccessionI18n)
    {
      $deaccessionI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->deaccessionI18ns as $deaccessionI18n)
    {
      $deaccessionI18n->id = $this->id;

      $deaccessionI18n->save($connection);
    }

    return $this;
  }

  public static function addJoinaccessionCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDeaccession::ACCESSION_ID, QubitAccession::ID);

    return $criteria;
  }

  public static function addJoinscopeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDeaccession::SCOPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function adddeaccessionI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitDeaccessionI18n::ID, $id);

    return $criteria;
  }

  public static function getdeaccessionI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::adddeaccessionI18nsCriteriaById($criteria, $id);

    return QubitDeaccessionI18n::get($criteria, $options);
  }

  public function adddeaccessionI18nsCriteria(Criteria $criteria)
  {
    return self::adddeaccessionI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentdeaccessionI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $deaccessionI18ns = $this->deaccessionI18ns->indexBy('culture');
    if (!isset($deaccessionI18ns[$options['culture']]))
    {
      $deaccessionI18ns[$options['culture']] = new QubitDeaccessionI18n;
    }

    return $deaccessionI18ns[$options['culture']];
  }
}
