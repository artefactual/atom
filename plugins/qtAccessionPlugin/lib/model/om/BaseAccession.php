<?php

abstract class BaseAccession extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'accession',

    ID = 'accession.ID',
    ACQUISITION_TYPE_ID = 'accession.ACQUISITION_TYPE_ID',
    DATE = 'accession.DATE',
    IDENTIFIER = 'accession.IDENTIFIER',
    PROCESSING_PRIORITY_ID = 'accession.PROCESSING_PRIORITY_ID',
    PROCESSING_STATUS_ID = 'accession.PROCESSING_STATUS_ID',
    RESOURCE_TYPE_ID = 'accession.RESOURCE_TYPE_ID',
    CREATED_AT = 'accession.CREATED_AT',
    UPDATED_AT = 'accession.UPDATED_AT',
    SOURCE_CULTURE = 'accession.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitAccession::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitAccession::ID);
    $criteria->addSelectColumn(QubitAccession::ACQUISITION_TYPE_ID);
    $criteria->addSelectColumn(QubitAccession::DATE);
    $criteria->addSelectColumn(QubitAccession::IDENTIFIER);
    $criteria->addSelectColumn(QubitAccession::PROCESSING_PRIORITY_ID);
    $criteria->addSelectColumn(QubitAccession::PROCESSING_STATUS_ID);
    $criteria->addSelectColumn(QubitAccession::RESOURCE_TYPE_ID);
    $criteria->addSelectColumn(QubitAccession::CREATED_AT);
    $criteria->addSelectColumn(QubitAccession::UPDATED_AT);
    $criteria->addSelectColumn(QubitAccession::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitAccession::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitAccession', $options);
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
    $criteria->add(QubitAccession::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitAccession::DATABASE_NAME)->getTable(QubitAccession::TABLE_NAME);
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

    if ('accessionI18ns' == $name)
    {
      return true;
    }

    if ('deaccessions' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentaccessionI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentaccessionI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('accessionI18ns' == $name)
    {
      if (!isset($this->refFkValues['accessionI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['accessionI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['accessionI18ns'] = self::getaccessionI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['accessionI18ns'];
    }

    if ('deaccessions' == $name)
    {
      if (!isset($this->refFkValues['deaccessions']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['deaccessions'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['deaccessions'] = self::getdeaccessionsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['deaccessions'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentaccessionI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentaccessionI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrentaccessionI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentaccessionI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->accessionI18ns as $accessionI18n)
    {
      $accessionI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->accessionI18ns as $accessionI18n)
    {
      $accessionI18n->id = $this->id;

      $accessionI18n->save($connection);
    }

    return $this;
  }

  public static function addJoinacquisitionTypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAccession::ACQUISITION_TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinprocessingPriorityCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAccession::PROCESSING_PRIORITY_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinprocessingStatusCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAccession::PROCESSING_STATUS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinresourceTypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAccession::RESOURCE_TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addaccessionI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAccessionI18n::ID, $id);

    return $criteria;
  }

  public static function getaccessionI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaccessionI18nsCriteriaById($criteria, $id);

    return QubitAccessionI18n::get($criteria, $options);
  }

  public function addaccessionI18nsCriteria(Criteria $criteria)
  {
    return self::addaccessionI18nsCriteriaById($criteria, $this->id);
  }

  public static function adddeaccessionsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitDeaccession::ACCESSION_ID, $id);

    return $criteria;
  }

  public static function getdeaccessionsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::adddeaccessionsCriteriaById($criteria, $id);

    return QubitDeaccession::get($criteria, $options);
  }

  public function adddeaccessionsCriteria(Criteria $criteria)
  {
    return self::adddeaccessionsCriteriaById($criteria, $this->id);
  }

  public function getCurrentaccessionI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $accessionI18ns = $this->accessionI18ns->indexBy('culture');
    if (!isset($accessionI18ns[$options['culture']]))
    {
      $accessionI18ns[$options['culture']] = new QubitAccessionI18n;
    }

    return $accessionI18ns[$options['culture']];
  }
}
