<?php

abstract class BaseTaxonomy extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'taxonomy',

    ID = 'taxonomy.ID',
    USAGE = 'taxonomy.USAGE',
    PARENT_ID = 'taxonomy.PARENT_ID',
    SOURCE_CULTURE = 'taxonomy.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitTaxonomy::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitTaxonomy::ID);
    $criteria->addSelectColumn(QubitTaxonomy::USAGE);
    $criteria->addSelectColumn(QubitTaxonomy::PARENT_ID);
    $criteria->addSelectColumn(QubitTaxonomy::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitTaxonomy::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitTaxonomy', $options);
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
    $criteria->add(QubitTaxonomy::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitTaxonomy::DATABASE_NAME)->getTable(QubitTaxonomy::TABLE_NAME);
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

    if ('taxonomysRelatedByparentId' == $name)
    {
      return true;
    }

    if ('taxonomyI18ns' == $name)
    {
      return true;
    }

    if ('terms' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrenttaxonomyI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrenttaxonomyI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
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

    if ('taxonomysRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['taxonomysRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['taxonomysRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['taxonomysRelatedByparentId'] = self::gettaxonomysRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['taxonomysRelatedByparentId'];
    }

    if ('taxonomyI18ns' == $name)
    {
      if (!isset($this->refFkValues['taxonomyI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['taxonomyI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['taxonomyI18ns'] = self::gettaxonomyI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['taxonomyI18ns'];
    }

    if ('terms' == $name)
    {
      if (!isset($this->refFkValues['terms']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['terms'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['terms'] = self::gettermsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['terms'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrenttaxonomyI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrenttaxonomyI18n(array('sourceCulture' => true) + $options), '__get'), $args);
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

    call_user_func_array(array($this->getCurrenttaxonomyI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrenttaxonomyI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->taxonomyI18ns as $taxonomyI18n)
    {
      $taxonomyI18n->clear();
    }

    return parent::clear();
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->taxonomyI18ns as $taxonomyI18n)
    {
      $taxonomyI18n->id = $this->id;

      $taxonomyI18n->save($connection);
    }

    return $this;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitTaxonomy::PARENT_ID, QubitTaxonomy::ID);

    return $criteria;
  }

  public static function addtaxonomysRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitTaxonomy::PARENT_ID, $id);

    return $criteria;
  }

  public static function gettaxonomysRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addtaxonomysRelatedByparentIdCriteriaById($criteria, $id);

    return QubitTaxonomy::get($criteria, $options);
  }

  public function addtaxonomysRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addtaxonomysRelatedByparentIdCriteriaById($criteria, $this->id);
  }

  public static function addtaxonomyI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitTaxonomyI18n::ID, $id);

    return $criteria;
  }

  public static function gettaxonomyI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addtaxonomyI18nsCriteriaById($criteria, $id);

    return QubitTaxonomyI18n::get($criteria, $options);
  }

  public function addtaxonomyI18nsCriteria(Criteria $criteria)
  {
    return self::addtaxonomyI18nsCriteriaById($criteria, $this->id);
  }

  public static function addtermsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitTerm::TAXONOMY_ID, $id);

    return $criteria;
  }

  public static function gettermsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addtermsCriteriaById($criteria, $id);

    return QubitTerm::get($criteria, $options);
  }

  public function addtermsCriteria(Criteria $criteria)
  {
    return self::addtermsCriteriaById($criteria, $this->id);
  }

  public function getCurrenttaxonomyI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $taxonomyI18ns = $this->taxonomyI18ns->indexBy('culture');
    if (!isset($taxonomyI18ns[$options['culture']]))
    {
      $taxonomyI18ns[$options['culture']] = new QubitTaxonomyI18n;
    }

    return $taxonomyI18ns[$options['culture']];
  }

  public function getAncestorsAndSelfForAcl()
  {
    if (!isset($this->values['ancestorsAndSelfForAcl']))
    {
      $cte = "(
      	WITH RECURSIVE aas AS
      	(
      	  SELECT tb1.id, tb1.parent_id, 1 as lev
          FROM taxonomy tb1
          WHERE tb1.id=$this->id
      	  UNION ALL
      	  SELECT tb2.id, tb2.parent_id, aas.lev + 1
          FROM taxonomy tb2
          JOIN aas ON aas.parent_id=tb2.id
      	)
      	SELECT id, lev FROM aas
      )";

      $criteria = new Criteria;
      $criteria->addJoin(QubitTaxonomy::ID, 'cte.id', "RIGHT JOIN $cte");
      $criteria->addDescendingOrderByColumn('lev');

      $this->values['ancestorsAndSelfForAcl'] = self::get($criteria);
    }

    return $this->values['ancestorsAndSelfForAcl'];
  }
}
