<?php

abstract class BaseFixityReport extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'fixity_report',

    ID = 'fixity_report.ID',
    SUCCESS = 'fixity_report.SUCCESS',
    MESSAGE = 'fixity_report.MESSAGE',
    FAILURES = 'fixity_report.FAILURES',
    AIP_ID = 'fixity_report.AIP_ID',
    UUID = 'fixity_report.UUID',
    TIME_STARTED = 'fixity_report.TIME_STARTED',
    TIME_COMPLETED = 'fixity_report.TIME_COMPLETED';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitFixityReport::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitFixityReport::ID);
    $criteria->addSelectColumn(QubitFixityReport::SUCCESS);
    $criteria->addSelectColumn(QubitFixityReport::MESSAGE);
    $criteria->addSelectColumn(QubitFixityReport::FAILURES);
    $criteria->addSelectColumn(QubitFixityReport::AIP_ID);
    $criteria->addSelectColumn(QubitFixityReport::UUID);
    $criteria->addSelectColumn(QubitFixityReport::TIME_STARTED);
    $criteria->addSelectColumn(QubitFixityReport::TIME_COMPLETED);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitFixityReport::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitFixityReport', $options);
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
    $criteria->add(QubitFixityReport::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitFixityReport::DATABASE_NAME)->getTable(QubitFixityReport::TABLE_NAME);
  }

  public static function addJoinaipCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFixityReport::AIP_ID, QubitAip::ID);

    return $criteria;
  }
}
