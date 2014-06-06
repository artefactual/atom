<?php

abstract class BaseSavedQuery extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'saved_query',

    ID = 'saved_query.ID',
    TYPE_ID = 'saved_query.TYPE_ID',
    SCOPE = 'saved_query.SCOPE',
    NAME = 'saved_query.NAME',
    DESCRIPTION = 'saved_query.DESCRIPTION',
    USER_ID = 'saved_query.USER_ID',
    PARAMS = 'saved_query.PARAMS',
    CREATED_AT = 'saved_query.CREATED_AT',
    UPDATED_AT = 'saved_query.UPDATED_AT';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitSavedQuery::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitSavedQuery::ID);
    $criteria->addSelectColumn(QubitSavedQuery::TYPE_ID);
    $criteria->addSelectColumn(QubitSavedQuery::SCOPE);
    $criteria->addSelectColumn(QubitSavedQuery::NAME);
    $criteria->addSelectColumn(QubitSavedQuery::DESCRIPTION);
    $criteria->addSelectColumn(QubitSavedQuery::USER_ID);
    $criteria->addSelectColumn(QubitSavedQuery::PARAMS);
    $criteria->addSelectColumn(QubitSavedQuery::CREATED_AT);
    $criteria->addSelectColumn(QubitSavedQuery::UPDATED_AT);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitSavedQuery::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitSavedQuery', $options);
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
    $criteria->add(QubitSavedQuery::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitSavedQuery::DATABASE_NAME)->getTable(QubitSavedQuery::TABLE_NAME);
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitSavedQuery::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinuserCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitSavedQuery::USER_ID, QubitUser::ID);

    return $criteria;
  }
}
