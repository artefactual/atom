<?php

abstract class BaseEvent extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'event',

    ID = 'event.ID',
    START_DATE = 'event.START_DATE',
    START_TIME = 'event.START_TIME',
    END_DATE = 'event.END_DATE',
    END_TIME = 'event.END_TIME',
    TYPE_ID = 'event.TYPE_ID',
    OBJECT_ID = 'event.OBJECT_ID',
    ACTOR_ID = 'event.ACTOR_ID',
    SOURCE_CULTURE = 'event.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitEvent::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitEvent::ID);
    $criteria->addSelectColumn(QubitEvent::START_DATE);
    $criteria->addSelectColumn(QubitEvent::START_TIME);
    $criteria->addSelectColumn(QubitEvent::END_DATE);
    $criteria->addSelectColumn(QubitEvent::END_TIME);
    $criteria->addSelectColumn(QubitEvent::TYPE_ID);
    $criteria->addSelectColumn(QubitEvent::OBJECT_ID);
    $criteria->addSelectColumn(QubitEvent::ACTOR_ID);
    $criteria->addSelectColumn(QubitEvent::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitEvent::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitEvent', $options);
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
    $criteria->add(QubitEvent::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitEvent::DATABASE_NAME)->getTable(QubitEvent::TABLE_NAME);
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

    if ('eventI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrenteventI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrenteventI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('eventI18ns' == $name)
    {
      if (!isset($this->refFkValues['eventI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['eventI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['eventI18ns'] = self::geteventI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['eventI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrenteventI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrenteventI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrenteventI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrenteventI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->eventI18ns as $eventI18n)
    {
      $eventI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->eventI18ns as $eventI18n)
    {
      $eventI18n->id = $this->id;

      $eventI18n->save($connection);
    }

    return $this;
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitEvent::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinobjectCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitEvent::OBJECT_ID, QubitObject::ID);

    return $criteria;
  }

  public static function addJoinactorCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitEvent::ACTOR_ID, QubitActor::ID);

    return $criteria;
  }

  public static function addeventI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitEventI18n::ID, $id);

    return $criteria;
  }

  public static function geteventI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addeventI18nsCriteriaById($criteria, $id);

    return QubitEventI18n::get($criteria, $options);
  }

  public function addeventI18nsCriteria(Criteria $criteria)
  {
    return self::addeventI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrenteventI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $eventI18ns = $this->eventI18ns->indexBy('culture');
    if (!isset($eventI18ns[$options['culture']]))
    {
      $eventI18ns[$options['culture']] = new QubitEventI18n;
    }

    return $eventI18ns[$options['culture']];
  }
}
