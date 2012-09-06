<?php

abstract class BaseObjectTermRelation extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'object_term_relation',

    ID = 'object_term_relation.ID',
    OBJECT_ID = 'object_term_relation.OBJECT_ID',
    TERM_ID = 'object_term_relation.TERM_ID',
    START_DATE = 'object_term_relation.START_DATE',
    END_DATE = 'object_term_relation.END_DATE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitObjectTermRelation::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitObjectTermRelation::ID);
    $criteria->addSelectColumn(QubitObjectTermRelation::OBJECT_ID);
    $criteria->addSelectColumn(QubitObjectTermRelation::TERM_ID);
    $criteria->addSelectColumn(QubitObjectTermRelation::START_DATE);
    $criteria->addSelectColumn(QubitObjectTermRelation::END_DATE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitObjectTermRelation::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitObjectTermRelation', $options);
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
    $criteria->add(QubitObjectTermRelation::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitObjectTermRelation::DATABASE_NAME)->getTable(QubitObjectTermRelation::TABLE_NAME);
  }

  public static function addJoinobjectCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitObjectTermRelation::OBJECT_ID, QubitObject::ID);

    return $criteria;
  }

  public static function addJointermCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);

    return $criteria;
  }
}
