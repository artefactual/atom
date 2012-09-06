<?php

abstract class BaseFunction extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'function',

    ID = 'function.ID',
    TYPE_ID = 'function.TYPE_ID',
    PARENT_ID = 'function.PARENT_ID',
    DESCRIPTION_STATUS_ID = 'function.DESCRIPTION_STATUS_ID',
    DESCRIPTION_DETAIL_ID = 'function.DESCRIPTION_DETAIL_ID',
    DESCRIPTION_IDENTIFIER = 'function.DESCRIPTION_IDENTIFIER',
    SOURCE_STANDARD = 'function.SOURCE_STANDARD',
    LFT = 'function.LFT',
    RGT = 'function.RGT',
    SOURCE_CULTURE = 'function.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitFunction::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitFunction::ID);
    $criteria->addSelectColumn(QubitFunction::TYPE_ID);
    $criteria->addSelectColumn(QubitFunction::PARENT_ID);
    $criteria->addSelectColumn(QubitFunction::DESCRIPTION_STATUS_ID);
    $criteria->addSelectColumn(QubitFunction::DESCRIPTION_DETAIL_ID);
    $criteria->addSelectColumn(QubitFunction::DESCRIPTION_IDENTIFIER);
    $criteria->addSelectColumn(QubitFunction::SOURCE_STANDARD);
    $criteria->addSelectColumn(QubitFunction::LFT);
    $criteria->addSelectColumn(QubitFunction::RGT);
    $criteria->addSelectColumn(QubitFunction::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitFunction::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitFunction', $options);
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
    $criteria->add(QubitFunction::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitFunction::DATABASE_NAME)->getTable(QubitFunction::TABLE_NAME);
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

    if ('functionsRelatedByparentId' == $name)
    {
      return true;
    }

    if ('functionI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentfunctionI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentfunctionI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('functionsRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['functionsRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['functionsRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['functionsRelatedByparentId'] = self::getfunctionsRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['functionsRelatedByparentId'];
    }

    if ('functionI18ns' == $name)
    {
      if (!isset($this->refFkValues['functionI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['functionI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['functionI18ns'] = self::getfunctionI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['functionI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentfunctionI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentfunctionI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentfunctionI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentfunctionI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->functionI18ns as $functionI18n)
    {
      $functionI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->functionI18ns as $functionI18n)
    {
      $functionI18n->id = $this->id;

      $functionI18n->save($connection);
    }

    return $this;
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunction::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunction::PARENT_ID, QubitFunction::ID);

    return $criteria;
  }

  public static function addJoindescriptionStatusCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunction::DESCRIPTION_STATUS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoindescriptionDetailCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFunction::DESCRIPTION_DETAIL_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addfunctionsRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFunction::PARENT_ID, $id);

    return $criteria;
  }

  public static function getfunctionsRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfunctionsRelatedByparentIdCriteriaById($criteria, $id);

    return QubitFunction::get($criteria, $options);
  }

  public function addfunctionsRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addfunctionsRelatedByparentIdCriteriaById($criteria, $this->id);
  }

  public static function addfunctionI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFunctionI18n::ID, $id);

    return $criteria;
  }

  public static function getfunctionI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfunctionI18nsCriteriaById($criteria, $id);

    return QubitFunctionI18n::get($criteria, $options);
  }

  public function addfunctionI18nsCriteria(Criteria $criteria)
  {
    return self::addfunctionI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentfunctionI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $functionI18ns = $this->functionI18ns->indexBy('culture');
    if (!isset($functionI18ns[$options['culture']]))
    {
      $functionI18ns[$options['culture']] = new QubitFunctionI18n;
    }

    return $functionI18ns[$options['culture']];
  }
}
