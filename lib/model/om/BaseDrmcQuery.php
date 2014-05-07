<?php

abstract class BaseDrmcQuery extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'drmc_query',

    ID = 'drmc_query.ID',
    TYPE = 'drmc_query.TYPE',
    NAME = 'drmc_query.NAME',
    DESCRIPTION = 'drmc_query.DESCRIPTION',
    QUERY = 'drmc_query.QUERY',
    USER_ID = 'drmc_query.USER_ID',
    CREATED_AT = 'drmc_query.CREATED_AT',
    UPDATED_AT = 'drmc_query.UPDATED_AT';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitDrmcQuery::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitDrmcQuery::ID);
    $criteria->addSelectColumn(QubitDrmcQuery::TYPE);
    $criteria->addSelectColumn(QubitDrmcQuery::NAME);
    $criteria->addSelectColumn(QubitDrmcQuery::DESCRIPTION);
    $criteria->addSelectColumn(QubitDrmcQuery::QUERY);
    $criteria->addSelectColumn(QubitDrmcQuery::USER_ID);
    $criteria->addSelectColumn(QubitDrmcQuery::CREATED_AT);
    $criteria->addSelectColumn(QubitDrmcQuery::UPDATED_AT);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitDrmcQuery::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitDrmcQuery', $options);
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
    $criteria->add(QubitDrmcQuery::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitDrmcQuery::DATABASE_NAME)->getTable(QubitDrmcQuery::TABLE_NAME);
  }

  public static function addJoinuserCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDrmcQuery::USER_ID, QubitUser::ID);

    return $criteria;
  }
}
