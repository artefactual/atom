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
 * @package    AccesstoMemory
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitActor extends BaseActor
{
  public $disableNestedSetUpdating = false;

  const
    ROOT_ID = 3;

  public function __toString()
  {
    $string = $this->authorizedFormOfName;
    if (!isset($string))
    {
      $string = $this->getAuthorizedFormOfName(array('sourceCulture' => true));
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
      case 'language':
      case 'script':

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
    }

    return call_user_func_array(array($this, 'BaseActor::__get'), $args);
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
      case 'script':

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
    }

    return call_user_func_array(array($this, 'BaseActor::__set'), $args);
  }

  protected function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('authorizedFormOfName', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public function save($connection = null)
  {
    if (QubitActor::ROOT_ID != $this->id && !isset($this->parentId) && 'QubitActor' == $this->className)
    {
      $this->parentId = QubitActor::ROOT_ID;
    }
    else if (QubitRepository::ROOT_ID != $this->id && !isset($this->parentId) && 'QubitRepository' == $this->className)
    {
      $this->parentId = QubitRepository::ROOT_ID;
    }

    // Save new digital objects
    // TODO Allow adding additional digital objects as derivatives
    foreach ($this->digitalObjectsRelatedByobjectId as $item)
    {
      $item->indexOnSave = false;

      // TODO Needed if $this is new, should be transparent
      $item->object = $this;
      $item->save($connection);

      break; // Save only one digital object per information object
    }

    parent::save($connection);

    $creationIoIds = $otherIoIds = array();
    $context = sfContext::getInstance();
    $env = $context->getConfiguration()->getEnvironment();

    // Save related event objects
    foreach ($this->events as $event)
    {
      $event->indexOnSave = false;

      // Update search index for related info object, update them
      // in QubitEvent synchronously in CLI tasks and jobs
      if (in_array($env, array('cli', 'worker')))
      {
        $event->indexOnSave = true;
      }
      elseif (isset($event->objectId))
      {
        // Otherwise, do not update in QubitEvent,
        // but save ids to update asynchronously
        if (isset($event->typeId) && $event->typeId == QubitTerm::CREATION_ID)
        {
          $creationIoIds[] = $event->objectId;
        }
        else
        {
          $otherIoIds[] = $event->objectId;
        }
      }

      $event->actor = $this;
      $event->save();
    }

    // Update asynchronously the saved IOs ids, two jobs may
    // be launched in here as creation events require updating
    // the descendants but other events don't.
    if (count($creationIoIds) > 0 || count($otherIoIds) > 0)
    {
      if (count($creationIoIds) > 0)
      {
        $jobOptions = array(
          'ioIds' => $creationIoIds,
          'updateIos' => true,
          'updateDescendants' => true
        );
        QubitJob::runJob('arUpdateEsIoDocumentsJob', $jobOptions);
      }

      if (count($otherIoIds) > 0)
      {
        $jobOptions = array(
          'ioIds' => $otherIoIds,
          'updateIos' => true,
          'updateDescendants' => false
        );
        QubitJob::runJob('arUpdateEsIoDocumentsJob', $jobOptions);
      }

      // Let user know related descriptions update has started
      $jobsUrl = $context->routing->generate(null, array('module' => 'jobs', 'action' => 'browse'));
      $message = $context->i18n->__('Your actor has been updated. Its related descriptions are being updated asynchronously – check the <a href="%1">job scheduler page</a> for status and details.', array('%1' => $jobsUrl));
      $context->user->setFlash('notice', $message);
    }

    // Save related contact information objects
    foreach ($this->contactInformations as $item)
    {
      $item->actor = $this;
      $item->save();
    }

    // Repositories are updated in the save function for QubitRepository class
    // in order to get the i18n values updated in the search index
    if ('QubitActor' == $this->className)
    {
      QubitSearch::getInstance()->update($this);
    }

    return $this;
  }

  public function delete($connection = null)
  {
    // Delete related digitalObjects
    foreach ($this->digitalObjectsRelatedByobjectId as $digitalObject)
    {
      // Set IO to null to avoid ES document update
      $digitalObject->objectId = null;
      $digitalObject->delete();
    }

    foreach ($this->events as $item)
    {
      if (isset($item->object) && isset($item->type))
      {
        unset($item->actor);

        $item->save();
      }
      else
      {
        $item->delete();
      }
    }

    foreach (QubitRelation::getBySubjectOrObjectId($this->id) as $relation)
    {
      $relation->delete();
    }

    if (!($this instanceOf QubitRightsHolder || $this instanceOf QubitDonor))
    {
      QubitSearch::getInstance()->delete($this);
    }

    return parent::delete($connection);
  }

  public static function getRoot()
  {
    return self::getById(self::ROOT_ID);
  }

  public static function getAllExceptUsers($options = array())
  {
    //returns all Actor objects except those that are
    //also an instance of the User class
    $criteria = new Criteria;
    $criteria->add(QubitObject::CLASS_NAME, 'QubitActor');

    // sort by name
    $criteria->addAscendingOrderByColumn('authorized_form_of_name');

    // Do fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor', $options);

    return QubitActor::get($criteria);
  }

  /**
   * Return an options_for_select array
   *
   * @param mixed $default current selected value for select list
   * @param array $options optional parameters
   * @return array options_for_select compatible array
   */
  public static function getOptionsForSelectList($default, $options = array())
  {
    $actors = self::getAllExceptUsers($options);

    foreach ($actors as $actor)
    {
      // Don't display actors with no name
      if ($name = $actor->getAuthorizedFormOfName($options))
      {
        $selectOptions[$actor->id] = $name;
      }
    }

    return options_for_select($selectOptions, $default, $options);
  }

  /**
   * Append criteria to get only Actor objects that are NOT
   * a users or repository.
   *
   * @param Criteria $criteria current search criteria
   * @return Criteria modified criteria object
   */
  public static function addGetOnlyActorsCriteria($criteria)
  {
    $criteria->addJoin(QubitActor::ID, QubitObject::ID);
    $criteria->add(QubitObject::CLASS_NAME, 'QubitActor');

    return $criteria;
  }

  /**
   * Returns only Actor objects, excluding those
   * that are an instance of the User or Repository class
   *
   * @return QubitQuery array of QubitActor objects
   */
  public static function getOnlyActors($criteria=null, $options=array())
  {
    if (is_null($criteria))
    {
      $criteria = new Criteria;
    }

    $criteria = QubitActor::addGetOnlyActorsCriteria($criteria);

    return self::get($criteria);
  }

  public static function getAllNames()
  {
    $actors = self::getOnlyActors();
    $allActorNames = array();
    foreach ($actors as $actor)
    {
      $actorId = $actor->id;
      $allActorNames[] = array('actorId' => $actorId, 'nameId' => null, 'name' => $actor->getAuthorizedFormOfName());
      $actorNames = array();
      $actorNames = $actor->getOtherNames();
      foreach ($actorNames as $name)
      {
        $allActorNames[] = array('actorId' => $actorId, 'nameId' => $name->id, 'name' => $name.' ('.$name->getType().')');
      }
    }

    return $allActorNames;
  }

  /**
   * Add a related property to this actor.
   *
   * @param string $name  name of property
   * @param string $value value of property
   * @param string $options array of optional parameters
   * @return QubitActor this object
   */
  public function addProperty($name, $value, $options = array())
  {
    $property = QubitProperty::addUnique($this->id, $name, $value, $options);

    return $this;
  }

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

  public function getActorNotes()
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitNote::TYPE_ID, QubitTerm::ID);
    $criteria->add(QubitNote::OBJECT_ID, $this->id);
    $criteria->add(QubitNote::SCOPE, 'QubitActor');
    QubitNote::addOrderByPreorder($criteria);

    return QubitNote::get($criteria);
  }

  public function getContactInformation()
  {
    $criteria = new Criteria;
    $criteria->add(QubitContactInformation::ACTOR_ID, $this->id);
    $criteria->addDescendingOrderByColumn(QubitContactInformation::PRIMARY_CONTACT);
    $contactInformation = QubitContactInformation::get($criteria);

    return $contactInformation;
  }

  public function getPrimaryContact()
  {
    $criteria = new Criteria;
    $criteria->add(QubitContactInformation::ACTOR_ID, $this->id);
    $criteria->add(QubitContactInformation::PRIMARY_CONTACT, true);
    $primaryContact = QubitContactInformation::getOne($criteria);

    if ($primaryContact)
    {
      return $primaryContact;
    }
    else
    {
      $criteria = new Criteria;
      $criteria->add(QubitContactInformation::ACTOR_ID, $this->id);

      return QubitContactInformation::getOne($criteria);
    }
  }

  protected $SubjectHitCount = null;

  public function setSubjectHitCount($count)
  {
    $this->SubjectHitCount = $count;
  }

  public function getSubjectHitCount()
  {
    return $this->SubjectHitCount;
  }

  /**
   * Save new link to a term.
   *
   * @param integer $termId QubitTerm primary key
   * @param string $relationNote DEPRECATED
   */
  public function setTermRelation($termId, $relationNote = null)
  {
    $newTermRelation = new QubitObjectTermRelation;
    $newTermRelation->setTermId($termId);

    //TODO: move to QubitNote
    //  $newTermRelation->setRelationNote($relationNote);
    $newTermRelation->setObjectId($this->id);
    $newTermRelation->save();
  }

  /**
   * Get many-to-many links to QubitTerm objects
   *
   * @param mixed $taxonomyId  Limit results by taxonomy type
   * @return QubitQuery collection of QubitObjectTermRelation objects
   */
  public function getTermRelations($taxonomyId = 'all')
  {
    $criteria = new Criteria;
    $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);

    if ($taxonomyId != 'all')
    {
      $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTERM::ID);
      $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);
    }

    return QubitObjectTermRelation::get($criteria);
  }

  public function getDatesOfChanges()
  {
    //TO DO

    return null;
  }

  /**
   * Get actor-to-actor relations linked to this actor
   *
   * @return QubitQuery collection of QubitRelation objects
   */
  public function getActorRelations()
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitRelation::TYPE_ID, QubitTerm::ID);
    $criteria->add($criteria->getNewCriterion(QubitRelation::OBJECT_ID, $this->id)
      ->addOr($criteria->getNewCriterion(QubitRelation::SUBJECT_ID, $this->id))
      ->addAnd($criteria->getNewCriterion(QubitTerm::TAXONOMY_ID, QubitTaxonomy::ACTOR_RELATION_TYPE_ID)));

    $criteria->addAscendingOrderByColumn(QubitRelation::TYPE_ID);
    $criteria->addDescendingOrderByColumn(QubitRelation::START_DATE);

    return QubitRelation::get($criteria);
  }

  /**
   * Search for an actor by the AUTHORIZED_FORM_OF_NAME i18n column. Optionally
   * limit search to a specific culture, history or maintaining repository
   *
   * @param string $name search string
   * @param array $options optional parameters
   * @return QubitActor found actor
   */
  public static function getByAuthorizedFormOfName($name, $options = array())
  {
    $criteria = new Criteria();
    $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
    $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, $name);

    if (isset($options['culture']))
    {
      $criteria->addAnd(QubitActorI18n::CULTURE, $options['culture']);
    }

    if (isset($options['history']))
    {
      $criteria->addAnd(QubitActorI18n::HISTORY, $options['history']);
    }

    if (isset($options['repositoryId']))
    {
      $criteria->addJoin(QubitActor::ID, QubitRelation::OBJECT_ID);
      $criteria->add(QubitRelation::TYPE_ID, QubitTerm::MAINTAINING_REPOSITORY_RELATION_ID);
      $criteria->add(QubitRelation::SUBJECT_ID, $options['repositoryId']);
    }

    return QubitActor::getOne($criteria, $options);
  }

  public function getLabel()
  {
    $label = null;
    if (null !== $this->descriptionIdentifier)
    {
      $label .= $this->descriptionIdentifier;
    }
    if (null !== $value = $this->getAuthorizedFormOfName(array('cultureFallback' => true)))
    {
      $label = (0 < strlen($label)) ? $label.' - '.$value : $value;
    }

    return $label;
  }

  public function getResourceRelations()
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitEvent::OBJECT_ID);
    $criteria->addGroupByColumn(QubitInformationObject::ID);
    $criteria->add(QubitEvent::ACTOR_ID, $this->id);

    return QubitInformationObject::get($criteria);
  }

  public function getMaintainingRepository()
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $this->id);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::MAINTAINING_REPOSITORY_RELATION_ID);

    if (null !== $relation = QubitRelation::getOne($criteria))
    {
      return $relation->subject;
    }
  }

  public function setOrDeleteMaintainingRepository($repository = null)
  {
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $this->id);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::MAINTAINING_REPOSITORY_RELATION_ID);
    $relation = QubitRelation::getOne($criteria);

    if (!isset($repository))
    {
      if (isset($relation))
      {
        $relation->delete();
      }

      return;
    }

    if (!isset($relation))
    {
      $relation = new QubitRelation;
      $relation->typeId = QubitTerm::MAINTAINING_REPOSITORY_RELATION_ID;
      $relation->subjectId = $repository->id;
      $this->relationsRelatedByobjectId[] = $relation;
    }
    else
    {
      $relation->subjectId = $repository->id;
      $relation->save();
    }
  }

  public function getOccupations()
  {
    $criteria = new Criteria;
    $this->addObjectTermRelationsRelatedByObjectIdCriteria($criteria);
    QubitObjectTermRelation::addJoinTermCriteria($criteria);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::ACTOR_OCCUPATION_ID);

    return QubitObjectTermRelation::get($criteria);
  }

  public function getSubjectAccessPoints()
  {
    return $this->getTermRelations(QubitTaxonomy::SUBJECT_ID);
  }

  public function getPlaceAccessPoints()
  {
    $criteria = new Criteria;

    $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);
    $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PLACE_ID);

    return QubitObjectTermRelation::get($criteria);
  }

  public static function setTermRelationByName($name, $options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, $options['taxonomyId']);
    $criteria->add(QubitTermI18n::NAME, $name);

    $culture = (isset($options['culture'])) ? $options['culture'] : sfContext::getInstance()->user->getCulture();
    $criteria->add(QubitTermI18n::CULTURE, $culture);

    if (null === $term = QubitTerm::getOne($criteria))
    {
      if (!QubitAcl::check(QubitTaxonomy::getById($options['taxonomyId']), 'createTerm'))
      {
        return;
      }
      $term = QubitFlatfileImport::createTerm($options['taxonomyId'], $name, $culture);
    }

    $relation = new QubitObjectTermRelation;
    $relation->term = $term;

    return $relation;
  }

  /****************
   Import methods
  *****************/
  /**
   * Wrapper for QubitDigitalObject::importFromUri() method
   *
   * @param array $uris URIs of remote files
   * @return QubitActor $this
   *
   * @TODO allow for different usage types
   */
  public function importDigitalObjectFromUri($uris, &$errors)
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
        $actor = new QubitActor;

        $digitalObject = new QubitDigitalObject;
        $digitalObject->usageId = QubitTerm::MASTER_ID;

        try
        {
          $digitalObject->importFromUri($uri);
        }
        catch (sfException $e)
        {
          $errors[] = sfContext::getInstance()->i18n->__('Encountered error fetching external resource: '.$uri);
          continue;
        }

        $actor->digitalObjectsRelatedByobjectId[] = $digitalObject;
        $actor->title = $digitalObject->name;

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

      try
      {
        $digitalObject->importFromUri($uris);
        $this->digitalObjectsRelatedByobjectId[] = $digitalObject;
      }
      catch (sfException $e)
      {
        $errors[] = sfContext::getInstance()->i18n->__('Encountered error fetching external resource: '.$uris);
      }
    }

    return $this;
  }

  /**
   * Wrapper for QubitDigitalObject::importFromBase64() method
   *
   * @param string $encodedString base-64 encoded data
   * @param string $filename name of destination file
   * @return QubitActor $this
   *
   * @TODO allow for different usage types
   */
  public function importDigitalObjectFromBase64($encodedString, $filename)
  {
    $digitalObject = new QubitDigitalObject;
    $digitalObject->usageId = QubitTerm::MASTER_ID;
    $digitalObject->importFromBase64($encodedString, $filename);

    $this->digitalObjectsRelatedByobjectId[] = $digitalObject;
  }

  /**
   * Return the absolute link to the digital object master unless the user has
   * no permission (readMaster). Text objects are always allowed for reading.
   *
   * @return string Absolute link to the digital object master
   */
  public function getDigitalObjectLink()
  {
    if (count($this->digitalObjectsRelatedByobjectId) <= 0)
    {
      return;
    }

    $digitalObject = $this->digitalObjectsRelatedByobjectId[0];
    if (!$digitalObject->masterAccessibleViaUrl())
    {
      return;
    }

    $isText = in_array($digitalObject->mediaTypeId, array(QubitTerm::TEXT_ID));

    $hasReadMaster = sfContext::getInstance()->user->isAuthenticated();
    if ($hasReadMaster || $isText)
    {
      if (QubitTerm::EXTERNAL_URI_ID == $digitalObject->usageId)
      {
        return $digitalObject->path;
      }
      else
      {
        $request = sfContext::getInstance()->getRequest();
        return $request->getUriPrefix().$request->getRelativeUrlRoot().
          $digitalObject->getFullPath();
      }
    }
  }
}
