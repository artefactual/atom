<?php

abstract class BaseDigitalObject extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'digital_object',

    ID = 'digital_object.ID',
    INFORMATION_OBJECT_ID = 'digital_object.INFORMATION_OBJECT_ID',
    USAGE_ID = 'digital_object.USAGE_ID',
    MIME_TYPE = 'digital_object.MIME_TYPE',
    MEDIA_TYPE_ID = 'digital_object.MEDIA_TYPE_ID',
    NAME = 'digital_object.NAME',
    PATH = 'digital_object.PATH',
    SEQUENCE = 'digital_object.SEQUENCE',
    BYTE_SIZE = 'digital_object.BYTE_SIZE',
    CHECKSUM = 'digital_object.CHECKSUM',
    CHECKSUM_TYPE = 'digital_object.CHECKSUM_TYPE',
    PARENT_ID = 'digital_object.PARENT_ID';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitDigitalObject::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitDigitalObject::ID);
    $criteria->addSelectColumn(QubitDigitalObject::INFORMATION_OBJECT_ID);
    $criteria->addSelectColumn(QubitDigitalObject::USAGE_ID);
    $criteria->addSelectColumn(QubitDigitalObject::MIME_TYPE);
    $criteria->addSelectColumn(QubitDigitalObject::MEDIA_TYPE_ID);
    $criteria->addSelectColumn(QubitDigitalObject::NAME);
    $criteria->addSelectColumn(QubitDigitalObject::PATH);
    $criteria->addSelectColumn(QubitDigitalObject::SEQUENCE);
    $criteria->addSelectColumn(QubitDigitalObject::BYTE_SIZE);
    $criteria->addSelectColumn(QubitDigitalObject::CHECKSUM);
    $criteria->addSelectColumn(QubitDigitalObject::CHECKSUM_TYPE);
    $criteria->addSelectColumn(QubitDigitalObject::PARENT_ID);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitDigitalObject::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitDigitalObject', $options);
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
    $criteria->add(QubitDigitalObject::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitDigitalObject::DATABASE_NAME)->getTable(QubitDigitalObject::TABLE_NAME);
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

    if ('digitalObjectsRelatedByparentId' == $name)
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

    if ('digitalObjectsRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['digitalObjectsRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['digitalObjectsRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['digitalObjectsRelatedByparentId'] = self::getdigitalObjectsRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['digitalObjectsRelatedByparentId'];
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public static function addJoininformationObjectCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDigitalObject::INFORMATION_OBJECT_ID, QubitInformationObject::ID);

    return $criteria;
  }

  public static function addJoinusageCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDigitalObject::USAGE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinmediaTypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDigitalObject::MEDIA_TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitDigitalObject::PARENT_ID, QubitDigitalObject::ID);

    return $criteria;
  }

  public static function adddigitalObjectsRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitDigitalObject::PARENT_ID, $id);

    return $criteria;
  }

  public static function getdigitalObjectsRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::adddigitalObjectsRelatedByparentIdCriteriaById($criteria, $id);

    return QubitDigitalObject::get($criteria, $options);
  }

  public function adddigitalObjectsRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::adddigitalObjectsRelatedByparentIdCriteriaById($criteria, $this->id);
  }
}
