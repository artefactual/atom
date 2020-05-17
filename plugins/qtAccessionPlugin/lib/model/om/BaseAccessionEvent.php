<?php

abstract class BaseAccessionEvent extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'accession_event',

    ID = 'accession_event.ID',
    TYPE_ID = 'accession_event.TYPE_ID',
    ACCESSION_ID = 'accession_event.ACCESSION_ID',
    DATE = 'accession_event.DATE',
    SOURCE_CULTURE = 'accession_event.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitAccessionEvent::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitAccessionEvent::ID);
    $criteria->addSelectColumn(QubitAccessionEvent::TYPE_ID);
    $criteria->addSelectColumn(QubitAccessionEvent::ACCESSION_ID);
    $criteria->addSelectColumn(QubitAccessionEvent::DATE);
    $criteria->addSelectColumn(QubitAccessionEvent::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitAccessionEvent::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitAccessionEvent', $options);
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
    $criteria->add(QubitAccessionEvent::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitAccessionEvent::DATABASE_NAME)->getTable(QubitAccessionEvent::TABLE_NAME);
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

    if ('accessionEventI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentaccessionEventI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentaccessionEventI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('accessionEventI18ns' == $name)
    {
      if (!isset($this->refFkValues['accessionEventI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessionEventI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessionEventI18ns'] = self::getaccessionEventI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessionEventI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentaccessionEventI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentaccessionEventI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentaccessionEventI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentaccessionEventI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->accessionEventI18ns as $accessionEventI18n)
    {
      $accessionEventI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->accessionEventI18ns as $accessionEventI18n)
    {
      $accessionEventI18n->id = $this->id;

      $accessionEventI18n->save($connection);
    }

    return $this;
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAccessionEvent::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinaccessionCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAccessionEvent::ACCESSION_ID, QubitAccession::ID);

    return $criteria;
  }

  public static function addaccessionEventI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccessionEventI18n::ID, $id);

    return $criteria;
  }

  public static function getaccessionEventI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessionEventI18nsCriteriaById($criteria, $id);

    return QubitAccessionEventI18n::get($criteria, $options);
  }

  public function addaccessionEventI18nsCriteria(Criteria $criteria)
  {
    return self::addaccessionEventI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentaccessionEventI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $accessionEventI18ns = $this->accessionEventI18ns->indexBy('culture');
    if (!isset($accessionEventI18ns[$options['culture']]))
    {
      $accessionEventI18ns[$options['culture']] = new QubitAccessionEventI18n;
    }

    return $accessionEventI18ns[$options['culture']];
  }
}
