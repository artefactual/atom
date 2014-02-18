<?php

abstract class BaseAip extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'aip',

    ID = 'aip.ID',
    TYPE_ID = 'aip.TYPE_ID',
    UUID = 'aip.UUID',
    FILENAME = 'aip.FILENAME',
    SIZE_ON_DISK = 'aip.SIZE_ON_DISK',
    DIGITAL_OBJECT_COUNT = 'aip.DIGITAL_OBJECT_COUNT',
    CREATED_AT = 'aip.CREATED_AT',
    PART_OF = 'aip.PART_OF';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitAip::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitAip::ID);
    $criteria->addSelectColumn(QubitAip::TYPE_ID);
    $criteria->addSelectColumn(QubitAip::UUID);
    $criteria->addSelectColumn(QubitAip::FILENAME);
    $criteria->addSelectColumn(QubitAip::SIZE_ON_DISK);
    $criteria->addSelectColumn(QubitAip::DIGITAL_OBJECT_COUNT);
    $criteria->addSelectColumn(QubitAip::CREATED_AT);
    $criteria->addSelectColumn(QubitAip::PART_OF);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitAip::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitAip', $options);
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
    $criteria->add(QubitAip::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitAip::DATABASE_NAME)->getTable(QubitAip::TABLE_NAME);
  }

  public static function addJointypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAip::TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinobjectRelatedBypartOfCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitAip::PART_OF, QubitObject::ID);

    return $criteria;
  }
}
