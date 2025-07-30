<?php

/*
**** Module: Request To Publish Object component *****
**** Date  : 14-12-2024   ******
**** Email : Johan Pieterse <johan@plainsailingisystems.co.za>  *****
*/

abstract class BaseRequestToPublish extends QubitObject implements ArrayAccess
{
  const
    DATABASE_NAME = 'propel',

    TABLE_NAME = 'request_to_publish',

    ID = 'request_to_publish.ID',
    PARENT_ID = 'request_to_publish.PARENT_ID',
    LFT = 'request_to_publish.LFT',
    RGT = 'request_to_publish.RGT',
    SOURCE_CULTURE = 'request_to_publish.SOURCE_CULTURE';

  public static function addSelectColumns(Criteria $criteria)
  {
    parent::addSelectColumns($criteria);

    $criteria->addJoin(QubitRequestToPublish::ID, QubitObject::ID);

    $criteria->addSelectColumn(QubitRequestToPublish::ID);
    $criteria->addSelectColumn(QubitRequestToPublish::PARENT_ID);
    $criteria->addSelectColumn(QubitRequestToPublish::LFT);
    $criteria->addSelectColumn(QubitRequestToPublish::RGT);
    $criteria->addSelectColumn(QubitRequestToPublish::SOURCE_CULTURE);

    return $criteria;
  }

