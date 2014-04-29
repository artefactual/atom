<?php

abstract class BaseJob extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'job',

    ID = 'job.ID',
    NAME = 'job.NAME',
    STATUS_ID = 'job.STATUS_ID',
    COMPLETED_AT = 'job.COMPLETED_AT',
    USER_ID = 'job.USER_ID';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitJob::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitJob::ID);
    $criteria->addSelectColumn(QubitJob::NAME);
    $criteria->addSelectColumn(QubitJob::STATUS_ID);
    $criteria->addSelectColumn(QubitJob::COMPLETED_AT);
    $criteria->addSelectColumn(QubitJob::USER_ID);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitJob::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitJob', $options);
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
    $criteria->add(QubitJob::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitJob::DATABASE_NAME)->getTable(QubitJob::TABLE_NAME);
  }

  public static function addJoinuserCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitJob::USER_ID, QubitUser::ID);

    return $criteria;
  }
}
