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
 * Extended methods for Information object model
 *
 * @package AccesstoMemory
 * @subpackage model
 * @author Jack Bates <jack@nottheoilrig.com>
 * @author Peter Van Garderen <peter@artefactual.com>
 * @author David Juhasz <david@artefactual.com>
 * @author Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class QubitInformationObject extends BaseInformationObject
{
  const
    ROOT_ID = 1;

  // allow per-object disabling of nested set updating during bulk imports
  public $disableNestedSetUpdating = false;

  /**
   * When cast as a string, return i18n-ized object title with fallback to
   * source culture
   *
   * @return string title value with fallback to source culture
   */
  public function __toString()
  {
    $string = $this->title;
    if (!isset($string))
    {
      $string = $this->getTitle(array('sourceCulture' => true));
    }

    return (string) $string;
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    switch ($name)
    {
      case 'objectUUID':
      case 'aipUUID':

        if (!isset($this->values[$name]))
        {
          $criteria = new Criteria;
          $this->addPropertysCriteria($criteria);
          $criteria->add(QubitProperty::NAME, $name);

          if (1 == count($query = QubitProperty::get($criteria)))
          {
            $this->values[$name] = $query[0];
          }
        }

        if (isset($this->values[$name]))
        {
          return $this->values[$name];
        }

        break;

      case 'language':
      case 'languageOfDescription':
      case 'script':
      case 'scriptOfDescription':

        if (!isset($this->values[$name]))
        {
          $criteria = new Criteria;
          $this->addPropertysCriteria($criteria);
          $criteria->add(QubitProperty::NAME, $name);

          if (1 == count($query = QubitProperty::get($criteria)))
          {
            $this->values[$name] = $query[0];
          }
        }

        if (isset($this->values[$name]) && null !== $value = unserialize($this->values[$name]->__get('value', $options + array('sourceCulture' => true))))
        {
          return $value;
        }

        return array();

      case 'referenceCode':

        if (sfConfig::get('app_inherit_code_informationobject'))
        {
          if (!isset($this->identifier))
          {
            return;
          }

          $identifier = array();
          $repository = null;
          foreach ($this->ancestors->andSelf()->orderBy('lft') as $item)
          {
            if (isset($item->identifier))
            {
              $identifier[] = $item->identifier;
            }

            if (isset($item->repository))
            {
              $repository = $item->repository;
            }
          }
          $identifier = implode(sfConfig::get('app_separator_character', '-'), $identifier);

          if (isset($repository->identifier))
          {
            $identifier = "$repository->identifier $identifier";
          }

          if (isset($repository))
          {
            $countryCode = $repository->getCountryCode();
            if (isset($countryCode))
            {
              $identifier = "$countryCode $identifier";
            }
          }

          return $identifier;
        }

        return $this->identifier;

      // It may happen that the sourceStandard column is undefined, in that case
      // look up for the value in higher levels
      case 'sourceStandard':

        foreach ($this->ancestors->andSelf()->orderBy('rgt') as $item)
        {
          if (isset($item->sourceStandard))
          {
            return call_user_func_array(array($item, 'QubitObject::__get'), $args);
          }

          // Stop iteration before the root object is reached
          if (QubitInformationObject::ROOT_ID == $item->parentId)
          {
            break;
          }
        }

        break;

      default:

        return call_user_func_array(array($this, 'BaseInformationObject::__get'), $args);
    }
  }

  public function __set($name, $value)
  {
    $args = func_get_args();

    $options = array();
    if (2 < count($args))
    {
      $options = $args[2];
    }

    switch ($name)
    {
      case 'language':
      case 'languageOfDescription':
      case 'script':
      case 'scriptOfDescription':

        if (!isset($this->values[$name]))
        {
          $criteria = new Criteria;
          $this->addPropertysCriteria($criteria);
          $criteria->add(QubitProperty::NAME, $name);

          if (1 == count($query = QubitProperty::get($criteria)))
          {
            $this->values[$name] = $query[0];
          }
          else
          {
            $this->values[$name] = new QubitProperty;
            $this->values[$name]->name = $name;
            $this->propertys[] = $this->values[$name];
          }
        }

        $this->values[$name]->__set('value', serialize($value), $options + array('sourceCulture' => true));

        return $this;

      default:

        return call_user_func_array(array($this, 'BaseInformationObject::__set'), $args);
    }
  }

  protected function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('title', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public function save($connection = null)
  {
    parent::save($connection);

    // Save child information objects
    foreach ($this->informationObjectsRelatedByparentId->transient as $item)
    {
      // TODO Needed if $this is new, should be transparent
      $item->parent = $this;

      try
      {
        $item->save($connection);
      }
      catch (PropelException $e)
      {
      }
    }

    // Save updated related events (update search index after updating all
    // related objects that are included in the index document)
    foreach ($this->events as $item)
    {
      $item->setIndexOnSave(false);

      // TODO Needed if $this is new, should be transparent
      $item->informationObject = $this;

      try
      {
        $item->save($connection);
      }
      catch (PropelException $e)
      {
      }
    }

    // Save new digital objects
    // TODO Allow adding additional digital objects as derivatives
    foreach ($this->digitalObjects as $item)
    {
      $item->indexOnSave = false;

      // TODO Needed if $this is new, should be transparent
      $item->informationObject = $this;

      try
      {
        $item->save($connection);
      }
      catch (PropelException $e)
      {
      }

      break; // Save only one digital object per information object
    }

    // Save updated Status
    $hasPubStatus = false;
    foreach ($this->statuss as $item)
    {
      if (QubitTerm::STATUS_TYPE_PUBLICATION_ID == $item->typeId)
      {
        $hasPubStatus = true;
      }

      $item->setIndexOnSave(false);

      // TODO Needed if $this is new, should be transparent
      $item->object = $this;

      $item->save($connection);
    }

    // Force a publication status
    if ($this->id != QubitInformationObject::ROOT_ID && !$hasPubStatus)
    {
      $status = new QubitStatus;
      $status->objectId = $this->id;
      $status->typeId = QubitTerm::STATUS_TYPE_PUBLICATION_ID;
      $status->statusId = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);
      $status->setIndexOnSave(false);

      $status->save($connection);
    }

    QubitSearch::updateInformationObject($this);

    return $this;
  }

  public static function getRoot()
  {
    return self::getById(self::ROOT_ID);
  }

  /**
   * Additional actions to take on delete
   *
   */
  public function delete($connection = null)
  {
    QubitSearch::deleteInformationObject($this);

    $this->deletePhysicalObjectRelations();

    // Delete subject relations
    $criteria = new Criteria;
    $criteria = $this->addrelationsRelatedBysubjectIdCriteria($criteria);

    if ($subjectRelations = QubitRelation::get($criteria))
    {
      foreach ($subjectRelations as $subjectRelation)
      {
        $subjectRelation->delete();
      }
    }

    // Delete object relations
    $criteria = new Criteria;
    $criteria = $this->addrelationsRelatedByobjectIdCriteria($criteria);

    if ($objectRelations = QubitRelation::get($criteria))
    {
      foreach ($objectRelations as $objectRelation)
      {
        $objectRelation->delete();
      }
    }

    parent::delete($connection);
  }

  /**
   * Get all information objects updated between two dates
   * @date from, the inferior limit date
   * @date util, the superior limit date
   * @return QubitQuery collection of QubitInformationObjects
   */
  public static function getUpdatedRecords($from = '', $until = '', $set = '')
  {
    $criteria = new Criteria;

    $criteria->addJoin(QubitInformationObject::ID, QubitObject::ID);
    if ($from != '')
    {
      $criteria->add(QubitObject::UPDATED_AT, $from, Criteria::GREATER_EQUAL);
    }

    if ($until != '')
    {
      $criteria->add(QubitObject::UPDATED_AT, $until, Criteria::LESS_EQUAL);
    }
    if ($set != '')
    {
      $criteria->add(QubitInformationObject::LFT, $set['lft'], Criteria::GREATER_EQUAL);
      $criteria->add(QubitInformationObject::RGT, $set['rgt'], Criteria::LESS_EQUAL);
    }
    $criteria->add(QubitInformationObject::PARENT_ID, null, Criteria::ISNOTNULL);
    $criteria->addAscendingOrderByColumn(QubitObject::UPDATED_AT);
    return QubitInformationObject::get($criteria);
  }

  public function setMaterialType($materialType)
  {
    // add the materialType to term list (assuming it's a new subject)
    // TODO check first to see if this term exists, in which case, just get its ID
    $newTerm = new QubitTerm;
    $newTerm->setTaxonomyId(QubitTaxonomy::MATERIAL_TYPE_ID);
    $newTerm->setName($materialType);
    $newTerm->save();

    // associate this new subject term with this information object
    $this->addTermRelation($newTerm->id);
  }

  public function getMaterialTypes()
  {
    return $this->getTermRelations(QubitTaxonomy::MATERIAL_TYPE_ID);
  }

  public function getMediaTypes()
  {
    //TO DO: get via linked digital objects & physical objects
  }

  public function getRepositoryCountry()
  {
    if ($this->getRepositoryId())
    {
      return $this->getRepository()->getCountry();
    }
    else
    {
      return null;
    }
  }

  /**
   * Wrapper for getRepository method to allow inheriting repo from ancestors
   *
   * @param array $options optional parameters
   * @return QubitRepository repository object
   */
  public function getRepository(array $options = array())
  {
    $repositoryId = parent::offsetGet('repositoryId', $options);
    $repository = QubitRepository::getById($repositoryId);

    if (isset($options['inherit']) && false !== $options['inherit'])
    {
      if (null === $repository)
      {
        // Ascend up object hierarchy until a related repository is found
        foreach ($this->getAncestors() as $ancestor)
        {
          if (null !== $repository = $ancestor->getRepository())
          {
            break;
          }
        }
      }
    }

    return $repository;
  }

  /**************************
     Nested Set (Hierarchy)
  ***************************/

  /**
   * Get direct descendants of current object.
   *
   * @param array $options optional parameters
   * @return QubitQuery collection of children
   */
  public function getChildren($options = array())
  {
    $c = new Criteria;
    $c->add(QubitInformationObject::PARENT_ID, $this->id);

    $sortBy = (isset($options['sortBy'])) ? $options['sortBy'] : 'lft';

    switch ($sortBy)
    {
      case 'identifierTitle':
        $c = QubitCultureFallback::addFallbackCriteria($c, 'QubitInformationObject');
        $c->addAscendingOrderByColumn('identifier');
        $c->addAscendingOrderByColumn('title');
        break;
      case 'title':
        $c = QubitCultureFallback::addFallbackCriteria($c, 'QubitInformationObject');
        $c->addAscendingOrderByColumn('title');
        break;
      case 'none':
      case 'lft':
      default:
        $c->addAscendingOrderByColumn('lft');
    }

    return QubitInformationObject::get($c, $options);
  }

  /**
   * Get all info objects that have the root node as a parent, and have children
   * (not orphans)
   *
   * @return QubitQuery collection of QubitInformationObjects
   */
  public static function getCollections()
  {
    $criteria = new Criteria;
    $criteria->addAlias('parent', QubitInformationObject::TABLE_NAME);
    $criteria->addJoin(QubitInformationObject::PARENT_ID, 'parent.id');

    // For a node with no children: rgt = (lft+1); therefore search for nodes
    // with: rgt > (lft+1)
    $criteria->add(QubitInformationObject::RGT, QubitInformationObject::RGT.' > ('.QubitInformationObject::LFT.' + 1)', Criteria::CUSTOM);
    $criteria->add('parent.lft', 1);

    return QubitInformationObject::get($criteria);
  }

  public function getCollectionRoot()
  {
    return $this->ancestors->andSelf()->orderBy('lft')->__get(1);
  }

  public function setRoot()
  {
    $criteria = new Criteria;
    $criteria = QubitInformationObject::addRootsCriteria($criteria);
    $parentId = QubitInformationObject::getOne($criteria)->id;

    $this->parentId = $parentId;
  }

  protected function updateNestedSet($connection = null)
  {
    if (!$this->disableNestedSetUpdating)
    {
      return parent::updateNestedSet($connection);
    }
  }

  /***********************
   Actor/Event relations
  ************************/

  public function getActors($options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitEvent::ACTOR_ID);
    $criteria->add(QubitEvent::INFORMATION_OBJECT_ID, $this->id);

    if (isset($options['eventTypeId']))
    {
      $criteria->add(QubitEvent::TYPE_ID, $options['eventTypeId']);
    }

    if (isset($options['cultureFallback']) && true === $options['cultureFallback'])
    {
      $criteria->addAscendingOrderByColumn('authorized_form_of_name');
      $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor', $options);
    }

    $actors = QubitActor::get($criteria);

    // allow inheriting actors from ancestors
    if (isset($options['inherit']) && false !== $options['inherit'])
    {
      if (0 === count($actors))
      {
        // Ascend up object hierarchy until an actor is found
        foreach ($this->getAncestors() as $ancestor)
        {
          if (0 !== count($actors = $ancestor->getActors($options)))
          {
            break;
          }
        }
      }
    }

    return $actors;
  }

  public function getCreators($options = array())
  {
    return $this->getActors($options = array('eventTypeId' => QubitTerm::CREATION_ID));
  }

  public function getPublishers()
  {
    return $this->getActors($options = array('eventTypeId' => QubitTerm::PUBLICATION_ID));
  }

  public function getContributors()
  {
    return $this->getActors($options = array('eventTypeId' => QubitTerm::CONTRIBUTION_ID));
  }

  public function getActorEvents(array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitEvent::INFORMATION_OBJECT_ID, $this->id);
    $criteria->add(QubitEvent::ACTOR_ID, null, Criteria::ISNOTNULL);
    if (isset($options['eventTypeId']))
    {
      $criteria->add(QubitEvent::TYPE_ID, $options['eventTypeId']);
    }
    $criteria->addDescendingOrderByColumn(QubitEvent::START_DATE);

    return QubitEvent::get($criteria);
  }

  public function getCreationEvents()
  {
    $criteria = new Criteria;
    $criteria->add(QubitEvent::INFORMATION_OBJECT_ID, $this->id);
    $criteria->add(QubitEvent::TYPE_ID, QubitTerm::CREATION_ID);

    $criteria->addDescendingOrderByColumn(QubitEvent::START_DATE);

    return QubitEvent::get($criteria);
  }

  /**
   * Related events which have a date
   */
  public function getDates(array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitEvent::INFORMATION_OBJECT_ID, $this->id);

    $criteria->addMultipleJoin(array(
      array(QubitEvent::ID, QubitEventI18n::ID),
      array(QubitEvent::SOURCE_CULTURE, QubitEventI18n::CULTURE)),
      Criteria::LEFT_JOIN);

    $criteria->add($criteria->getNewCriterion(QubitEvent::END_DATE, null, Criteria::ISNOTNULL)
      ->addOr($criteria->getNewCriterion(QubitEvent::START_DATE, null, Criteria::ISNOTNULL))
      ->addOr($criteria->getNewCriterion(QubitEventI18n::DATE, null, Criteria::ISNOTNULL)));

    if (isset($options['type_id']))
    {
      $criteria->add(QubitEvent::TYPE_ID, $options['type_id']);
    }

    $criteria->addDescendingOrderByColumn(QubitEvent::START_DATE);

    return QubitEvent::get($criteria);
  }

  /**
   * Get an array of name access points related to this InformationObject.
   *
   * @return array of related QubitEvent objects.
   */
  public function getNameAccessPoints()
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::SUBJECT_ID, $this->id);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);

    return QubitRelation::get($criteria);
  }

  /**
   * Get name access point by $actorId and $eventTypeId (should be unique)
   *
   * @param integer $actorId foreign key to QubitActor::ID
   * @param integer $eventTypeId foreign key to QubitTerm (even type taxonomy)
   * @return QubitEvent object or NULL if no matching relation found
   */
  public function getNameAccessPoint($actorId, $eventTypeId)
  {
    $criteria = new Criteria;

    $criteria->add(QubitEvent::INFORMATION_OBJECT_ID, $this->id);
    $criteria->add(QubitEvent::ACTOR_ID, $actorId);
    $criteria->add(QubitEvent::TYPE_ID, $eventTypeId);

    return QubitEvent::getOne($criteria);
  }

  /********************
     Term relations
  *********************/

  /**
   * Add a many-to-many Term relation to this information object.
   *
   * @param integer $termId primary key of QubitTerm
   * @param string  $relationNote descriptive string (optional)
   * @return QubitInformationObject $this
   */
  public function addTermRelation($termId, $options = array())
  {
    // Don't add a term relation to this information object that already exists.
    if ($this->getTermRelation($termId) === null)
    {
      $newTermRelation = new QubitObjectTermRelation;
      $newTermRelation->setTermId($termId);

      $this->objectTermRelationsRelatedByobjectId[] = $newTermRelation;
    }

    return $this;
  }

  public function getTermRelations($taxonomyId = 'all')
  {
    $criteria = new Criteria;
    $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);

    if ($taxonomyId != 'all')
    {
      $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
      $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);
    }

    return QubitObjectTermRelation::get($criteria);
  }

  /**
   * Get related term object by id (should be unique)
   *
   * @param
   */
  public function getTermRelation($termId)
  {
    $criteria = new Criteria;
    $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);
    $criteria->add(QubitObjectTermRelation::TERM_ID, $termId);

    return QubitObjectTermRelation::getOne($criteria);
  }

  public function setSubjectAccessPoint($subject)
  {
    // add the subject to term list (assuming it's a new subject)
    // TODO check first to see if this term exists, in which case, just get its ID
    $newTerm = new QubitTerm;
    $newTerm->setTaxonomyId(QubitTaxonomy::SUBJECT_ID);
    $newTerm->setName($subject);
    $newTerm->save();

    // associate this new subject term with this information object
    $this->addTermRelation($newTerm->id);
  }

  public function getSubjectAccessPoints()
  {
    return $this->getTermRelations(QubitTaxonomy::SUBJECT_ID);
  }

  public function getPlaceAccessPoints()
  {
    return $this->getTermRelations(QubitTaxonomy::PLACE_ID);
  }

  /**************
    Properties
  ***************/

  /**
   * Add a property related to this information object
   *
   * @param string $name  Name of property
   * @param string $value Value of property
   * @param string $options array of optional parameters
   * @return QubitInformationObject this information object
   */
  public function addProperty($name, $value, $options = array())
  {
    // Don't re-add a property that exists already
    if (null != $this->id && QubitProperty::isExistent($this->id, $name, $value, $options))
    {
      return;
    }

    $property = new QubitProperty;
    $property->setName($name);
    $property->setValue($value, $options);

    if (isset($options['scope']))
    {
      $property->setScope($options['scope']);
    }

    // Add property to related items, to save with QubitInfoObject::save();
    $this->propertys[] = $property;

    return $this;
  }

  /**
   * Return all properties related to this information object,
   * with option of filtering by name and/or scope
   *
   * @param string $name filter results by name (optional)
   * @param string $scope filter results by scope (optional)
   * @return QubitQuery list of QubitProperty objects matching criteria
   */
  public function getProperties($name = null, $scope = null)
  {
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->id);
    if ($name)
    {
      $criteria->add(QubitProperty::NAME, $name);
    }
    if ($scope)
    {
      $criteria->add(QubitProperty::SCOPE, $scope);
    }

    return QubitProperty::get($criteria);
  }

  /**
   * Get first matching related property by name (optionally scope).
   * Return an empty QubitProperty object if a matching one doesn't exist.
   *
   * @param string $name
   * @param array $options
   * @return QubitProperty
   */
  public function getPropertyByName($name, $options = array())
  {
    if (null === $property = QubitProperty::getOneByObjectIdAndName($this->id, $name, $options))
    {
      $property = new QubitProperty;
    }

    return $property;
  }

  /**
   * Save a related property and create a new property if a matching one doesn't
   * already exist.
   *
   * @param string $name name of property
   * @param string $value new value to set
   * @param array $options array of options
   * @return QubitInformationObject
   */
  public function saveProperty($name, $value, $options = array())
  {
    // Get existing property if possible
    if (null === ($property = QubitProperty::getOneByObjectIdAndName($this->id, $name, $options)))
    {
      // Create a new property if required
      $property = new QubitProperty;
      $property->setObjectId($this->id);
      $property->setName($name);

      if (isset($options['scope']))
      {
        $property->setScope($options['scope']);
      }
    }

    $property->setValue($value, $options);
    $property->save();

    return $this;
  }

  /*****************************************
        Generate Strings for Search Index
  ******************************************/

  public function getCreatorsNameString($options = array())
  {
    if ($this->getCreators())
    {
      $culture = (isset($options['culture'])) ? $options['culture'] : sfContext::getInstance()->user->getCulture();
      $creatorNameString = '';
      $creators = $this->getCreators();
      foreach ($creators as $creator)
      {
        $creatorNameString .= $creator->getAuthorizedFormOfName(array('culture' => $culture)).' ';
        foreach ($creator->getOtherNames() as $otherName)
        {
          $creatorNameString .= $otherName->getName(array('culture' => $culture)).' ';
        }
      }

      return $creatorNameString;
    }
    else
    {
      return null;
    }
  }

  public function getCreatorsHistoryString($options = array())
  {
    if ($this->getCreators())
    {
      $culture = (isset($options['culture'])) ? $options['culture'] : sfContext::getInstance()->user->getCulture();
      $creatorHistoryString = '';
      $creators = $this->getCreators();
      foreach ($creators as $creator)
      {
        $creatorHistoryString .= $creator->getHistory(array('culture' => $culture)).' ';
      }

      return $creatorHistoryString;
    }
    else
    {
      return null;
    }
  }

  public function getDatesString($options = array())
  {
    if ($this->getDates())
    {
      $culture = (isset($options['culture'])) ? $options['culture'] : sfContext::getInstance()->user->getCulture();
      $datesString = '';
      $dates = $this->getDates();
      foreach ($dates as $date)
      {
        $datesString .= $date->getDate(array('culture' => $culture)).' ';
      }

      return $datesString;
    }
    else
    {
      return null;
    }
  }

  public function getAccessPointsString($typeId, $options = array())
  {
    $str = '';
    $accessPoints = $this->getTermRelations($typeId);

    if ($accessPoints)
    {
      $list = array();
      $culture = (isset($options['culture'])) ? $options['culture'] : sfContext::getInstance()->user->getCulture();

      foreach ($accessPoints as $accessPoint)
      {
        $term = $accessPoint->getTerm();
        $list[] = $term->getName(array('culture' => $culture));

        if (0 < count($term->otherNames))
        {
          foreach ($term->otherNames as $altLabel)
          {
            $list[] = $altLabel->getName(array('culture' => $culture));
          }
        }
      }

      $str = implode(' ', $list);
    }

    return $str;
  }

  public function getNameAccessPointsString($options = array())
  {
    $nameAccessPointString = '';

    $criteria = new Criteria;
    $criteria->add(QubitRelation::SUBJECT_ID, $this->id);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);

    $culture = (isset($options['culture'])) ? $options['culture'] : sfContext::getInstance()->user->getCulture();

    foreach ($this->nameAccessPoints = QubitRelation::get($criteria) as $name)
    {
      $nameAccessPointString .= $name->object->getAuthorizedFormOfName(array('culture' => $culture)).' ';
    }

    return $nameAccessPointString;
  }

  /********************
    Physical Objects
  *********************/

  /**
   * Add a relation from this info object to a phyical object. Check to make
   * sure the relationship is unique.
   *
   * @param QubitPhysicalObject $physicalObject Subject of relationship
   * @return QubitInformationObject this object
   */
  public function addPhysicalObject($physicalObject)
  {
    // Verify that $physicalObject is really a Physical Object and
    // Don't add an identical info object -> physical object relationship
    if (get_class($physicalObject) == 'QubitPhysicalObject' && $this->getPhysicalObject($physicalObject->id) === null)
    {
      $relation = new QubitRelation;
      $relation->setSubject($physicalObject);
      $relation->setTypeId(QubitTerm::HAS_PHYSICAL_OBJECT_ID);

      $this->relationsRelatedByobjectId[] = $relation;
    }

    return $this;
  }

  /**
   * Get a specific physical object related to this info object
   *
   * @param integer $physicalObjectId the id of the related physical object
   * @return mixed the QubitRelation object on success, null if no match found
   */
  public function getPhysicalObject($physicalObjectId)
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $this->id);
    $criteria->add(QubitRelation::SUBJECT_ID, $physicalObjectId);

    return QubitRelation::getOne($criteria);
  }

  /**
   * Get all physical objects related to this info object
   *
   */
  public function getPhysicalObjects()
  {
    $relatedPhysicalObjects = QubitRelation::getRelatedSubjectsByObjectId('QubitPhysicalObject', $this->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID));

    return $relatedPhysicalObjects;
  }

  /**
   * Cascade delete child records in q_relation
   *
   */
  protected function deletePhysicalObjectRelations()
  {
    $relations = QubitRelation::getRelationsByObjectId($this->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID));

    foreach ($relations as $relation)
    {
      $relation->delete();
    }
  }

  /******************
    Digital Objects
  ******************/

  /**
   * Get the digital object related to this information object. The
   * informationObject to digitalObject relationship is "one to zero or one".
   *
   * @return mixed QubitDigitalObject or null
   */
  public function getDigitalObject()
  {
    $digitalObjects = $this->getDigitalObjects();
    if (count($digitalObjects) > 0)
    {
      return $digitalObjects[0];
    }
    else
    {
      return null;
    }
  }

  /****************
   Import methods
  *****************/

  /**
   * Wrapper for QubitDigitalObject::importFromUri() method
   *
   * @param array $uris URIs of remote files
   * @return QubitInformationObject $this
   *
   * @TODO allow for different usage types
   */
  public function importDigitalObjectFromUri($uris)
  {
    if (is_array($uris) && 1 < count($uris))
    {
      // Get publication status from current object
      $pubStatus = null;
      if (isset($this->statuss) && 0 < count($this->statuss))
      {
        foreach ($this->statuss as $status)
        {
          if (QubitTerm::STATUS_TYPE_PUBLICATION_ID == $status->typeId)
          {
            $pubStatus = $status->statusId;
            break;
          }
        }
      }

      foreach ($uris as $uri)
      {
        $infoObject = new QubitInformationObject;

        $digitalObject = new QubitDigitalObject;
        $digitalObject->usageId = QubitTerm::MASTER_ID;
        $digitalObject->importFromUri($uri);

        $infoObject->digitalObjects[] = $digitalObject;
        $infoObject->title = $digitalObject->name;

        if (isset($pubStatus))
        {
          $infoObject->setStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID, 'statusId' => $pubStatus));
        }

        $this->informationObjectsRelatedByparentId[] = $infoObject;
      }
    }
    else
    {
      $digitalObject = new QubitDigitalObject;
      $digitalObject->usageId = QubitTerm::MASTER_ID;

      if (is_array($uris))
      {
        $uris = array_shift($uris);
      }
      $digitalObject->importFromUri($uris);

      $this->digitalObjects[] = $digitalObject;
    }

    return $this;
  }

  /**
   * Wrapper for QubitDigitalObject::importFromBase64() method
   *
   * @param string $encodedString base-64 encoded data
   * @param string $filename name of destination file
   * @return QubitInformationObject $this
   *
   * @TODO allow for different usage types
   */
  public function importDigitalObjectFromBase64($encodedString, $filename)
  {
    $digitalObject = new QubitDigitalObject;
    $digitalObject->usageId = QubitTerm::MASTER_ID;
    $digitalObject->importFromBase64($encodedString, $filename);

    $this->digitalObjects[] = $digitalObject;
  }

  public function setRepositoryByName($name)
  {
    // ignore if repository URL instead of name is being passed
    if (strtolower(substr($name, 0, 4)) !== 'http')
    {
      // see if Repository record already exists, if so link to it
      $criteria = new Criteria;
      $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
      $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, $name);
      if ($actor = QubitActor::getOne($criteria))
      {
        if ($actor->getClassName() == 'QubitRepository')
        {
          $this->setRepositoryId($actor->id);
        }
        //TODO figure out how to create a Repository from an existing Actor
        //e.g. if the Actor record exists but it is not yet been used as a Repository
      }
      else
      {
        // if the repository does not already exist, create a new Repository and link to it
        $repository = new QubitRepository;
        $repository->setAuthorizedFormOfName($name);
        $repository->save();
        $this->setRepositoryId($repository->id);
      }
    }
  }

  public function setRepositoryAddress($address)
  {
    if ($repository = $this->getRepository())
    {
      if ($primaryContact = $repository->getPrimaryContact())
      {
        if (is_null($primaryContact->getStreetAddress()))
        {
          $primaryContact->setStreetAddress($address);
          $primaryContact->save();
        }
      }
      else
      {
        $contactInformation = new QubitContactInformation;
        $contactInformation->setStreetAddress($address);
        $contactInformation->setPrimaryContact(true);
        $contactInformation->setActorId($repository->id);
        $contactInformation->save();
      }
    }
  }

  /**
   * Import access points (only subjects and places)
   */
  public function setAccessPointByName($name, $options = array())
  {
    // Only create an linked access point if the type is indicated
    if (!isset($options['type_id']))
    {
      return;
    }

    // See if the access point record already exists, if not create it
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::NAME, $name);
    $criteria->add(QubitTerm::TAXONOMY_ID, $options['type_id']);

    if (null === $accessPoint = QubitTerm::getOne($criteria))
    {
      $accessPoint = new QubitTerm;
      $accessPoint->setName($name);
      $accessPoint->setTaxonomyId($options['type_id']);
      $accessPoint->save();
    }

    $relation = new QubitObjectTermRelation;
    $relation->term = $accessPoint;

    $this->objectTermRelationsRelatedByobjectId[] = $relation;
  }

  public function setActorByName($name, $options)
  {
    // Only create an linked Actor if the event or relation type is indicated
    if (!isset($options['event_type_id']) && !isset($options['relation_type_id']))
    {
      return;
    }

    // See if the Actor record already exists, if not create it
    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
    $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, $name);

    if (null === $actor = QubitActor::getOne($criteria))
    {
      $actor = new QubitActor;

      // Make root actor the parent of new actors
      $actor->parentId = QubitActor::ROOT_ID;

      $actor->setAuthorizedFormOfName($name);
      if (isset($options['entity_type_id']))
      {
        // set actor entityTypeId
        $actor->setEntityTypeId($options['entity_type_id']);
      }
      if (isset($options['source']))
      {
        // set actor entityTypeId
        $actor->setSources($options['source']);
      }
      if (isset($options['rules']))
      {
        // set actor entityTypeId
        $actor->setRules($options['rules']);
      }
      if (isset($options['history']))
      {
        $actor->setHistory($options['history']);
      }
      if (isset($options['dates_of_existence']))
      {
        $actor->datesOfExistence = $options['dates_of_existence'];
      }
      $actor->save();
    }

    if (isset($options['event_type_id']))
    {
      // create an event object to link the information object and actor
      $event = new QubitEvent;
      $event->setActorId($actor->id);
      $event->setTypeId($options['event_type_id']);
      if (isset($options['dates']))
      {
        $event->setDate($options['dates']);
      }
      if (isset($options['date_start']))
      {
        $event->setStartDate($options['date_start']);
      }
      if (isset($options['date_end']))
      {
        $event->setEndDate($options['date_end']);
      }
      if (isset($options['event_note']))
      {
        $event->setDescription($options['event_note']);
      }

      $this->events[] = $event;
    }
    else if (isset($options['relation_type_id']))
    {
      // only add Actor as name access point if they are not already linked to
      // an event (i.e. they are not already a "creator", "accumulator", etc.)
      $existingRelation = false;
      foreach ($this->events as $existingEvent)
      {
        if ($actor->id == $existingEvent->actorId)
        {
          $existingRelation = true;
          break;
        }
      }

      if (!$existingRelation)
      {
        $relation = new QubitRelation;
        $relation->objectId = $actor->id;
        $relation->typeId = QubitTerm::NAME_ACCESS_POINT_ID;

        $this->relationsRelatedBysubjectId[] = $relation;
      }
    }
  }

  /**
   * Import language-related data from a <langusage> tag in EAD2002
   *
   * @param $langusageNode  DOMNode  EAD langusage DOM node
   */
  public function importLangusageEadData($langusageNode)
  {
    // get language nodes
    $langNodeList = QubitXmlImport::queryDomNode($langusageNode, "/xml/langusage/language");

    $languagesOfDescription = array();

    // amalgamate language data
    foreach($langNodeList as $langNode)
    {
      if ($langNode->hasAttributes())
      {
        if ($langNode->attributes->getNamedItem('langcode'))
        {
          $langType = $langNode->getAttribute('encodinganalog');
          $langCode = substr($langNode->getAttribute('langcode'), 0, 2);

          switch($langType)
          {
            case 'Language':
              $this->setLangcode($langCode);
              break;

            case 'Language Of Description':
              array_push($languagesOfDescription, $langCode);
              break;
          }
        }
      }
    }

    // add language(s) of description, if any
    if (count($languagesOfDescription))
    {
      $this->addProperty(
        'languageOfDescription',
        serialize($languagesOfDescription)
      );
    }
  }

  /**
   * Import creation-related data from an <bioghist> tag in EAD2002
   *
   * @param $biogHistNode  DOMNode  EAD bioghist DOM node
   */
  public function importBioghistEadData($biogHistNode)
  {
    // get chronlist element in bioghist element
    $chronlistNodeList = $biogHistNode->getElementsByTagName('chronlist');
    if ($chronlistNodeList->length)
    {
      foreach($chronlistNodeList as $chronlistNode)
      {
        // get chronitem elements in chronlist element
        $chronitemNodeList = $chronlistNode->getElementsByTagName('chronitem');
        foreach($chronitemNodeList as $chronitemNode)
        {
          // get creation date element contents
          $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/date[@type='creation']");
          foreach($dateNodeList as $dateNode) {
            $date = $dateNode->nodeValue;
          }

          // get creation start and end date from "normal" attribute
          $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/date[@type='creation']/@normal");
          foreach($dateNodeList as $dateNormalAttr) {
            $normalizedDates = sfEadPlugin::parseEadDenormalizedDateData($dateNormalAttr->value);

            $date_start = $normalizedDates['start'];

            if ($normalizedDates['end'])
            {
              $date_end = $normalizedDates['end'];
            }
          }

          // get creation end date element contents
          $history = '';
          $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, '/xml/chronitem/eventgrp/event/note[not(@type="eventNote")]/p');
          foreach($dateNodeList as $noteNode) {
            $history = $noteNode->nodeValue;
          }

          $possibleNameFields = array(
            'name'     => QubitTerm::PERSON_ID,
            'persname' => QubitTerm::PERSON_ID,
            'famname'  => QubitTerm::FAMILY_ID,
            'corpname' => QubitTerm::CORPORATE_BODY_ID
          );

          $typeId = QubitTerm::PERSON_ID;
          $name   = '';
          foreach($possibleNameFields as $fieldName => $fieldTypeId)
          {
            $fieldValue = '';
            $nameNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/eventgrp/event/origination/". $fieldName);
            foreach($nameNodeList as $nameNode) {
              $fieldValue = $nameNode->nodeValue;
              $datesValue = $nameNode->getAttribute('source');
            }
            if ($fieldValue != '')
            {
              $name             = $fieldValue;
              $typeId           = $fieldTypeId;
              $datesOfExistence = $datesValue;
            }
          }

          $eventNote = '';
          $eventNoteList = QubitXmlImport::queryDomNode($chronitemNode, '/xml/chronitem/eventgrp/event/note[@type="eventNote"]/p');
          foreach($eventNoteList as $eventNoteNode) {
            $eventNote = $eventNoteNode->nodeValue;
          }

          $eventSpec = array(
            'event_type_id' => QubitTerm::CREATION_ID,
            'history'       => $history
          );

          if ($date)
          {
            $eventSpec['dates'] = $date;
          }

          if ($datesOfExistence)
          {
            $eventSpec['dates_of_existence'] = $datesOfExistence;
          }

          if ($date_start)
          {
            $eventSpec['date_start'] = $date_start;
          }

          if ($date_end)
          {
            $eventSpec['date_end'] = $date_end;
          }

          if (0 < strlen($eventNote))
          {
            $eventSpec['event_note'] = $eventNote;
          }

          $this->setActorByName($name, $eventSpec);
        }
      }
    }
  }

  /**
   * Import actor history from on <bioghist> tag in EAD2002
   *
   * @param $history string actor history
   */
  public function setHistoryByOrigination($history)
  {
    // Check events array for related events/actors (we may not have saved this
    // data to the database yet)
    if (0 < count($relatedEvents = $this->events))
    {
      foreach ($relatedEvents as $event)
      {
        if (null !== ($actor = $event->getActor()))
        {
          $actor->setHistory($history);
          $actor->save();
          break;
        }
      }
    }
  }

  public function setLevelOfDescriptionByName($name)
  {
    // don't proceed with empty $name, or if 'otherlevel' value is passed
    if (0 < strlen($name) && $name !== 'otherlevel')
    {
      // see if Level of Description term already exists, if so link to it
      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $name);
      if ($term = QubitTermI18n::getOne($criteria))
      {
        $this->levelOfDescriptionId = $term->id;
      }
      else
      {
        // if the Level of Description term does not already exist, create a new Level and link to it
        $term = new QubitTerm;
        $term->setTaxonomyId(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
        $term->setName($name);
        $term->setRoot();
        $term->save();
        $this->levelOfDescriptionId = $term->id;
      }
    }
  }

  /**
   * Set publication status using a status name
   *
   * @param $name  valid publication status name
   */
  public function setPublicationStatusByName($name)
  {
    $criteria = new Criteria;
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PUBLICATION_STATUS_ID);
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::NAME, $name);
    if ($term = QubitTermI18n::getOne($criteria))
    {
      $this->setStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID, 'statusId' => $term->id));
    }
  }

  /**
   * Set description level of detail using a name
   *
   * @param $name  valid description detail level name
   */
  public function setDescriptionLevelOfDetailByName($name)
  {
    $this->setTermIdPropertyUsingTermName(
      'descriptionDetailId',
      $name,
      QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID
    );
  }

  /**
   * Set description status using a status name
   *
   * @param $name  valid publication status name
   */
  public function setDescriptionStatusByName($name)
  {
    $this->setTermIdPropertyUsingTermName(
      'descriptionStatusId',
      $name,
      QubitTaxonomy::DESCRIPTION_STATUS_ID
    );
  }

  /**
   * Set term ID property using term name
   *
   * @param $property  object property to set
   * @param $name  valid term name
   * @param $taxonomyId  taxonomy ID
   */
  public function setTermIdPropertyUsingTermName($property, $name, $taxonomyId)
  {
    static $termNames;

    if (!isset($termNames))
    {
      $termNames = array();
    }

    if (!isset($termNames[$taxonomyId]))
    {
      $termNames[$taxonomyId] = array();
      $terms = QubitTaxonomy::getTaxonomyTerms($taxonomyId);
      foreach($terms as $term) {
        $termNames[$taxonomyId][strtolower($term->name)] = $term->id;
      }
    }

    if (isset($termNames[$taxonomyId][strtolower($name)]))
    {
      $this->$property = $termNames[$taxonomyId][strtolower($name)];
    }
  }

  public function setDates($date, $options = array())
  {
    // parse the normalized dates into an Event start and end date
    $normalizedDate = array();
    if (isset($options['normalized_dates']))
    {
      preg_match('/(?P<start>\d{4}(-\d{2})?(-\d{2})?)\/?(?P<end>\d{4}(-\d{2})?(-\d{2})?)?/', $options['normalized_dates'], $matches);
      $normalizedDate['start'] = new DateTime($this->getDefaultDateValue($matches['start']));
      if (isset($matches['end']))
      {
        $normalizedDate['end'] = new DateTime($this->getDefaultDateValue($matches['end']));
      }
      else
      {
        $normalizedDate['end'] = null;
      }
    }
    else
    {
      $normalizedDate['start'] = null;
      $normalizedDate['end'] = null;
    }

    // determine the Event type
    if (isset($options['date_type']))
    {
      $eventType = $options['date_type'];
      // see if Event Type already exists, if so use it
      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::EVENT_TYPE_ID);
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $eventType);
      if ($term = QubitTermI18n::getOne($criteria))
      {
        $eventTypeId = $term->id;
      }
      else
      {
        // if the Event Type does not already exist, create a new type and use it
        $term = new QubitTerm;
        $term->setTaxonomyId(QubitTaxonomy::EVENT_TYPE_ID);
        $term->setName($eventType);
        $term->setRoot();
        $term->save();
        $eventTypeId = $term->id;
      }
    }
    else
    {
      // set event type to 'creation' by default
      $eventTypeId = QubitTerm::CREATION_ID;
    }

    // if this information object is not linked to a creator, create an event object
    // and link it to the information object
    $event = new QubitEvent;
    $event->setTypeId($eventTypeId);
    $event->setStartDate($normalizedDate['start']);
    $event->setEndDate($normalizedDate['end']);
    $event->setDate($date);

    $this->events[] = $event;
  }

  protected function getDefaultDateValue($date)
  {
    if (strlen($date) == 4)
    {
      return $date.'-01-01';
    }
    else if (strlen($date) == 7)
    {
      return $date.'-01';
    }

    return $date;
  }

  public function setIdentifierWithCodes($identifier, $options)
  {
    $this->setIdentifier($identifier);

    if ($repository = QubitRepository::getById($this->getRepositoryId()))
    {
      // if the repository doesn't already have a code, set it using the <unitid repositorycode=""> value
      if (isset($options['repositorycode']))
      {
        if (!$repository->getIdentifier())
        {
          $repository->setIdentifier($options['repositorycode']);
          $repository->save();
        }
      }
      // if the repository doesn't already have an country code, set it using the <unitid countrycode=""> value
      if (isset($options['countrycode']))
      {
        if (!$repository->getCountryCode())
        {
          if ($primaryContact = $repository->getPrimaryContact())
          {
            $primaryContact->setCountryCode(strtoupper($options['countrycode']));
            $primaryContact->save();
          }
          else if (count($contacts = $repository->getContactInformation()) > 0)
          {
            $contacts[0]->setCountryCode(strtoupper($options['countrycode']));
            $contacts[0]->save();
          }
          else
          {
            $contactInformation = new QubitContactInformation;
            $contactInformation->setCountryCode(strtoupper($options['countrycode']));
            $contactInformation->setActorId($repository->id);
            $contactInformation->save();
          }
        }
      }
    }
  }

  public function setTermRelationByName($name, $options)
  {
    // see if subject term already exists
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, $options['taxonomyId']);
    $criteria->add(QubitTermI18n::NAME, $name);
    if (null === $term = QubitTerm::getOne($criteria))
    {
      $term = new QubitTerm;
      $term->setTaxonomyId($options['taxonomyId']);
      $term->setName($name);
      $term->setRoot();
      $term->save();
      if (isset($options['source']))
      {
        $note = new QubitNote;
        $note->content = $options['source'];
        $note->typeId = QubitTerm::SOURCE_NOTE_ID;
        $note->userId = sfContext::getInstance()->user->getAttribute('user_id');

        $term->notes[] = $note;
      }
    }

    $this->addTermRelation($term->id);
  }

  public function setPhysicalObjectByName($physicalObjectName, $options)
  {
    // see if physical object already exists, otherwise create a new physical object
    $criteria = new Criteria;
    $criteria->addJoin(QubitPhysicalObject::ID, QubitPhysicalObjectI18n::ID);
    $criteria->add(QubitPhysicalObjectI18n::NAME, $physicalObjectName);
    if ($existingPhysicalObject = QubitPhysicalObject::getOne($criteria))
    {
      $this->addPhysicalObject($existingPhysicalObject);
    }
    else
    {
      $newPhysicalObject = new QubitPhysicalObject;
      $newPhysicalObject->setName($physicalObjectName);

      // see if physical object type already exists, otherwise create a new one
      if ($options['type'])
      {
        $criteria = new Criteria;
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID);
        $criteria->add(QubitTermI18n::NAME, $options['type']);
        if ($physicalObjectType = QubitTerm::getOne($criteria))
        {
          $newPhysicalObject->setTypeId($physicalObjectType->id);
        }
        else
        {
          $newTerm = new QubitTerm;
          $newTerm->setTaxonomyId(QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID);
          $newTerm->setName($options['type']);
          $newTerm->parentId = QubitTerm::CONTAINER_ID;
          $newTerm->save();
          $newPhysicalObject->setTypeId($newTerm->id);
        }
      }

      if (isset($options['location']))
      {
        $newPhysicalObject->setLocation($options['location']);
      }
      $newPhysicalObject->save();
      $this->addPhysicalObject($newPhysicalObject);
    }
  }

  public function importPhysicalObject($textInstances, $name = false, $type = false)
  {
    $location = (is_array($textInstances)) ? $textInstances[0] : '';

    // if a type has been provided, look it up
    $term = ($type)
      ? QubitFlatfileImport::createOrFetchTerm(
          QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID,
          $type
        )
      : false;

    $object = new QubitPhysicalObject();
    $object->location = trim($location);
    $object->name = trim($name);

    if ($term)
    {
      $object->typeId = $term->id;
    }

    $object->save();

    $this->addPhysicalObject($object);
  }

  public function importEadNote(array $options = array())
  {
    $newNote = new QubitNote;
    $newNote->setScope('QubitInformationObject');

    if (isset($options['userId']))
    {
      $newNote->setUserId($options['userId']);
    }

    if (isset($options['note']))
    {
      $newNote->setContent($options['note']);
    }

    if (isset($options['noteTypeId']))
    {
      $newNote->setTypeId($options['noteTypeId']);
    }

    $this->notes[] = $newNote;
  }

  /**************
  OAI methods
  ***************/

  /**
   * Get Record by Oai identifier
   * @param integer $identifier, the oai_identifier
   * @return QubitQuery collection of QubitInformationObjects
   */
  public static function getRecordByOaiID($oai_local_identifier)
  {
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::OAI_LOCAL_IDENTIFIER, $oai_local_identifier);
    return QubitInformationObject::get($criteria)->offsetGet(0, array('defaultValue' => null));
  }

  /**
   * Get Oai identifier
   * @param
   * @return String containing OAI-compliant Identifier
   */

  public function getOaiIdentifier()
  {
    $domain = sfContext::getInstance()->request->getHost();
    $oaiRepositoryCode = QubitSetting::getByName('oai_repository_code')->getValue(array('sourceCulture'=>true));
    $oaiIdentifier = 'oai:'.$domain.':'.$oaiRepositoryCode.'_'.$this->getOaiLocalIdentifier();

    return $oaiIdentifier;
  }

  /**
   * Set source Oai identifier
   * @param
   * @return String set the OAI Identifier returned from the source repository as part of an OAI response
   */

  public function setSourceOaiIdentifier($value)
  {
    $this->addProperty('source_oai_identifier', $value, $options = array('scope' => 'oai', 'sourceCulture' => true));
  }

  public function getSourceOaiIdentifier()
  {
    return $this->getPropertyByName('source_oai_identifier');
  }

  /*****************************************************
   Search Index methods
  *****************************************************/

  public static function getByCulture($culture, $options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitInformationObjectI18n::ID);
    $criteria->add(QubitInformationObjectI18n::CULTURE, $culture);

    return QubitInformationObject::get($criteria, $options);
  }

  /*****************************************************
   Publication Status
  *****************************************************/
  public function getPublicationStatus()
  {
    return $this->getStatus($options = array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID));
  }

  public function setPublicationStatus($value)
  {
    return $this->setStatus($options = array('statusId' => $value, 'typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID));
  }

  /*****************************************************
   TreeView
  *****************************************************/

  public function getTreeViewSiblings(array $options = array())
  {
    // The max number of items that will be shown
    // The final amount may be smaller if there are no result enough
    $limit = 5;
    if (isset($options['limit']))
    {
      $limit = $options['limit'];
    }

    // Show 'previous' or 'next' siblings
    $position = 'next';
    if (isset($options['position']))
    {
      $position = $options['position'];
    }

    // They way the results are sorted
    // 'none' (default), 'title' or 'identifierTitle'
    $sort = sfConfig::get('app_sort_treeview_informationobject', 'none');
    if (isset($options['sort']))
    {
      $sort = $options['sort'];
    }

    // This is the array of objects that we are bulding
    $results = array();

    // We are using $current to store the last sibling found in the last
    // iteration of the following loop
    $current = $this;

    // Query the database and discard results by ACL, repeating the same
    // operation until the number of results requested is fulfilled
    do
    {
      $criteria = new Criteria;
      $criteria->add(QubitInformationObject::PARENT_ID, $this->parentId);

      switch ($sort)
      {
        case 'identifierTitle':

          $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');

          $concatCurrent  = $current->__get('identifier') ?: " ";
          $concatCurrent .= $current->title;
          $concatCurrent .= str_pad($current->lft, 12, '0', STR_PAD_LEFT);
          $concatCurrent = Propel::getConnection()->quote($concatCurrent);

          if ('next' == $position)
          {
            $criteria->add('title',
              'CONVERT(CONCAT(
                  COALESCE(identifier, " "),
                  COALESCE((CASE WHEN (current.TITLE IS NOT NULL AND current.TITLE <> "") THEN current.TITLE ELSE source.TITLE END), ""),
                  LPAD(lft, 12, 0)), CHAR)
                > '.$concatCurrent,
              Criteria::CUSTOM);

            $criteria->addAscendingOrderByColumn('identifier');
            $criteria->addAscendingOrderByColumn('title');
            $criteria->addAscendingOrderByColumn('lft');
          }
          else // 'previous'
          {
            $criteria->add('title',
              'CONVERT(CONCAT(
                COALESCE(identifier, " "),
                COALESCE((CASE WHEN (current.TITLE IS NOT NULL AND current.TITLE <> "") THEN current.TITLE ELSE source.TITLE END), ""),
                LPAD(lft, 12, 0)), CHAR)
                < '.$concatCurrent,
              Criteria::CUSTOM);

            $criteria->addDescendingOrderByColumn('identifier');
            $criteria->addDescendingOrderByColumn('title');
            $criteria->addDescendingOrderByColumn('lft');
          }

          break;

        case 'title':

          $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');

          $concatCurrent = $current->title;
          $concatCurrent .= str_pad($current->lft, 12, '0', STR_PAD_LEFT);
          $concatCurrent = Propel::getConnection()->quote($concatCurrent);

          if ('next' == $position)
          {
            $criteria->add('title',
              'CONVERT(CONCAT(COALESCE((CASE WHEN (current.TITLE IS NOT NULL AND current.TITLE <> "") THEN current.TITLE ELSE source.TITLE END), ""), LPAD(lft, 12, 0)), CHAR)
                > '.$concatCurrent,
              Criteria::CUSTOM);

            $criteria->addAscendingOrderByColumn('title');
            $criteria->addAscendingOrderByColumn('lft');
          }
          else // 'previous'
          {
            $criteria->add('title',
              'CONVERT(CONCAT(COALESCE((CASE WHEN (current.TITLE IS NOT NULL AND current.TITLE <> "") THEN current.TITLE ELSE source.TITLE END), ""), LPAD(lft, 12, 0)), CHAR)
                < '.$concatCurrent,
              Criteria::CUSTOM);

            $criteria->addDescendingOrderByColumn('title');
            $criteria->addDescendingOrderByColumn('lft');
          }

          break;

        default:

          if ('next' == $position)
          {
            $criteria->add(QubitInformationObject::LFT, $current->lft, Criteria::GREATER_THAN);
            $criteria->addAscendingOrderByColumn(QubitInformationObject::LFT);
          }
          else // 'previous'
          {
            $criteria->add(QubitInformationObject::LFT, $current->lft, Criteria::LESS_THAN);
            $criteria->addDescendingOrderByColumn(QubitInformationObject::LFT);
          }
      }

      // This is the number of items we were asked for.
      $criteria->setLimit($limit);

      // Iterate over results and store them in the $results array
      foreach (QubitInformationObject::get($criteria) as $item)
      {
        // We will need this later to control the loop
        $last = $item;

        // ACL checks
        if (!QubitAcl::check($item, 'read'))
        {
          continue;
        }

        // Add item to array
        $results[] = $item;
      }

      // If $last is not set at this time it means that we could not
      // find any results and it is worthless to continue
      if (!isset($last))
      {
        break;
      }

      // Stop the loop if there are no more items to search for
      // Depending on the sorting mode this is done in different ways
      switch ($sort)
      {
        case 'identifierTitle':
        case 'title':

          // Look for the last sibling
          if (!isset($conditionLimit))
          {
            $criteria = new Criteria;
            $criteria->add(QubitInformationObject::PARENT_ID, $this->parentId);
            $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');

            if ('next' == $position)
            {
              if ('identifierTitle' == $sort)
              {
                $criteria->addDescendingOrderByColumn('identifier');
              }
              $criteria->addDescendingOrderByColumn('title');
              $criteria->addDescendingOrderByColumn('lft');
            }
            else
            {
              if ('identifierTitle' == $sort)
              {
                $criteria->addAscendingOrderByColumn('identifier');
              }
              $criteria->addAscendingOrderByColumn('title');
              $criteria->addAscendingOrderByColumn('lft');
            }

            $criteria->setLimit(1);

            if (null === $informationObject = QubitInformationObject::getOne($criteria))
            {
              throw new sfException;
            }

            $conditionLimit = $informationObject->id;
          }

          if ($conditionLimit == $last->id)
          {
            break 2;
          }

          break;

        default:

          if ('next' == $position)
          {
            if (1 == $this->parent->rgt - $last->rgt)
            {
              break 2;
            }
          }
          else // 'previous'
          {
            if (1 == $last->lft - $this->parent->lft)
            {
              break 2;
            }
          }
      }

      // In the next iteration we will need the last sibling found
      $current = $last;
      unset($last);
    }
    while ($limit > count($results));

    return $results;
  }

  public static function addTreeViewSortCriteria($criteria)
  {
    switch (sfConfig::get('app_sort_treeview_informationobject'))
    {
      case 'identifierTitle':
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');
        $criteria->addAscendingOrderByColumn('identifier');
        $criteria->addAscendingOrderByColumn('title');
        $criteria->addAscendingOrderByColumn('lft');

        break;

      case 'title':
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');
        $criteria->addAscendingOrderByColumn('title');
        $criteria->addAscendingOrderByColumn('lft');

        break;

      default:
        $criteria->addAscendingOrderByColumn(self::LFT);
    }

    return $criteria;
  }

  /**
   * Return the correct class instance for the current standards-based template
   * (e.g. 'sfRadPlugin', 'sfIsadPlugin')
   *
   * @param QubitInformationObject resource
   *
   * @return mixed standards-based class instance
   */
  public static function getStandardsBasedInstance(QubitInformationObject $resource)
  {
    if (!isset(sfContext::getInstance()->getRequest()->informationobject_template))
    {
      return $resource;
    }

    $class = 'sf'.ucfirst(sfContext::getInstance()->getRequest()->informationobject_template).'Plugin';

    try
    {
      $instance = new $class($resource);
    }
    catch (Exception $e)
    {
      return $resource;
    }

    return $instance;
  }
}
