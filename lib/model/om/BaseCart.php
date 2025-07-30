<?php

/*
**** Module: Cart Object component *****
**** Date  : 04-02-2025   ******
**** Email : Johan Pieterse <johan@plainsailingisystems.co.za>  *****
*/

abstract class BaseCart extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'cart',

    ID = 'cart.ID',
    ARCHIVAL_DESCRIPTION_ID = 'cart.ARCHIVAL_DESCRIPTION_ID',
    ARCHIVAL_DESCRIPTION = 'cart.ARCHIVAL_DESCRIPTION',
	SLUG = 'cart.SLUG',
	USER_ID = 'cart.USER_ID',
    COMPLETED_AT = 'cart.COMPLETED_AT',
    CREATED_AT = 'cart.CREATED_AT';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitCart::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitCart::ID);
	$criteria->addSelectColumn(QubitCart::ARCHIVAL_DESCRIPTION);
    $criteria->addSelectColumn(QubitCart::ARCHIVAL_DESCRIPTION_ID);
	$criteria->addSelectColumn(QubitCart::SLUG);
	$criteria->addSelectColumn(QubitCart::USER_ID);
    $criteria->addSelectColumn(QubitCart::CREATED_AT);
    $criteria->addSelectColumn(QubitCart::COMPLETED_AT);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitCart::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);
//echo $criteria->toString();

    return QubitQuery::createFromCriteria($criteria, 'QubitCart', $options);
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
    $criteria->add(QubitCart::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    } else {

	}
  }

  public static function getByUserIDandObjectId($id, $adId, array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitCart::USER_ID, $id);
    $criteria->add(QubitCart::ARCHIVAL_DESCRIPTION_ID, $adId);
	$query = self::getOne($criteria, $options);
//echo $criteria->toString();
	return $query;
  }

  public static function addOrderByPreorder(Criteria $criteria, $order = Criteria::ASC)
  {
    if ($order == Criteria::DESC)
    {
      return $criteria->addDescendingOrderByColumn(QubitCart::LFT);
    }

    return $criteria->addAscendingOrderByColumn(QubitCart::LFT);
  }

  public static function addRootsCriteria(Criteria $criteria)
  {
    $criteria->add(QubitCart::PARENT_ID);

    return $criteria;
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitCart::DATABASE_NAME)->getTable(QubitCart::TABLE_NAME);
  }

  public function __isset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    try
    {
      return call_user_func_array(array($this, 'QubitObject::__isset'), $args);
    }
    catch (sfException $e)
    {
    }

    if ('cartRelatedByparentId' == $name)
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

    if ('cartRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['cartRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['cartRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['cartRelatedByparentId'] = self::getcartRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['cartRelatedByparentId'];
    }

   // throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    return $this;
  }

  public function __unset($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    return $this;
  }

  public function clear()
  {
    return parent::clear(); 
  }

  public function save($connection = null)
  {
    parent::save($connection);

    return $this;
  }

  protected function param($column)
  {
    $value = $this->values[$column->getPhpName()];

    // Convert to DateTime or SQL zero special case
    if (isset($value) && $column->isTemporal() && !$value instanceof DateTime)
    {
      // Year only: one or more digits.  Convert to SQL zero special case
      if (preg_match('/^\d+$/', $value))
      {
        $value .= '-0-0';
      }

      // Year and month only: one or more digits, plus separator, plus
      // one or more digits.  Convert to SQL zero special case
      else if (preg_match('/^\d+[-\/]\d+$/', $value))
      {
        $value .= '-0';
      }

      // Convert to DateTime if not SQL zero special case: year plus
      // separator plus zero to twelve (possibly zero padded) plus
      // separator plus one or more zeros
      if (!preg_match('/^\d+[-\/]0*(?:1[0-2]|\d)[-\/]0+$/', $value))
      {
        try
        {
          $value = new DateTime($value);
        }
        catch (Exception $e)
        {
          return null;
        }
      }
    }

    return $value;
  }

  protected function insert($connection = null)
  {

    parent::insert($connection);

    return $this;
  }

  public function delete($connection = null)
  {
    if ($this->deleted)
    {
      throw new PropelException('This object has already been deleted.');
    }

    $this->clear();

    parent::delete($connection);

    return $this;
  }

  public static function addJoinrequestorCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitCart::REQUESTOR_ID, QubitTerm::ID);

    return $criteria;
  }
  
  public static function addJoindispatcherCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitCart::DISPATCHER_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitCart::PARENT_ID, QubitCart::ID);

    return $criteria;
  }

  public static function addcartRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitCart::PARENT_ID, $id);

    return $criteria;
  }

  public static function getcartRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addcartRelatedByparentIdCriteriaById($criteria, $id);

    return QubitCart::get($criteria, $options);
  }

  public function addcartRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addcartRelatedByparentIdCriteriaById($criteria, $this->id);
  }

}
