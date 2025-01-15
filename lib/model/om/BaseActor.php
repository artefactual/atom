<?php

abstract class BaseActor extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'actor',

    ID = 'actor.ID',
    CORPORATE_BODY_IDENTIFIERS = 'actor.CORPORATE_BODY_IDENTIFIERS',
    ENTITY_TYPE_ID = 'actor.ENTITY_TYPE_ID',
    DESCRIPTION_STATUS_ID = 'actor.DESCRIPTION_STATUS_ID',
    DESCRIPTION_DETAIL_ID = 'actor.DESCRIPTION_DETAIL_ID',
    DESCRIPTION_IDENTIFIER = 'actor.DESCRIPTION_IDENTIFIER',
    SOURCE_STANDARD = 'actor.SOURCE_STANDARD',
    PARENT_ID = 'actor.PARENT_ID',
    SOURCE_CULTURE = 'actor.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitActor::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitActor::ID);
    $criteria->addSelectColumn(QubitActor::CORPORATE_BODY_IDENTIFIERS);
    $criteria->addSelectColumn(QubitActor::ENTITY_TYPE_ID);
    $criteria->addSelectColumn(QubitActor::DESCRIPTION_STATUS_ID);
    $criteria->addSelectColumn(QubitActor::DESCRIPTION_DETAIL_ID);
    $criteria->addSelectColumn(QubitActor::DESCRIPTION_IDENTIFIER);
    $criteria->addSelectColumn(QubitActor::SOURCE_STANDARD);
    $criteria->addSelectColumn(QubitActor::PARENT_ID);
    $criteria->addSelectColumn(QubitActor::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitActor::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitActor', $options);
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
    $criteria->add(QubitActor::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitActor::DATABASE_NAME)->getTable(QubitActor::TABLE_NAME);
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

    if ('actorsRelatedByparentId' == $name)
    {
      return true;
    }

    if ('actorI18ns' == $name)
    {
      return true;
    }

    if ('contactInformations' == $name)
    {
      return true;
    }

    if ('events' == $name)
    {
      return true;
    }

    if ('rightss' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentactorI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentactorI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
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

    if ('actorsRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['actorsRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['actorsRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['actorsRelatedByparentId'] = self::getactorsRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['actorsRelatedByparentId'];
    }

    if ('actorI18ns' == $name)
    {
      if (!isset($this->refFkValues['actorI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['actorI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['actorI18ns'] = self::getactorI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['actorI18ns'];
    }

    if ('contactInformations' == $name)
    {
      if (!isset($this->refFkValues['contactInformations']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['contactInformations'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['contactInformations'] = self::getcontactInformationsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['contactInformations'];
    }

    if ('events' == $name)
    {
      if (!isset($this->refFkValues['events']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['events'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['events'] = self::geteventsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['events'];
    }

    if ('rightss' == $name)
    {
      if (!isset($this->refFkValues['rightss']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['rightss'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['rightss'] = self::getrightssById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['rightss'];
    }

    try
    {
      if (1 > strlen((string) $value = call_user_func_array(array($this->getCurrentactorI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentactorI18n(array('sourceCulture' => true) + $options), '__get'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    throw new sfException("Unknown record property \"$name\" on \"".get_class($this).'"');
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    call_user_func_array(array($this, 'QubitObject::__set'), $args);

    call_user_func_array(array($this->getCurrentactorI18n($options), '__set'), $args);

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

    call_user_func_array(array($this, 'QubitObject::__unset'), $args);

    call_user_func_array(array($this->getCurrentactorI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->actorI18ns as $actorI18n)
    {
      $actorI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->actorI18ns as $actorI18n)
    {
      $actorI18n->id = $this->id;

      $actorI18n->save($connection);
    }

    return $this;
  }

  public static function addJoinentityTypeCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitActor::ENTITY_TYPE_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoindescriptionStatusCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitActor::DESCRIPTION_STATUS_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoindescriptionDetailCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitActor::DESCRIPTION_DETAIL_ID, QubitTerm::ID);

    return $criteria;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitActor::PARENT_ID, QubitActor::ID);

    return $criteria;
  }

  public static function addactorsRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitActor::PARENT_ID, $id);

    return $criteria;
  }

  public static function getactorsRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addactorsRelatedByparentIdCriteriaById($criteria, $id);

    return QubitActor::get($criteria, $options);
  }

  public function addactorsRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addactorsRelatedByparentIdCriteriaById($criteria, $this->id);
  }

  public static function addactorI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitActorI18n::ID, $id);

    return $criteria;
  }

  public static function getactorI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addactorI18nsCriteriaById($criteria, $id);

    return QubitActorI18n::get($criteria, $options);
  }

  public function addactorI18nsCriteria(Criteria $criteria)
  {
    return self::addactorI18nsCriteriaById($criteria, $this->id);
  }

  public static function addcontactInformationsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitContactInformation::ACTOR_ID, $id);

    return $criteria;
  }

  public static function getcontactInformationsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addcontactInformationsCriteriaById($criteria, $id);

    return QubitContactInformation::get($criteria, $options);
  }

  public function addcontactInformationsCriteria(Criteria $criteria)
  {
    return self::addcontactInformationsCriteriaById($criteria, $this->id);
  }

  public static function addeventsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitEvent::ACTOR_ID, $id);

    return $criteria;
  }

  public static function geteventsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addeventsCriteriaById($criteria, $id);

    return QubitEvent::get($criteria, $options);
  }

  public function addeventsCriteria(Criteria $criteria)
  {
    return self::addeventsCriteriaById($criteria, $this->id);
  }

  public static function addrightssCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRights::RIGHTS_HOLDER_ID, $id);

    return $criteria;
  }

  public static function getrightssById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrightssCriteriaById($criteria, $id);

    return QubitRights::get($criteria, $options);
  }

  public function addrightssCriteria(Criteria $criteria)
  {
    return self::addrightssCriteriaById($criteria, $this->id);
  }

  public function getCurrentactorI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $actorI18ns = $this->actorI18ns->indexBy('culture');
    if (!isset($actorI18ns[$options['culture']]))
    {
      $actorI18ns[$options['culture']] = new QubitActorI18n;
    }

    return $actorI18ns[$options['culture']];
  }

  public function getAncestorsAndSelfForAcl()
  {
    if (!isset($this->values['ancestorsAndSelfForAcl']))
    {
      $cte = "(
      	WITH RECURSIVE aas AS
      	(
      	  SELECT tb1.id, tb1.parent_id, 1 as lev
          FROM actor tb1
          WHERE tb1.id=$this->id
      	  UNION ALL
      	  SELECT tb2.id, tb2.parent_id, aas.lev + 1
          FROM actor tb2
          JOIN aas ON aas.parent_id=tb2.id
      	)
      	SELECT id, lev FROM aas
      )";

      $criteria = new Criteria;
      $criteria->addJoin(QubitActor::ID, 'cte.id', "RIGHT JOIN $cte");
      $criteria->addDescendingOrderByColumn('lev');

      $this->values['ancestorsAndSelfForAcl'] = self::get($criteria);
    }

    return $this->values['ancestorsAndSelfForAcl'];
  }
}
