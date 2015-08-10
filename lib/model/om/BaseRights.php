<?php

abstract class BaseRights extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'rights',

    ID = 'rights.ID',
    START_DATE = 'rights.START_DATE',
    END_DATE = 'rights.END_DATE',
    BASIS_ID = 'rights.BASIS_ID',
    RIGHTS_HOLDER_ID = 'rights.RIGHTS_HOLDER_ID',
    COPYRIGHT_STATUS_ID = 'rights.COPYRIGHT_STATUS_ID',
    COPYRIGHT_STATUS_DATE = 'rights.COPYRIGHT_STATUS_DATE',
    COPYRIGHT_JURISDICTION = 'rights.COPYRIGHT_JURISDICTION',
    STATUTE_DETERMINATION_DATE = 'rights.STATUTE_DETERMINATION_DATE',
    STATUTE_CITATION_ID = 'rights.STATUTE_CITATION_ID',
    SOURCE_CULTURE = 'rights.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitRights::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitRights::ID);
    $criteria->addSelectColumn(QubitRights::START_DATE);
    $criteria->addSelectColumn(QubitRights::END_DATE);
    $criteria->addSelectColumn(QubitRights::BASIS_ID);
    $criteria->addSelectColumn(QubitRights::RIGHTS_HOLDER_ID);
    $criteria->addSelectColumn(QubitRights::COPYRIGHT_STATUS_ID);
    $criteria->addSelectColumn(QubitRights::COPYRIGHT_STATUS_DATE);
    $criteria->addSelectColumn(QubitRights::COPYRIGHT_JURISDICTION);
    $criteria->addSelectColumn(QubitRights::STATUTE_DETERMINATION_DATE);
    $criteria->addSelectColumn(QubitRights::STATUTE_CITATION_ID);
    $criteria->addSelectColumn(QubitRights::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitRights::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitRights', $options);
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
    $criteria->add(QubitRights::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitRights::DATABASE_NAME)->getTable(QubitRights::TABLE_NAME);
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

    if ('grantedRights' == $name)
    {
      return true;
    }

    if ('rightsI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentrightsI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrightsI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('grantedRights' == $name)
    {
      if (!isset($this->refFkValues['grantedRights']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['grantedRights'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['grantedRights'] = self::getgrantedRightsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['grantedRights'];
    }

    if ('rightsI18ns' == $name)
    {
      if (!isset($this->refFkValues['rightsI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['rightsI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['rightsI18ns'] = self::getrightsI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['rightsI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentrightsI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrightsI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentrightsI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentrightsI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->rightsI18ns as $rightsI18n)
    {
      $rightsI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->rightsI18ns as $rightsI18n)
    {
      $rightsI18n->id = $this->id;

      $rightsI18n->save($connection);
    }

    return $this;
  }

  public static function addJoinbasisCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRights::BASIS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinrightsHolderCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRights::RIGHTS_HOLDER_ID, QubitActor::ID);

    return $criteria;
  }

  public static function addJoincopyrightStatusCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRights::COPYRIGHT_STATUS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinstatuteCitationCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRights::STATUTE_CITATION_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addgrantedRightsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitGrantedRight::RIGHTS_ID, $id);

    return $criteria;
  }

  public static function getgrantedRightsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addgrantedRightsCriteriaById($criteria, $id);

    return QubitGrantedRight::get($criteria, $options);
  }

  public function addgrantedRightsCriteria(Criteria $criteria)
  {
    return self::addgrantedRightsCriteriaById($criteria, $this->id);
  }

  public static function addrightsI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRightsI18n::ID, $id);

    return $criteria;
  }

  public static function getrightsI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrightsI18nsCriteriaById($criteria, $id);

    return QubitRightsI18n::get($criteria, $options);
  }

  public function addrightsI18nsCriteria(Criteria $criteria)
  {
    return self::addrightsI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentrightsI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $rightsI18ns = $this->rightsI18ns->indexBy('culture');
    if (!isset($rightsI18ns[$options['culture']]))
    {
      $rightsI18ns[$options['culture']] = new QubitRightsI18n;
    }

    return $rightsI18ns[$options['culture']];
  }
}
