<?php

abstract class BaseFixityRecovery extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'fixity_recovery',

    ID = 'fixity_recovery.ID',
    SUCCESS = 'fixity_recovery.SUCCESS',
    MESSAGE = 'fixity_recovery.MESSAGE',
    AIP_ID = 'fixity_recovery.AIP_ID',
    TIME_STARTED = 'fixity_recovery.TIME_STARTED',
    TIME_COMPLETED = 'fixity_recovery.TIME_COMPLETED',
    USER_ID = 'fixity_recovery.USER_ID',
    STORAGE_SERVICE_EVENT_ID = 'fixity_recovery.STORAGE_SERVICE_EVENT_ID';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitFixityRecovery::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitFixityRecovery::ID);
    $criteria->addSelectColumn(QubitFixityRecovery::SUCCESS);
    $criteria->addSelectColumn(QubitFixityRecovery::MESSAGE);
    $criteria->addSelectColumn(QubitFixityRecovery::AIP_ID);
    $criteria->addSelectColumn(QubitFixityRecovery::TIME_STARTED);
    $criteria->addSelectColumn(QubitFixityRecovery::TIME_COMPLETED);
    $criteria->addSelectColumn(QubitFixityRecovery::USER_ID);
    $criteria->addSelectColumn(QubitFixityRecovery::STORAGE_SERVICE_EVENT_ID);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitFixityRecovery::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitFixityRecovery', $options);
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
    $criteria->add(QubitFixityRecovery::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitFixityRecovery::DATABASE_NAME)->getTable(QubitFixityRecovery::TABLE_NAME);
  }

  public static function addJoinaipCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFixityRecovery::AIP_ID, QubitAip::ID);

    return $criteria;
  }

  public static function addJoinuserCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFixityRecovery::USER_ID, QubitUser::ID);

    return $criteria;
  }
}
