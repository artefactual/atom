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
    SESSION_UUID = 'fixity_report.SESSION_UUID',
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
    $criteria->addSelectColumn(QubitFixityReport::SESSION_UUID);
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

  public function __isset($name)
  {
    $args = func_get_args();

    try
    {
      return call_user_func_array(array($this, 'QubitObject::__isset'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('fixityRecoverys' == $name)
    {
      return true;
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

    if ('fixityRecoverys' == $name)
    {
      if (!isset($this->refFkValues['fixityRecoverys']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['fixityRecoverys'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['fixityRecoverys'] = self::getfixityRecoverysById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['fixityRecoverys'];
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public static function addJoinaipCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFixityReport::AIP_ID, QubitAip::ID);

    return $criteria;
  }

  public static function addfixityRecoverysCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFixityRecovery::FIXITY_REPORT_ID, $id);

    return $criteria;
  }

  public static function getfixityRecoverysById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfixityRecoverysCriteriaById($criteria, $id);

    return QubitFixityRecovery::get($criteria, $options);
  }

  public function addfixityRecoverysCriteria(Criteria $criteria)
  {
    return self::addfixityRecoverysCriteriaById($criteria, $this->id);
  }
}