  public static function get(Criteria $criteria, array $options = array())
  {
    if (!isset($options['connection']))
    {
      $options['connection'] = Propel::getConnection(QubitRequestToPublish::DATABASE_NAME);
    }

    self::addSelectColumns($criteria);

    return QubitQuery::createFromCriteria($criteria, 'QubitRequestToPublish', $options);
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
    $criteria->add(QubitRequestToPublish::ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }

  public static function addOrderByPreorder(Criteria $criteria, $order = Criteria::ASC)
  {
    if ($order == Criteria::DESC)
    {
      return $criteria->addDescendingOrderByColumn(QubitRequestToPublish::LFT);
    }

    return $criteria->addAscendingOrderByColumn(QubitRequestToPublish::LFT);
  }

  public static function addRootsCriteria(Criteria $criteria)
  {
    $criteria->add(QubitRequestToPublish::PARENT_ID);

    return $criteria;
  }

  public function __construct()
  {
    parent::__construct();

    $this->tables[] = Propel::getDatabaseMap(QubitRequestToPublish::DATABASE_NAME)->getTable(QubitRequestToPublish::TABLE_NAME);
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

    if ('requesttopublishRelatedByparentId' == $name)
    {
      return true;
    }

    if ('requesttopublishI18ns' == $name)
    {
      return true;
    }

    try
    {
      if (!$value = call_user_func_array(array($this->getCurrentrequesttopublishI18n($options), '__isset'), $args) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrequesttopublishI18n(array('sourceCulture' => true) + $options), '__isset'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    if ('ancestors' == $name)
    {
      return true;
    }

    if ('descendants' == $name)
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

    if ('requesttopublishRelatedByparentId' == $name)
    {
      if (!isset($this->refFkValues['requesttopublishRelatedByparentId']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['requesttopublishRelatedByparentId'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['requesttopublishRelatedByparentId'] = self::getrequesttopublishRelatedByparentIdById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['requesttopublishRelatedByparentId'];
    }

    if ('requesttopublishI18ns' == $name)
    {
      if (!isset($this->refFkValues['requesttopublishI18ns']))
      {
        if (!isset($this->id))
        {
          $this->refFkValues['requesttopublishI18ns'] = QubitQuery::create();
        }
        else
        {
          $this->refFkValues['requesttopublishI18ns'] = self::getrequesttopublishI18nsById($this->id, array('self' => $this) + $options);
        }
      }

      return $this->refFkValues['requesttopublishI18ns'];
    }

    try
    {
      if (1 > strlen($value = call_user_func_array(array($this->getCurrentrequesttopublishI18n($options), '__get'), $args)) && !empty($options['cultureFallback']))
      {
        return call_user_func_array(array($this->getCurrentrequesttopublishI18n(array('sourceCulture' => true) + $options), '__get'), $args);
      }

      return $value;
    }
    catch (sfException $e)
    {
    }

    if ('ancestors' == $name)
    {
      if (!isset($this->values['ancestors']))
      {
        if ($this->new)
        {
          $this->values['ancestors'] = QubitQuery::create(array('self' => $this) + $options);
        }
        else
        {
          $criteria = new Criteria;
          $this->addAncestorsCriteria($criteria);
          $this->addOrderByPreorder($criteria);
          $this->values['ancestors'] = self::get($criteria, array('self' => $this) + $options);
        }
      }

      return $this->values['ancestors'];
    }

    if ('descendants' == $name)
    {
      if (!isset($this->values['descendants']))
      {
        if ($this->new)
        {
          $this->values['descendants'] = QubitQuery::create(array('self' => $this) + $options);
        }
        else
        {
          $criteria = new Criteria;
          $this->addDescendantsCriteria($criteria);
          $this->addOrderByPreorder($criteria);
          $this->values['descendants'] = self::get($criteria, array('self' => $this) + $options);
        }
      }

      return $this->values['descendants'];
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

    call_user_func_array(array($this->getCurrentrequesttopublishI18n($options), '__set'), $args);

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

    call_user_func_array(array($this->getCurrentrequesttopublishI18n($options), '__unset'), $args);

    return $this;
  }

  public function clear()
  {
    foreach ($this->requesttopublishI18ns as $requesttopublishI18n)
    {
      $requesttopublishI18n->clear();
    }

    return parent::clear(); 
  }

  public function save($connection = null)
  {
    parent::save($connection);

    foreach ($this->requesttopublishI18ns as $requesttopublishI18n)
    {
      $requesttopublishI18n->id = $this->id;

      $requesttopublishI18n->save($connection);
    }

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
    $this->updateNestedSet($connection);

    parent::insert($connection);

    return $this;
  }

  protected function update($connection = null)
  {
    // Update nested set keys only if parent id has changed
    if (isset($this->values['parentId']))
    {
      // Get the "original" parentId before any updates
      $offset = 0;
      $originalParentId = null;
      foreach ($this->tables as $table)
      {
        foreach ($table->getColumns() as $column)
        {
          if ('parentId' == $column->getPhpName())
          {
            $originalParentId = $this->row[$offset];
            break;
          }
          $offset++;
        }
      }

      // If updated value of parentId is different then original value,
      // update the nested set
      if ($originalParentId != $this->values['parentId'])
      {
        $this->updateNestedSet($connection);
      }
    }

    parent::update($connection);

    return $this;
  }

  public function delete($connection = null)
  {
    if ($this->deleted)
    {
      throw new PropelException('This object has already been deleted.');
    }

    $this->clear();
    $this->deleteFromNestedSet($connection);

    parent::delete($connection);

    return $this;
  }

  public static function addJoinparentCriteria(Criteria $criteria)
  {
    $criteria->addJoin(QubitRequestToPublish::PARENT_ID, QubitRequestToPublish::ID);

    return $criteria;
  }

  public static function addRequestToPublishRelatedByparentIdCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRequestToPublish::PARENT_ID, $id);

    return $criteria;
  }

  public static function getrequesttopublishRelatedByparentIdById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addRequestToPublishRelatedByparentIdCriteriaById($criteria, $id);

    return QubitRequestToPublish::get($criteria, $options);
  }

  public function addrequesttopublishRelatedByparentIdCriteria(Criteria $criteria)
  {
    return self::addRequestToPublishRelatedByparentIdCriteriaById($criteria, $this->id);
  }

  public static function addrequesttopublishI18nsCriteriaById(Criteria $criteria, $id)
  {
    $criteria->add(QubitRequestToPublishI18n::ID, $id);

    return $criteria;
  }

  public static function getrequesttopublishI18nsById($id, array $options = array())
  {
    $criteria = new Criteria;
    self::addrequesttopublishI18nsCriteriaById($criteria, $id);

    return QubitRequestToPublishI18n::get($criteria, $options);
  }

  public function addrequesttopublishI18nsCriteria(Criteria $criteria)
  {
    return self::addrequesttopublishI18nsCriteriaById($criteria, $this->id);
  }

  public function getCurrentrequesttopublishI18n(array $options = array())
  {
    if (!empty($options['sourceCulture']))
    {
      $options['culture'] = $this->sourceCulture;
    }

    if (!isset($options['culture']))
    {
      $options['culture'] = sfPropel::getDefaultCulture();
    }

    $requesttopublishI18ns = $this->requesttopublishI18ns->indexBy('culture');
    if (!isset($requesttopublishI18ns[$options['culture']]))
    {
      $requesttopublishI18ns[$options['culture']] = new QubitRequestToPublishI18n;
    }

    return $requesttopublishI18ns[$options['culture']];
  }

  public function hasChildren()
  {
    return ($this->rgt - $this->lft) > 1;
  }

  public function addAncestorsCriteria(Criteria $criteria)
  {
    return $criteria->add(QubitRequestToPublish::LFT, $this->lft, Criteria::LESS_THAN)->add(QubitRequestToPublish::RGT, $this->rgt, Criteria::GREATER_THAN);
  }

  public function addDescendantsCriteria(Criteria $criteria)
  {
    return $criteria->add(QubitRequestToPublish::LFT, $this->lft, Criteria::GREATER_THAN)->add(QubitRequestToPublish::RGT, $this->rgt, Criteria::LESS_THAN);
  }

  protected function updateNestedSet($connection = null)
  {
// HACK Try to prevent modifying left and right values anywhere except in this
// method.  Perhaps it would be more logical to use protected visibility for
// these values?
unset($this->values['lft']);
unset($this->values['rgt']);
    if (!isset($connection))
    {
      //$connection = QubitTransactionFilter::getConnection(QubitPresevationObjectI18n::DATABASE_NAME);
		$connection = Propel::getConnection();
    }

    if (!isset($this->lft) || !isset($this->rgt))
    {
      $delta = 2;
    }
    else
    {
      $delta = $this->rgt - $this->lft + 1;
    }

    if (null === $parent = $this->__get('parent', array('connection' => $connection)))
    {
      $statement = $connection->prepare('
        SELECT MAX('.QubitRequestToPublish::RGT.')
        FROM '.QubitRequestToPublish::TABLE_NAME);
      $statement->execute();
      $row = $statement->fetch();
      $max = $row[0];

      if (!isset($this->lft) || !isset($this->rgt))
      {
        $this->lft = $max + 1;
        $this->rgt = $max + 2;

        return $this;
      }

      $shift = $max + 1 - $this->lft;
    }
    else
    {
      $parent->clear();

      if (isset($this->lft) && isset($this->rgt) && $this->lft <= $parent->lft && $this->rgt >= $parent->rgt)
      {
        throw new PropelException('An object cannot be a descendant of itself.');
      }

      $statement = $connection->prepare('
        UPDATE '.QubitRequestToPublish::TABLE_NAME.'
        SET '.QubitRequestToPublish::LFT.' = '.QubitRequestToPublish::LFT.' + ?
        WHERE '.QubitRequestToPublish::LFT.' >= ?');
      $statement->execute(array($delta, $parent->rgt));

      $statement = $connection->prepare('
        UPDATE '.QubitRequestToPublish::TABLE_NAME.'
        SET '.QubitRequestToPublish::RGT.' = '.QubitRequestToPublish::RGT.' + ?
        WHERE '.QubitRequestToPublish::RGT.' >= ?');
      $statement->execute(array($delta, $parent->rgt));

      if (!isset($this->lft) || !isset($this->rgt))
      {
        $this->lft = $parent->rgt;
        $this->rgt = $parent->rgt + 1;
        $parent->rgt += 2;

        return $this;
      }

      if ($this->lft > $parent->rgt)
      {
        $this->lft += $delta;
        $this->rgt += $delta;
      }

      $shift = $parent->rgt - $this->lft;
    }

    $statement = $connection->prepare('
      UPDATE '.QubitRequestToPublish::TABLE_NAME.'
      SET '.QubitRequestToPublish::LFT.' = '.QubitRequestToPublish::LFT.' + ?, '.QubitRequestToPublish::RGT.' = '.QubitRequestToPublish::RGT.' + ?
      WHERE '.QubitRequestToPublish::LFT.' >= ?
      AND '.QubitRequestToPublish::RGT.' <= ?');
    $statement->execute(array($shift, $shift, $this->lft, $this->rgt));

    $this->deleteFromNestedSet($connection);

    if ($shift > 0)
    {
      $this->lft -= $delta;
      $this->rgt -= $delta;
    }

    $this->lft += $shift;
    $this->rgt += $shift;

    return $this;
  }

  protected function deleteFromNestedSet($connection = null)
  {
    if (!isset($connection))
    {
		$connection = Propel::getConnection();
    }

    $delta = $this->rgt - $this->lft + 1;

    $statement = $connection->prepare('
      UPDATE '.QubitRequestToPublish::TABLE_NAME.'
      SET '.QubitRequestToPublish::LFT.' = '.QubitRequestToPublish::LFT.' - ?
      WHERE '.QubitRequestToPublish::LFT.' >= ?');
    $statement->execute(array($delta, $this->rgt));

    $statement = $connection->prepare('
      UPDATE '.QubitRequestToPublish::TABLE_NAME.'
      SET '.QubitRequestToPublish::RGT.' = '.QubitRequestToPublish::RGT.' - ?
      WHERE '.QubitRequestToPublish::RGT.' >= ?');
    $statement->execute(array($delta, $this->rgt));

    return $this;
  }

  public function isInTree()
  {
    return $this->lft > 0 && $this->rgt > $this->lft;
  }

  public function isRoot()
  {
      return $this->isInTree() && $this->lft == 1;
  }

  public function isDescendantOf($parent)
  {
    return $this->isInTree() && $this->lft > $parent->lft && $this->rgt < $parent->rgt;
  }

  public function moveToFirstChildOf($parent, PropelPDO $con = null)
  {
    if ($parent->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($parent->lft + 1, $con);

    return $this;
  }

  public function moveToLastChildOf($parent, PropelPDO $con = null)
  {
    if ($parent->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as child of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($parent->rgt, $con);

    return $this;
  }

  public function moveToPrevSiblingOf($sibling, PropelPDO $con = null)
  {
    if (!$this->isInTree())
    {
      throw new PropelException('This object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
    }

    if ($sibling->isRoot())
    {
      throw new PropelException('Cannot move to previous sibling of a root node.');
    }

    if ($sibling->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($sibling->lft, $con);

    return $this;
  }

  public function moveToNextSiblingOf($sibling, PropelPDO $con = null)
  {
    if (!$this->isInTree())
    {
      throw new PropelException('This object must be already in the tree to be moved. Use the insertAsPrevSiblingOf() instead.');
    }

    if ($sibling->isRoot())
    {
      throw new PropelException('Cannot move to previous sibling of a root node.');
    }

    if ($sibling->isDescendantOf($this))
    {
      throw new PropelException('Cannot move a node as sibling of one of its subtree nodes.');
    }

    $this->moveSubtreeTo($sibling->rgt + 1, $con);

    return $this;
  }

  protected function moveSubtreeTo($destLeft, PropelPDO $con = null)
  {
    $left  = $this->lft;
    $right = $this->rgt;

    $treeSize = $right - $left +1;

    if ($con === null)
    {
      $con = Propel::getConnection();
    }

    $con->beginTransaction();

    try
    {
      // make room next to the target for the subtree
      self::shiftRLValues($treeSize, $destLeft, null, $con);

      if ($left >= $destLeft) // src was shifted too?
      {
        $left += $treeSize;
        $right += $treeSize;
      }

      // move the subtree to the target
      self::shiftRLValues($destLeft - $left, $left, $right, $con);

      // remove the empty room at the previous location of the subtree
      self::shiftRLValues(-$treeSize, $right + 1, null, $con);

      // update all loaded nodes
      // self::updateLoadedNodes(null, $con);

      $con->commit();
    }
    catch (PropelException $e)
    {
      $con->rollback();

      throw $e;
    }
  }

  /**
   * Adds $delta to all L and R values that are >= $first and <= $last.
   * '$delta' can also be negative.
   *
   * @param int $delta Value to be shifted by, can be negative
   * @param int $first First node to be shifted
   * @param int $last Last node to be shifted (optional)
   * @param PropelPDO $con Connection to use.
   */
  protected function shiftRLValues($delta, $first, $last = null, PropelPDO $con = null)
  {
    if ($con === null)
    {
      $con = Propel::getConnection();
    }

    // Shift left column values
    $whereCriteria = new Criteria;
    $criterion = $whereCriteria->getNewCriterion(QubitRequestToPublish::LFT, $first, Criteria::GREATER_EQUAL);
    if (null !== $last)
    {
      $criterion->addAnd($whereCriteria->getNewCriterion(QubitRequestToPublish::LFT, $last, Criteria::LESS_EQUAL));
    }
    $whereCriteria->add($criterion);

    $valuesCriteria = new Criteria;
    $valuesCriteria->add(QubitRequestToPublish::LFT, array('raw' => QubitRequestToPublish::LFT . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

    BasePeer::doUpdate($whereCriteria, $valuesCriteria, $con);

    // Shift right column values
    $whereCriteria = new Criteria;
    $criterion = $whereCriteria->getNewCriterion(QubitRequestToPublish::RGT, $first, Criteria::GREATER_EQUAL);
    if (null !== $last)
    {
      $criterion->addAnd($whereCriteria->getNewCriterion(QubitRequestToPublish::RGT, $last, Criteria::LESS_EQUAL));
    }
    $whereCriteria->add($criterion);

    $valuesCriteria = new Criteria;
    $valuesCriteria->add(QubitRequestToPublish::RGT, array('raw' => QubitRequestToPublish::RGT . ' + ?', 'value' => $delta), Criteria::CUSTOM_EQUAL);

    BasePeer::doUpdate($whereCriteria, $valuesCriteria, $con);
  }
  
  public function getAncestorsAndSelfForAcl()
  {
    if (!isset($this->values['ancestorsAndSelfForAcl']))
    {
      $cte = "(
      	WITH RECURSIVE aas AS
      	(
      	  SELECT tb1.id, tb1.parent_id, 1 as lev
          FROM requesttopublish tb1
          WHERE tb1.id=$this->id
      	  UNION ALL
      	  SELECT tb2.id, tb2.parent_id, aas.lev + 1
          FROM requesttopublish tb2
          JOIN aas ON aas.parent_id=tb2.id
      	)
      	SELECT id, lev FROM aas
      )";

      $criteria = new Criteria;
      $criteria->addJoin(QubitRequestToPublish::ID, 'cte.id', "RIGHT JOIN $cte");
      $criteria->addDescendingOrderByColumn('lev');

      $this->values['ancestorsAndSelfForAcl'] = self::get($criteria);
    }

    return $this->values['ancestorsAndSelfForAcl'];
  }
  
  
}
