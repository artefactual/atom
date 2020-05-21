<?php

abstract class BasePhysicalObject extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'physical_object',

    ID = 'physical_object.ID',
    TYPE_ID = 'physical_object.TYPE_ID',
    SOURCE_CULTURE = 'physical_object.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitPhysicalObject::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitPhysicalObject::ID);
    $criteria->addSelectColumn(QubitPhysicalObject::TYPE_ID);
    $criteria->addSelectColumn(QubitPhysicalObject::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitPhysicalObject::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitPhysicalObject', $options);
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
    $criteria->add(QubitPhysicalObject::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitPhysicalObject::DATABASE_NAME)->getTable(QubitPhysicalObject::TABLE_NAME);
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

    if ('physicalObjectI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentphysicalObjectI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentphysicalObjectI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('physicalObjectI18ns' == $name)
    {
      if (!isset($this->refFkValues['physicalObjectI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['physicalObjectI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['physicalObjectI18ns'] = self::getphysicalObjectI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['physicalObjectI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentphysicalObjectI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentphysicalObjectI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentphysicalObjectI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentphysicalObjectI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->physicalObjectI18ns as $physicalObjectI18n)
    {
      $physicalObjectI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->physicalObjectI18ns as $physicalObjectI18n)
    {
      $physicalObjectI18n->id = $this->id;

      $physicalObjectI18n->save($connection);
    }

    return $this;
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitPhysicalObject::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addphysicalObjectI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitPhysicalObjectI18n::ID, $id);

    return $criteria;
  }

  public static function getphysicalObjectI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addphysicalObjectI18nsCriteriaById($criteria, $id);

    return QubitPhysicalObjectI18n::get($criteria, $options);
  }

  public function addphysicalObjectI18nsCriteria(Criteria $criteria)
  {
    return self::addphysicalObjectI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentphysicalObjectI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $physicalObjectI18ns = $this->physicalObjectI18ns->indexBy('culture');
    if (!isset($physicalObjectI18ns[$options['culture']]))
    {
      $physicalObjectI18ns[$options['culture']] = new QubitPhysicalObjectI18n;
    }

    return $physicalObjectI18ns[$options['culture']];
  }
}
