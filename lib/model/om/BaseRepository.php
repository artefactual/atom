<?php

abstract class BaseRepository extends QubitActor implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'repository',

    ID = 'repository.ID',
    IDENTIFIER = 'repository.IDENTIFIER',
    DESC_STATUS_ID = 'repository.DESC_STATUS_ID',
    DESC_DETAIL_ID = 'repository.DESC_DETAIL_ID',
    DESC_IDENTIFIER = 'repository.DESC_IDENTIFIER',
    UPLOAD_LIMIT = 'repository.UPLOAD_LIMIT',
    SOURCE_CULTURE = 'repository.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitRepository::ID, QubitActor::ID);

    $criteria->addSelectColumn(QubitRepository::ID);
    $criteria->addSelectColumn(QubitRepository::IDENTIFIER);
    $criteria->addSelectColumn(QubitRepository::DESC_STATUS_ID);
    $criteria->addSelectColumn(QubitRepository::DESC_DETAIL_ID);
    $criteria->addSelectColumn(QubitRepository::DESC_IDENTIFIER);
    $criteria->addSelectColumn(QubitRepository::UPLOAD_LIMIT);
    $criteria->addSelectColumn(QubitRepository::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitRepository::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitRepository', $options);
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
    $criteria->add(QubitRepository::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitRepository::DATABASE_NAME)->getTable(QubitRepository::TABLE_NAME);
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
      return call_user_func_array('QubitActor::__isset', $args);
    }
    catch (sfException $e)
    {
    }

    if ('informationObjects' == $name)
    {
      return true;
    }

    if ('repositoryI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentrepositoryI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrepositoryI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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
      return call_user_func_array('QubitActor::__get', $args);
    }
    catch (sfException $e)
    {
    }

    if ('informationObjects' == $name)
    {
      if (!isset($this->refFkValues['informationObjects']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['informationObjects'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['informationObjects'] = self::getinformationObjectsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['informationObjects'];
    }

    if ('repositoryI18ns' == $name)
    {
      if (!isset($this->refFkValues['repositoryI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['repositoryI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['repositoryI18ns'] = self::getrepositoryI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['repositoryI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentrepositoryI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrepositoryI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array('QubitActor::__set', $args);

    call_user_func_array(array($this->getCurrentrepositoryI18n($options), '__set'), $args);

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

    call_user_func_array('QubitActor::__unset', $args);

    call_user_func_array(array($this->getCurrentrepositoryI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->repositoryI18ns as $repositoryI18n)
    {
      $repositoryI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->repositoryI18ns as $repositoryI18n)
    {
      $repositoryI18n->id = $this->id;

      $repositoryI18n->save($connection);
    }

    return $this;
  }

  public static function addJoindescStatusCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRepository::DESC_STATUS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoindescDetailCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRepository::DESC_DETAIL_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addinformationObjectsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitInformationObject::REPOSITORY_ID, $id);

    return $criteria;
  }

  public static function getinformationObjectsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addinformationObjectsCriteriaById($criteria, $id);

    return QubitInformationObject::get($criteria, $options);
  }

  public function addinformationObjectsCriteria(Criteria $criteria)
  {
    return self::addinformationObjectsCriteriaById($criteria, $this->id);
  }

  public static function addrepositoryI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRepositoryI18n::ID, $id);

    return $criteria;
  }

  public static function getrepositoryI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrepositoryI18nsCriteriaById($criteria, $id);

    return QubitRepositoryI18n::get($criteria, $options);
  }

  public function addrepositoryI18nsCriteria(Criteria $criteria)
  {
    return self::addrepositoryI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentrepositoryI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $repositoryI18ns = $this->repositoryI18ns->indexBy('culture');
    if (!isset($repositoryI18ns[$options['culture']]))
    {
      $repositoryI18ns[$options['culture']] = new QubitRepositoryI18n;
    }

    return $repositoryI18ns[$options['culture']];
  }
}
