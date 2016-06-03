<?php

abstract class BaseUser extends QubitActor implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'user',

    ID = 'user.ID',
    USERNAME = 'user.USERNAME',
    EMAIL = 'user.EMAIL',
    SHA1_PASSWORD = 'user.SHA1_PASSWORD',
    SALT = 'user.SALT',
    ACTIVE = 'user.ACTIVE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitUser::ID, QubitActor::ID);

    $criteria->addSelectColumn(QubitUser::ID);
    $criteria->addSelectColumn(QubitUser::USERNAME);
    $criteria->addSelectColumn(QubitUser::EMAIL);
    $criteria->addSelectColumn(QubitUser::SHA1_PASSWORD);
    $criteria->addSelectColumn(QubitUser::SALT);
    $criteria->addSelectColumn(QubitUser::ACTIVE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitUser::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitUser', $options);
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
    $criteria->add(QubitUser::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitUser::DATABASE_NAME)->getTable(QubitUser::TABLE_NAME);
  }

  public function __isset($name)
  {
    $args = func_get_args();

    try
    {
      return call_user_func_array(array($this, 'QubitActor::__isset'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('jobs' == $name)
    {
      return true;
    }

    if ('notes' == $name)
    {
      return true;
    }

    if ('aclPermissions' == $name)
    {
      return true;
    }

    if ('aclUserGroups' == $name)
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
      return call_user_func_array(array($this, 'QubitActor::__get'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('jobs' == $name)
    {
      if (!isset($this->refFkValues['jobs']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['jobs'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['jobs'] = self::getjobsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['jobs'];
    }

    if ('notes' == $name)
    {
      if (!isset($this->refFkValues['notes']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['notes'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['notes'] = self::getnotesById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['notes'];
    }

    if ('aclPermissions' == $name)
    {
      if (!isset($this->refFkValues['aclPermissions']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['aclPermissions'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['aclPermissions'] = self::getaclPermissionsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['aclPermissions'];
    }

    if ('aclUserGroups' == $name)
    {
      if (!isset($this->refFkValues['aclUserGroups']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['aclUserGroups'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['aclUserGroups'] = self::getaclUserGroupsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['aclUserGroups'];
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public static function addjobsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitJob::USER_ID, $id);

    return $criteria;
  }

  public static function getjobsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addjobsCriteriaById($criteria, $id);

    return QubitJob::get($criteria, $options);
  }

  public function addjobsCriteria(Criteria $criteria)
  {
    return self::addjobsCriteriaById($criteria, $this->id);
  }

  public static function addnotesCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitNote::USER_ID, $id);

    return $criteria;
  }

  public static function getnotesById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addnotesCriteriaById($criteria, $id);

    return QubitNote::get($criteria, $options);
  }

  public function addnotesCriteria(Criteria $criteria)
  {
    return self::addnotesCriteriaById($criteria, $this->id);
  }

  public static function addaclPermissionsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAclPermission::USER_ID, $id);

    return $criteria;
  }

  public static function getaclPermissionsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaclPermissionsCriteriaById($criteria, $id);

    return QubitAclPermission::get($criteria, $options);
  }

  public function addaclPermissionsCriteria(Criteria $criteria)
  {
    return self::addaclPermissionsCriteriaById($criteria, $this->id);
  }

  public static function addaclUserGroupsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitAclUserGroup::USER_ID, $id);

    return $criteria;
  }

  public static function getaclUserGroupsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addaclUserGroupsCriteriaById($criteria, $id);

    return QubitAclUserGroup::get($criteria, $options);
  }

  public function addaclUserGroupsCriteria(Criteria $criteria)
  {
    return self::addaclUserGroupsCriteriaById($criteria, $this->id);
  }
}
