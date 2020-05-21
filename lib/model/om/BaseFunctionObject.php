<?php

abstract class BaseFunctionObject extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'function_object',

    ID = 'function_object.ID',
    TYPE_ID = 'function_object.TYPE_ID',
    DESCRIPTION_STATUS_ID = 'function_object.DESCRIPTION_STATUS_ID',
    DESCRIPTION_DETAIL_ID = 'function_object.DESCRIPTION_DETAIL_ID',
    DESCRIPTION_IDENTIFIER = 'function_object.DESCRIPTION_IDENTIFIER',
    SOURCE_STANDARD = 'function_object.SOURCE_STANDARD',
    SOURCE_CULTURE = 'function_object.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitFunctionObject::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitFunctionObject::ID);
    $criteria->addSelectColumn(QubitFunctionObject::TYPE_ID);
    $criteria->addSelectColumn(QubitFunctionObject::DESCRIPTION_STATUS_ID);
    $criteria->addSelectColumn(QubitFunctionObject::DESCRIPTION_DETAIL_ID);
    $criteria->addSelectColumn(QubitFunctionObject::DESCRIPTION_IDENTIFIER);
    $criteria->addSelectColumn(QubitFunctionObject::SOURCE_STANDARD);
    $criteria->addSelectColumn(QubitFunctionObject::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitFunctionObject::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitFunctionObject', $options);
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
    $criteria->add(QubitFunctionObject::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitFunctionObject::DATABASE_NAME)->getTable(QubitFunctionObject::TABLE_NAME);
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

    if ('functionObjectI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentfunctionObjectI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentfunctionObjectI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('functionObjectI18ns' == $name)
    {
      if (!isset($this->refFkValues['functionObjectI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['functionObjectI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['functionObjectI18ns'] = self::getfunctionObjectI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['functionObjectI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentfunctionObjectI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentfunctionObjectI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentfunctionObjectI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentfunctionObjectI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->functionObjectI18ns as $functionObjectI18n)
    {
      $functionObjectI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->functionObjectI18ns as $functionObjectI18n)
    {
      $functionObjectI18n->id = $this->id;

      $functionObjectI18n->save($connection);
    }

    return $this;
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunctionObject::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoindescriptionStatusCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunctionObject::DESCRIPTION_STATUS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoindescriptionDetailCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunctionObject::DESCRIPTION_DETAIL_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addfunctionObjectI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFunctionObjectI18n::ID, $id);

    return $criteria;
  }

  public static function getfunctionObjectI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfunctionObjectI18nsCriteriaById($criteria, $id);

    return QubitFunctionObjectI18n::get($criteria, $options);
  }

  public function addfunctionObjectI18nsCriteria(Criteria $criteria)
  {
    return self::addfunctionObjectI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentfunctionObjectI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $functionObjectI18ns = $this->functionObjectI18ns->indexBy('culture');
    if (!isset($functionObjectI18ns[$options['culture']]))
    {
      $functionObjectI18ns[$options['culture']] = new QubitFunctionObjectI18n;
    }

    return $functionObjectI18ns[$options['culture']];
  }
}
