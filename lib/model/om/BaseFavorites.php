<?php

/*
**** Module: Favorites Object component *****
**** Date  : 04-02-2025   ******
**** Email : Johan Pieterse <johan@plainsailingisystems.co.za>  *****
*/

abstract class BaseFavorites extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'favorites',

    ID = 'favorites.ID',
    ARCHIVAL_DESCRIPTION_ID = 'favorites.ARCHIVAL_DESCRIPTION_ID',
    ARCHIVAL_DESCRIPTION = 'favorites.ARCHIVAL_DESCRIPTION',
	SLUG = 'favorites.SLUG',
	USER_ID = 'favorites.USER_ID',
    COMPLETED_AT = 'favorites.COMPLETED_AT',
    CREATED_AT = 'favorites.CREATED_AT';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitFavorites::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitFavorites::ID);
    $criteria->addSelectColumn(QubitFavorites::ARCHIVAL_DESCRIPTION_ID);
	$criteria->addSelectColumn(QubitFavorites::ARCHIVAL_DESCRIPTION);
	$criteria->addSelectColumn(QubitFavorites::SLUG);
	$criteria->addSelectColumn(QubitFavorites::USER_ID);
    $criteria->addSelectColumn(QubitFavorites::CREATED_AT);
    $criteria->addSelectColumn(QubitFavorites::COMPLETED_AT);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitFavorites::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);
//echo $criteria->toString();

    return QubitQuery::createFromCriteria($criteria, 'QubitFavorites', $options);
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
    $criteria->add(QubitFavorites::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    } else {

	}
  }

  public static function getByUserIDandObjectId($id, $adId, array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitFavorites::USER_ID, $id);
    $criteria->add(QubitFavorites::ARCHIVAL_DESCRIPTION_ID, $adId);
	$query = self::getOne($criteria, $options);

	return $query;
  }

  public static function addOrderByPreorder(Criteria $criteria, $order = Criteria::ASC)
  {
    if ($order == Criteria::DESC)
    {
      return $criteria->addDescendingOrderByColumn(QubitFavorites::LFT);
    }

    return $criteria->addAscendingOrderByColumn(QubitFavorites::LFT);
  }

  public static function addRootsCriteria(Criteria $criteria)
  {
    $criteria->add(QubitFavorites::PARENT_ID);

    return $criteria;
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitFavorites::DATABASE_NAME)->getTable(QubitFavorites::TABLE_NAME);
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

    if ('favoritesRelatedByparentId' == $name)
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

    if ('favoritesRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['favoritesRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['favoritesRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['favoritesRelatedByparentId'] = self::getfavoritesRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['favoritesRelatedByparentId'];
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
    $criteria->addJoin(QubitFavorites::REQUESTOR_ID, QubitTerm::ID);

    return $criteria;
  }
  
  public static function addJoindispatcherCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFavorites::DISPATCHER_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitFavorites::PARENT_ID, QubitFavorites::ID);

    return $criteria;
  }

  public static function addfavoritesRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitFavorites::PARENT_ID, $id);

    return $criteria;
  }

  public static function getfavoritesRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addfavoritesRelatedByparentIdCriteriaById($criteria, $id);

    return QubitFavorites::get($criteria, $options);
  }

  public function addfavoritesRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addfavoritesRelatedByparentIdCriteriaById($criteria, $this->id);
  }

}
