<?php

abstract class BasePremisObject extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'premis_object',

    ID = 'premis_object.ID',
    INFORMATION_OBJECT_ID = 'premis_object.INFORMATION_OBJECT_ID',
    PUID = 'premis_object.PUID',
    FILENAME = 'premis_object.FILENAME',
    LAST_MODIFIED = 'premis_object.LAST_MODIFIED',
    DATE_INGESTED = 'premis_object.DATE_INGESTED',
    SIZE = 'premis_object.SIZE',
    MIME_TYPE = 'premis_object.MIME_TYPE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitPremisObject::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitPremisObject::ID);
    $criteria->addSelectColumn(QubitPremisObject::INFORMATION_OBJECT_ID);
    $criteria->addSelectColumn(QubitPremisObject::PUID);
    $criteria->addSelectColumn(QubitPremisObject::FILENAME);
    $criteria->addSelectColumn(QubitPremisObject::LAST_MODIFIED);
    $criteria->addSelectColumn(QubitPremisObject::DATE_INGESTED);
    $criteria->addSelectColumn(QubitPremisObject::SIZE);
    $criteria->addSelectColumn(QubitPremisObject::MIME_TYPE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitPremisObject::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitPremisObject', $options);
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
    $criteria->add(QubitPremisObject::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitPremisObject::DATABASE_NAME)->getTable(QubitPremisObject::TABLE_NAME);
  }

  public static function addJoininformationObjectCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitPremisObject::INFORMATION_OBJECT_ID, QubitInformationObject::ID);

    return $criteria;
  }
}
