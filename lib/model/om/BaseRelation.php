<?php

abstract class BaseRelation extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'relation',

    ID = 'relation.ID',
    SUBJECT_ID = 'relation.SUBJECT_ID',
    OBJECT_ID = 'relation.OBJECT_ID',
    TYPE_ID = 'relation.TYPE_ID',
    START_DATE = 'relation.START_DATE',
    END_DATE = 'relation.END_DATE',
    SOURCE_CULTURE = 'relation.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitRelation::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitRelation::ID);
    $criteria->addSelectColumn(QubitRelation::SUBJECT_ID);
    $criteria->addSelectColumn(QubitRelation::OBJECT_ID);
    $criteria->addSelectColumn(QubitRelation::TYPE_ID);
    $criteria->addSelectColumn(QubitRelation::START_DATE);
    $criteria->addSelectColumn(QubitRelation::END_DATE);
    $criteria->addSelectColumn(QubitRelation::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitRelation::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitRelation', $options);
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
    $criteria->add(QubitRelation::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitRelation::DATABASE_NAME)->getTable(QubitRelation::TABLE_NAME);
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

    if ('relationI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentrelationI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrelationI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('relationI18ns' == $name)
    {
      if (!isset($this->refFkValues['relationI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['relationI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['relationI18ns'] = self::getrelationI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['relationI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentrelationI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrelationI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentrelationI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentrelationI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->relationI18ns as $relationI18n)
    {
      $relationI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->relationI18ns as $relationI18n)
    {
      $relationI18n->id = $this->id;

      $relationI18n->save($connection);
    }

    return $this;
  }

  public static function addJoinsubjectCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitObject::ID);

    return $criteria;
  }

  public static function addJoinobjectCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitObject::ID);

    return $criteria;
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRelation::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addrelationI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRelationI18n::ID, $id);

    return $criteria;
  }

  public static function getrelationI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrelationI18nsCriteriaById($criteria, $id);

    return QubitRelationI18n::get($criteria, $options);
  }

  public function addrelationI18nsCriteria(Criteria $criteria)
  {
    return self::addrelationI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentrelationI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $relationI18ns = $this->relationI18ns->indexBy('culture');
    if (!isset($relationI18ns[$options['culture']]))
    {
      $relationI18ns[$options['culture']] = new QubitRelationI18n;
    }

    return $relationI18ns[$options['culture']];
  }
}
