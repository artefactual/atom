<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Represent relations between data objects as a subject-action-object
 * triplet.
 *
 * @package    AccesstoMemory
 * @subpackage relation
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitRelation extends BaseRelation
{
  // Flags for updating search index on save or delete
  public
    $indexOnSave = true,
    $indexSubjectOnDelete = true,
    $indexObjectOnDelete = true;

  /**
   * Additional save functionality (e.g. update search index)
   *
   * @param mixed $connection a database connection object
   * @return QubitInformationObject self-reference
   */
  public function save($connection = null)
  {
    // TODO $cleanObject = $this->object->clean;
    $cleanObjectId = $this->__get('objectId', array('clean' => true));

    // TODO $cleanSubject = $this->subject->clean;
    $cleanSubjectId = $this->__get('subjectId', array('clean' => true));

    parent::save($connection);

    if ($this->indexOnSave)
    {
      if ($this->objectId != $cleanObjectId && null !== QubitInformationObject::getById($cleanObjectId))
      {
        QubitSearch::getInstance()->update(QubitInformationObject::getById($cleanObjectId));
      }

      if ($this->subjectId != $cleanSubjectId && null != QubitInformationObject::getById($cleanSubjectId))
      {
        QubitSearch::getInstance()->update(QubitInformationObject::getById($cleanSubjectId));
      }

      if ($this->object instanceof QubitInformationObject)
      {
        QubitSearch::getInstance()->update($this->object);
      }

      if ($this->subject instanceof QubitInformationObject)
      {
        QubitSearch::getInstance()->update($this->subject);
      }
    }

    return $this;
  }

  protected function insert($connection = null)
  {
    $this->slug = QubitSlug::slugify($this->slug);

    return parent::insert($connection);
  }

  public function delete($connection = null)
  {
    parent::delete($connection);

    if ($this->indexObjectOnDelete && $this->object instanceof QubitInformationObject)
    {
      QubitSearch::getInstance()->update($this->object);
    }

    if ($this->indexSubjectOnDelete && $this->subject instanceof QubitInformationObject)
    {
      QubitSearch::getInstance()->update($this->subject);
    }
  }

  /**
   * Get records from relation table linked to object (semantic)
   * QubitObject identified by primary key $id.
   *
   * @param integer $id primary key of "object" QubitObject
   * @param array   $options optional parameters
   * @return QubitQuery collection of QubitRelation objects
   */
  public static function getRelationsByObjectId($id, $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $id);

    if (isset($options['typeId']))
    {
      $criteria->add(QubitRelation::TYPE_ID, $options['typeId']);
    }

    return QubitRelation::get($criteria, $options);
  }

  /**
   * Get records from relation table linked to subject
   * QubitObject identified by primary key $id.
   *
   * @param integer $id primary key of "subject" QubitObject
   * @param array   $options optional parameters
   * @return QubitQuery collection of QubitRelation objects
   */
  public static function getRelationsBySubjectId($id, $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::SUBJECT_ID, $id);

    if (isset($options['typeId']))
    {
      $criteria->add(QubitRelation::TYPE_ID, $options['typeId']);
    }

    return QubitRelation::get($criteria, $options);
  }

  /**
   * Get all relations from/to given object $id
   *
   * @param integer $id primary key of object
   * @param array   $options optional parameters
   * @return QubitQuery collection of QubitRelation objects
   */
  public static function getBySubjectOrObjectId($id, $options = array())
  {
    $criteria = new Criteria;

    $criterion1 = $criteria->getNewCriterion(QubitRelation::OBJECT_ID, $id);
    $criterion2 = $criteria->getNewCriterion(QubitRelation::SUBJECT_ID, $id);
    $criterion1->addOr($criterion2);

    // If restricting by relation type
    if (isset($options['typeId']))
    {
      $criterion3 = $criteria->getNewCriterion(QubitRelation::TYPE_ID, $options['typeId']);
      $criterion1->addAnd($criterion3);
    }

    $criteria->add($criterion1);
    $criteria->addAscendingOrderByColumn(QubitRelation::TYPE_ID);

    return QubitRelation::get($criteria);
  }

  /**
   * Get related subject objects via QubitRelation many-to-many relationship
   *
   * @param string  $className type of objects to return
   * @param integer $objectId primary key of "object" QubitObject
   * @param array   $options list of options to pass to QubitQuery
   * @return QubitQuery collection of QubitObjects
   */
  public static function getRelatedSubjectsByObjectId($className, $objectId, $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $objectId);
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitObject::ID);
    $criteria->add(QubitObject::CLASS_NAME, $className);

    if (isset($options['typeId']))
    {
      $criteria->add(QubitRelation::TYPE_ID, $options['typeId']);
    }

    return call_user_func(array($className, 'get'), $criteria, $options);
  }

  /**
   * Get related "object" (semantic) QubitObjects
   *
   * @param string  $className type of objects to return
   * @param integer $subjectId primary key of "subject" QubitObject
   * @param array   $options list of options to pass to QubitQuery
   * @return QubitQuery collection of QubitObjects
   */
  public static function getRelatedObjectsBySubjectId($className, $subjectId, $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::SUBJECT_ID, $subjectId);
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitObject::ID);
    $criteria->add(QubitObject::CLASS_NAME, $className);

    if (isset($options['typeId']))
    {
      $criteria->add(QubitRelation::TYPE_ID, $options['typeId']);
    }

    return call_user_func(array($className, 'get'), $criteria, $options);
  }

  /**
   * Get opposite vertex of relation
   *
   * @param integer $referenceId primary key of reference object
   * @return mixed other object in relationship
   */
  public function getOpposedObject($reference)
  {
    if (is_object($reference))
    {
      $refid = $reference->id;
    }
    else
    {
      $refid = $reference;
    }

    $opposite = null;
    if ($this->subjectId == $refid)
    {
      $opposite = $this->getObject();
    }
    else if ($this->objectId == $refid)
    {
      $opposite = $this->getSubject();
    }

    return $opposite;
  }
}
