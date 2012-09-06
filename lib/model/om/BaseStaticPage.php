<?php

abstract class BaseStaticPage extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'static_page',

    ID = 'static_page.ID',
    SOURCE_CULTURE = 'static_page.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitStaticPage::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitStaticPage::ID);
    $criteria->addSelectColumn(QubitStaticPage::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitStaticPage::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitStaticPage', $options);
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
    $criteria->add(QubitStaticPage::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitStaticPage::DATABASE_NAME)->getTable(QubitStaticPage::TABLE_NAME);
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

    if ('staticPageI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentstaticPageI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentstaticPageI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('staticPageI18ns' == $name)
    {
      if (!isset($this->refFkValues['staticPageI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['staticPageI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['staticPageI18ns'] = self::getstaticPageI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['staticPageI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentstaticPageI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentstaticPageI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentstaticPageI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentstaticPageI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->staticPageI18ns as $staticPageI18n)
    {
      $staticPageI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->staticPageI18ns as $staticPageI18n)
    {
      $staticPageI18n->id = $this->id;

      $staticPageI18n->save($connection);
    }

    return $this;
  }

  public static function addstaticPageI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitStaticPageI18n::ID, $id);

    return $criteria;
  }

  public static function getstaticPageI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addstaticPageI18nsCriteriaById($criteria, $id);

    return QubitStaticPageI18n::get($criteria, $options);
  }

  public function addstaticPageI18nsCriteria(Criteria $criteria)
  {
    return self::addstaticPageI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentstaticPageI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $staticPageI18ns = $this->staticPageI18ns->indexBy('culture');
    if (!isset($staticPageI18ns[$options['culture']]))
    {
      $staticPageI18ns[$options['culture']] = new QubitStaticPageI18n;
    }

    return $staticPageI18ns[$options['culture']];
  }
}
