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
 * @author Mark Triggs, Teaspoon Consulting Pty Ltd <mark@teaspoon-consulting.com>
 */
class QubitInformationObject extends BaseInformationObject
{
  const
    ROOT_ID = 1;

  public
    // Allow per-object disabling of nested set updating during bulk imports
    $disableNestedSetUpdating = false,
    // Flag for updating search index on save
    $indexOnSave = true;

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
      case 'formatName':
      case 'formatVersion':
      case 'formatRegistryKey':
      case 'formatRegistryName':
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
          return $this->getInheritedReferenceCode();
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
      $this->slug = $this->generateSlug();
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
      $item->save($connection);
    }

    // Save updated related events (update search index after updating all
    // related objects that are included in the index document)
    foreach ($this->eventsRelatedByobjectId as $item)
    {
      $item->indexOnSave = false;

      // TODO Needed if $this is new, should be transparent
      $item->object = $this;
      $item->save($connection);
    }

    // Save new digital objects
    // TODO Allow adding additional digital objects as derivatives
    foreach ($this->digitalObjects as $item)
    {
      $item->indexOnSave = false;

      // TODO Needed if $this is new, should be transparent
      $item->informationObject = $this;
      $item->save($connection);

      break; // Save only one digital object per information object
    }

    // Save new premis objects
    foreach ($this->premisObjects as $item)
    {
      // TODO Needed if $this is new, should be transparent
      $item->informationObject = $this;
      $item->save($connection);

      break; // Save only one premis object per information object
    }

    // Save updated Status
    $hasPubStatus = false;
    foreach ($this->statuss as $item)
    {
      if (QubitTerm::STATUS_TYPE_PUBLICATION_ID == $item->typeId)
      {
        $hasPubStatus = true;
      }

      $item->indexOnSave = false;

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
      $status->indexOnSave = false;

      $status->save($connection);
    }

    if ($this->indexOnSave)
    {
      QubitSearch::getInstance()->update($this);
    }

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
    // Delete related digitalObjects
    foreach ($this->digitalObjects as $digitalObject)
    {
      // Set IO to null to avoid ES document update
      $digitalObject->informationObjectId = null;
      $digitalObject->delete();
    }

    // Delete related premisObjects
    foreach ($this->premisObjects as $premisObject)
    {
      $premisObject->delete();
    }

    // Physical object relations
    $relations = QubitRelation::getRelationsByObjectId($this->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID));
    foreach ($relations as $item)
    {
      $item->indexObjectOnDelete = false;
      $item->delete();
    }

    // Delete subject relations
    $criteria = new Criteria;
    $criteria = $this->addrelationsRelatedBysubjectIdCriteria($criteria);
    if ($subjectRelations = QubitRelation::get($criteria))
    {
      foreach ($subjectRelations as $item)
      {
        $item->indexSubjectOnDelete = false;
        $item->delete();
      }
    }

    // Delete object relations
    $criteria = new Criteria;
    $criteria = $this->addrelationsRelatedByobjectIdCriteria($criteria);
    if ($objectRelations = QubitRelation::get($criteria))
    {
      foreach ($objectRelations as $item)
      {
        $item->indexObjectOnDelete = false;
        $item->delete();
      }
    }

    // Delete any keymap entries
    $this->removeKeymapEntries();

    QubitSearch::getInstance()->delete($this);

    parent::delete($connection);
  }

  /**
   * Remove any corresponding keymap entries on delete of this object
   *
   */
  private function removeKeymapEntries()
  {
    $criteria = new Criteria;
    $criteria->add(QubitKeymap::TARGET_ID, $this->id);
    $criteria->add(QubitKeymap::TARGET_NAME, 'information_object');

    if ($objectKeymap = QubitKeymap::get($criteria))
    {
      foreach ($objectKeymap as $item)
      {
        $item->delete();
      }
    }
  }

  /**
   * Return all keymap entries associated with this object.
   *
   * @return array  Array of keymap records. Returns null if no keymap
   *                records found.
   */
  public function fetchAllKeymapEntries()
  {
    $criteria = new Criteria;
    $criteria->add(QubitKeymap::TARGET_ID, $this->id);
    $criteria->add(QubitKeymap::TARGET_NAME, 'information_object');

    if (null !== $keymaps = QubitKeymap::get($criteria))
    {
      return $keymaps;
    }
  }

  /**
   * Get number of information objects with draft status
   *
   * @return int  Number of information objects with draft status
   */
  public static function getDraftCount()
  {
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::ID, QubitInformationObject::ROOT_ID, Criteria::NOT_EQUAL);
    $criteria->addJoin(QubitInformationObject::ID, QubitStatus::OBJECT_ID);
    $criteria->add(QubitStatus::STATUS_ID, QubitTerm::PUBLICATION_STATUS_DRAFT_ID);

    return BasePeer::doCount($criteria)->fetchColumn(0);
  }

  /**
   * Get all information objects updated between two dates
   *
   * @return array  collection of QubitInformationObjects and remaining object count
   */
  public static function getUpdatedRecords($options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitStatus::OBJECT_ID);
    $criteria->add(QubitStatus::STATUS_ID, QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);
    $criteria->addJoin(QubitInformationObject::ID, QubitObject::ID);

    if (!empty($options['from']))
    {
      $criteria->add(QubitObject::UPDATED_AT, $options['from'], Criteria::GREATER_EQUAL);
    }

    if (!empty($options['until']))
    {
      $criteria->addAnd(QubitObject::UPDATED_AT, $options['until'], Criteria::LESS_EQUAL);
    }

    if (!empty($options['set']))
    {
      $options['set']->apply($criteria);
    }

    if (!empty($options['filterDrafts']))
    {
      $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
    }

    if (!empty($options['topLevel']))
    {
      $criteria->add(QubitInformationObject::PARENT_ID, QubitInformationObject::ROOT_ID);
    }

    $criteria->addAscendingOrderByColumn(QubitObject::UPDATED_AT);

    if (empty($options['offset']))
    {
      $options['offset'] = 0;
    }

    if (empty($options['limit']))
    {
      $options['limit'] = 10;
    }

    $c2 = clone $criteria;
    $count = BasePeer::doCount($c2)->fetchColumn(0);
    $remaining = $count - ($options['offset'] + $options['limit']);

    $criteria->setOffset($options['offset']);
    $criteria->setLimit($options['limit']);

    return array(
      'data'      => QubitInformationObject::get($criteria),
      'remaining' => $remaining
    );
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

  /**
   * Returns whether or not this information object would
   * inherit the specified repository from one of its ancestors.
   *
   * @param int $repositoryId  The repository to check again. If this is
   * null, this function will return false.
   *
   * @return bool  Whether or not this information object would inherit
   * a repository with the same id as $repositoryId.
   */
  public function canInheritRepository($repositoryId)
  {
    // Inheriting from no repository doesn't make sense, return false.
    if (!$repositoryId)
    {
      return false;
    }

    $inheritedRepoId = null;

    // Find out which repository we'd inherit if any.
    foreach ($this->getAncestors() as $ancestor)
    {
      if ($ancestor->id == QubitInformationObject::ROOT_ID)
      {
        continue;
      }

      if (isset($ancestor->repositoryId))
      {
        $inheritedRepoId = $ancestor->repositoryId;
        break;
      }
    }

    return $repositoryId == $inheritedRepoId;
  }

  /**
   * Export or delete EAD and DC XML.
   */
  public function updateXmlExports()
  {
    // Don't update unless caching-on-update is enabled
    if (empty(sfConfig::get('app_cache_xml_on_save')))
    {
      return;
    }

    // Create EAD XML exports if the description and/or top-level parent is published... otherwise delete any that may exist
    if ($this->getCollectionRoot()->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)
    {
      // Export top-level parent as EAD
      $params = array(
        'objectId' => $this->getCollectionRoot()->id,
        'format'   => 'ead'
      );
      QubitJob::runJob('arXmlExportSingleFileJob', $params);
    }
    else
    {
      unlink($this->pathToEadExport());
      unlink($this->pathToEadExport(true));
    }


    // Create DC XML exports if the description's published... otherwise delete any that may exist
    if ($this->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)
    {
      // Export as DC
      $params = array(
        'objectId' => $this->id,
        'format' => 'dc'
      );
      QubitJob::runJob('arXmlExportSingleFileJob', $params);
    }
    else
    {
      unlink($this->pathToDcExport());
      unlink($this->pathToDcExport(true));
    }
  }

  /**
   * Return URL to EAD XML or, if unpublished, action to generate XML.
   *
   * @return string  URL
   */
  public function urlForEadExport()
  {
    if ($this->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID && file_exists($this->pathToEadExport()))
    {
      return sfConfig::get('siteBaseUrl') .'/'. $this->pathToEadExport();
    }
    else
    {
      return sfContext::getInstance()->routing->generate(null, array($this, 'module' => 'sfEadPlugin', 'sf_format' => 'xml'));
    }
  }

  /**
   * Return file path to EAD XML, regardless of whether it's available.
   *
   * @param
   * @return string  file path of EAD XML
   */
  public function pathToEadExport($contentsOnly = false)
  {
    return QubitInformationObjectXmlCache::resourceExportFilePath($this->getCollectionRoot(), 'ead', $contentsOnly);
  }

  /**
   * Return URL to DC XML or, if unpublished, action to generate XML.
   *
   * @param
   * @return string  URL
   */
  public function urlForDcExport()
  {
    if ($this->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID && file_exists($this->pathToDcExport()))
    {
      return sfConfig::get('siteBaseUrl') .'/'. $this->pathToDcExport();
    }
    else
    {
      return sfContext::getInstance()->routing->generate(null, array($this, 'module' => 'sfDcPlugin', 'sf_format' => 'xml'));
    }
  }

  /**
   * Return file path to DC XML, regardless of whether it's available.
   *
   * @param
   * @return string  file path of DC XML
   */
  public function pathToDcExport($contentsOnly = false)
  {
    return QubitInformationObjectXmlCache::resourceExportFilePath($this, 'dc', $contentsOnly);
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
   * (not orphans). Filtering drafts when requested.
   *
   * @return array  collection of QubitInformationObjects and remaining object count
   */
  public static function getCollections($options = array())
  {
    // For a node with no children: rgt = (lft+1);
    // therefore search for nodes with: rgt > (lft+1)
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::RGT, QubitInformationObject::RGT.' > ('.QubitInformationObject::LFT.' + 1)', Criteria::CUSTOM);
    $criteria->add(QubitInformationObject::PARENT_ID, QubitInformationObject::ROOT_ID);

    if (isset($options['filterDrafts']) && $options['filterDrafts'])
    {
      $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
    }

    if (empty($options['offset']))
    {
      $options['offset'] = 0;
    }

    if (empty($options['limit']))
    {
      $options['limit'] = 10;
    }

    $c2 = clone $criteria;
    $count = BasePeer::doCount($c2)->fetchColumn(0);
    $remaining = $count - ($options['offset'] + $options['limit']);
    $remaining = ($remaining < 0) ? 0 : $remaining;

    $criteria->setOffset($options['offset']);
    $criteria->setLimit($options['limit']);

    return array(
      'data' => QubitInformationObject::get($criteria),
      'count' => $count,
      'remaining' => $remaining
    );
  }

  public function getCollectionRoot()
  {
    return $this->ancestors->andSelf()->orderBy('lft')->__get(1);
  }

  public function getFindingAidStatus()
  {
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->id);
    $criteria->add(QubitProperty::NAME, 'findingAidStatus');
    $property = QubitProperty::getOne($criteria);

    if (!isset($property))
    {
      return;
    }

    return $property->getValue(array('sourceCulture'=>true));
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

  /**
   * Returns descendants order by lft checking their visivility.
   * The childs of a non visible descendant will not be added even if they are visible
   *
   * @return array of QubitInformationObject objects.
   */
  public function getDescendantsForExport($options = array())
  {
    $descendants = array();
    $levels = isset($options['levels']) ? $options['levels'] : array();
    $numLevels = count($levels);

    foreach ($this->getChildren()->orderBy('lft') as $child)
    {
      $addCondition = true;

      // If we're not in a CLI enviroment, check ACL
      if ('cli' != sfContext::getInstance()->getConfiguration()->getEnvironment())
      {
        $addCondition = QubitAcl::check($child, 'read');
      }
      // Otherwise, if public option is set to true, check drafts directly
      if (isset($options['public']) && $options['public'])
      {
        // If $addCondition is already false, it should stay false.
        $addCondition = $addCondition && QubitTerm::PUBLICATION_STATUS_DRAFT_ID != $child->getPublicationStatus()->statusId;
      }
      // If 'levels' option is set, and $child LOD is not in $levels array, return and do not add more descendants.
      if (0 < $numLevels)
      {
        $addCondition = $addCondition && array_key_exists($child->levelOfDescriptionId, $levels);
      }

      if ($addCondition)
      {
        $descendants[] = $child;
        $descendants = array_merge($descendants, $child->getDescendantsForExport($options));
      }
    }

    return $descendants;
  }

  /* True if this information object or any descendant uses a LOD of $level. */
  public function containsLevelOfDescription($level, $culture = 'en')
  {
    $sql = "
      SELECT 1 FROM information_object
      INNER JOIN term_i18n on term_i18n.id = information_object.level_of_description_id
      WHERE information_object.lft > ? and information_object.rgt < ?
      AND term_i18n.culture = ? AND term_i18n.name = ?
      LIMIT 1
    ";

    $rows = QubitPdo::fetchAll($sql, array($this->lft, $this->rgt, $culture, $level));

    return count($rows) == 1;
  }


  /***********************
   Actor/Event relations
  ************************/

  public function getActors($options = array())
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitEvent::ACTOR_ID);
    $criteria->add(QubitEvent::OBJECT_ID, $this->id);

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
    $criteria->add(QubitEvent::OBJECT_ID, $this->id);
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
    $criteria->add(QubitEvent::OBJECT_ID, $this->id);
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
    $criteria->add(QubitEvent::OBJECT_ID, $this->id);

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

    $criteria->add(QubitEvent::OBJECT_ID, $this->id);
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

  public function getGenreAccessPoints()
  {
    return $this->getTermRelations(QubitTaxonomy::GENRE_ID);
  }

  public function getPlaceAccessPoints(array $options = array('events' => false))
  {
    $criteria = new Criteria;

    // Places are either associated with the information object directly
    // (for standard authorities) or with events (for places added in
    // the events module).
    if ($options['events'])
    {
      $criteria->addJoin(QubitObjectTermRelation::OBJECT_ID, QubitEvent::ID);
      $criteria->add(QubitEvent::OBJECT_ID, $this->id);
    }
    else
    {
      $criteria->add(QubitObjectTermRelation::OBJECT_ID, $this->id);
    }

    $criteria->addJoin(QubitObjectTermRelation::TERM_ID, QubitTerm::ID);
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::PLACE_ID);

    return QubitObjectTermRelation::get($criteria);
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

  /**
   * Get the digital object's checksum value
   *
   * @return string  digital object checksum or null
   */
  public function getDigitalObjectChecksum()
  {
    if (null !== $do = $this->getDigitalObject())
    {
      return $do->getChecksum();
    }
  }

  /**
   * Get the total digital object count for this & all descendents to this
   * information object.
   *
   * @return int  The total digital object count.
   */
  public function getDescendentDigitalObjectCount()
  {
    $sql = '
      SELECT COUNT(d.id) FROM information_object i
      INNER JOIN digital_object d ON i.id=d.information_object_id
      WHERE i.lft > ? and i.rgt < ?
    ';

    $params = array($this->lft, $this->rgt);

    return QubitPdo::fetchColumn($sql, $params);
  }

  /**
   * Get the digital object's public URL
   *
   * @return string  digital object URL or null
   */
  public function getDigitalObjectPublicUrl()
  {
    // Set digital object URL
    $do = $this->digitalObjects[0];

    if (isset($do))
    {
      $path = $do->getFullPath();

      // if path is external, it's absolute so return it
      if (QubitTerm::EXTERNAL_URI_ID == $do->usageId)
      {
        return $path;
      }
      else if (QubitTerm::OFFLINE_ID === $do->usageId)
      {
        throw new sfException('getDigitalObjectPublicUrl() is not available for offline digital objects');
      }
      else
      {
        if (!QubitAcl::check($this, 'readMaster') && null !== $do->reference &&
            QubitAcl::check($this, 'readReference'))
        {
          $path = $do->reference->getFullPath();
        }

        return rtrim(QubitSetting::getByName('siteBaseUrl'), '/').'/'.ltrim($path, '/');
      }
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
        $infoObject = new QubitInformationObject;

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

      try
      {
        $digitalObject->importFromUri($uris);
        $this->digitalObjects[] = $digitalObject;
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

  /**
   * Returns an actor if one exists with the specified name and
   * is related to this information object (either as a subject
   * or an object)
   *
   * @param $name  The actor name
   * @param $relatedBy  The relation type, either 'object' or 'subject'
   * @return QubitActor matching the specified parameters, null otherwise
   */
  private function getActorByNameAndRelation($name, $relatedBy = 'object')
  {
    // We could also maybe use $this->$varmagic here but
    // I figure just a simple if/else was more readable.
    if ($relatedBy === 'object')
    {
      $relations = $this->relationsRelatedByobjectId;
    }
    else
    {
      $relations = $this->relationsRelatedBysubjectId;
    }

    foreach ($relations as $relation)
    {
      if ($relation->$relatedBy instanceof QubitActor)
      {
        foreach ($relation->$relatedBy->actorI18ns as $actorI18n)
        {
          if (isset($actorI18n->authorizedFormOfName) &&
            $name == $actorI18n->authorizedFormOfName)
          {
            return $relation->$relatedBy;
          }
        }
      }
    }

    return null;
  }

  /**
   * Returns an actor if one exists with the specified name and
   * who is also part of an event related to this information object.
   *
   * @param $name  The actor name
   * @return QubitActor matching the specified parameters, null otherwise
   */
  private function getActorByNameAndEvent($name)
  {
    foreach ($this->eventsRelatedByobjectId as $event)
    {
      if (isset($event->actor))
      {
        foreach ($event->actor->actorI18ns as $actorI18n)
        {
          if (isset($actorI18n->authorizedFormOfName) &&
            $name == $actorI18n->authorizedFormOfName)
          {
            return $event->actor;
          }
        }
      }
    }

    return null;
  }

  /**
   * This method will add an existing actor related to this information object to events or
   * name access points. Or, if no actor exists as such, create a new one to add to events
   * or name access point.
   *
   * First it will try to find an existing actor associated with this information object
   * that matches the specified name. If there are no actors with that name associated with
   * this information object, we will create a new actor with said name.
   *
   * To find existing actors associated with the information object, we check:
   * 1. Actors associated with this information object by an existing event
   * 2. Actors associated with this information object by relation (either subject or object)
   * 3. Actors that have the same name
   *
   * @param $name  The name of the actor
   * @param $options  An array of options filling in the new event or name access point info.
   *
   * @return QubitActor  The new or existing actor just added to the event/name access point list.
   */
  public function setActorByName($name, $options)
  {
    // Only create and link actor if the event or relation type is indicated
    if (!isset($options['event_type_id']) && !isset($options['relation_type_id']))
    {
      return;
    }

    // Check if the actor is already related in the description events
    // Store if it's related to avoid add it as a name access point
    $actor = $this->getActorByNameAndEvent($name);
    $existingEventRelation = !is_null($actor);

    // Check relations related by subject
    if (!$actor)
    {
      $actor = $this->getActorByNameAndRelation($name, 'subject');
    }

    // Check relations related by object
    if (!$actor)
    {
      $actor = $this->getActorByNameAndRelation($name, 'object');
    }

    // Lastly, check just if there are any other actors with this exact name
    if (!$actor)
    {
      $actor = QubitActor::getByAuthorizedFormOfName($name);
    }

    // When the history option is populated and we
    // already have a match with a different history
    if ($actor && !empty($options['history']) && $actor->history !== $options['history'])
    {
      // Try to get a full match in name and history or create a new one.
      // Only the delete and replace option from IO import could reach
      // this point so there is no need to check by maintaining repository
      // and update the actor history.
      $actor = QubitActor::getByAuthorizedFormOfName($name, array('history' => $options['history']));
    }

    // If there isn't a match create a new actor
    if (!$actor)
    {
      $actor = new QubitActor;
      $actor->parentId = QubitActor::ROOT_ID;
      $actor->setAuthorizedFormOfName($name);

      if (isset($options['entity_type_id']))
      {
        $actor->setEntityTypeId($options['entity_type_id']);
      }
      if (isset($options['source']))
      {
        $actor->setSources($options['source']);
      }
      if (isset($options['rules']))
      {
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

    // Create event or relation to link the information object and actor
    if (isset($options['event_type_id']))
    {
      $event = new QubitEvent;
      $event->setActor($actor);
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

      $this->eventsRelatedByobjectId[] = $event;
    }
    // In EAD import, the term relation is not always created at this point;
    // it might be created afterwards.
    else if (isset($options['relation_type_id']) &&
      isset($options['createRelation']) &&
      false !== $options['createRelation'])
    {
      // Only add actor as name access point if they are not already linked to
      // an event (i.e. they are not already a "creator", "accumulator", etc.)
      if (!$existingEventRelation)
      {
        $relation = new QubitRelation;
        $relation->object = $actor;
        $relation->typeId = QubitTerm::NAME_ACCESS_POINT_ID;

        $this->relationsRelatedBysubjectId[] = $relation;
      }
    }

    return $actor;
  }

  /**
   * Import data from a <originalsloc> tag in EAD2002
   *
   * As the locations of originals field is unstructured, EAD content must
   * be amalgamated as text. Note that when EAD exported from AtoM the content
   * is exported in a p tag within the originalsloc tag.
   *
   * @param $originalsNode  DOMNode  EAD originalsloc DOM node
   */
  public function importOriginalsLocationEadData($originalsNode)
  {
    $text = '';

    // Add <p> tag content to amalgamated value
    foreach ($originalsNode->getElementsByTagName('p') as $pNode)
    {
      $text .= trim($pNode->textContent) ."\n";
    }

    // Add <addressline> tag content to amalgamated value
    foreach ($originalsNode->getElementsByTagName('address') as $addressNode)
    {
      foreach ($addressNode->getElementsByTagName('addressline') as $addressLineNode)
      {
        $text .= trim($addressLineNode->textContent) ."\n";
      }
    }

    $this->setLocationOfOriginals(trim($text));
  }

  /**
   * Import language-related data from a <langusage> tag in EAD2002
   *
   * @param $langusageNode  DOMNode  EAD langusage DOM node
   */
  public function importLangusageEadData($langusageNode)
  {
    $languagesOfDescription = array();
    $scriptsOfDescription = array();

    $langCodeConvertor = new fbISO639_Map;

    // get language nodes
    if (0 < count($langNodeList = QubitXmlImport::queryDomNode($langusageNode, "/xml/langusage/language/@langcode")))
    {
      // set first language as source culture
      if ($langCode = $langCodeConvertor->getID1($langNodeList->item(0)->nodeValue, false))
      {
        $this->setLangcode(strtolower($langCode));
      }
      else
      {
        $this->setLangcode(strtolower($langNodeList->item(0)->nodeValue));
      }

      // get all as language(s) of description
      foreach($langNodeList as $langNode)
      {
        if ($langCode = $langCodeConvertor->getID1($langNode->nodeValue, false))
        {
          array_push($languagesOfDescription, strtolower($langCode));
        }
        else
        {
          array_push($languagesOfDescription, strtolower($langNode->nodeValue));
        }
      }
    }

    // get script nodes
    $scriptNodeList = QubitXmlImport::queryDomNode($langusageNode, "/xml/langusage/language/@scriptcode");

    // get scripts (s) of description
    foreach($scriptNodeList as $scriptNode)
    {
      array_push($scriptsOfDescription, $scriptNode->nodeValue);
    }

    // add language(s) of description, if any
    if (count($languagesOfDescription))
    {
      $this->addProperty(
        'languageOfDescription',
        serialize($languagesOfDescription)
      );
    }

    // add script(s) of description, if any
    if (count($scriptsOfDescription))
    {
      $this->addProperty(
        'scriptOfDescription',
        serialize($scriptsOfDescription)
      );
    }
  }

/**
   * Import language-related data from a <langmaterial> tag in EAD2002
   *
   * @param $langmaterialNode  DOMNode  EAD langmaterial DOM node
   */
  public function importLangmaterialEadData($langmaterialNode)
  {
    // get language and script nodes
    $langNodeList = QubitXmlImport::queryDomNode($langmaterialNode, "/xml/langmaterial/language");

    $langCodeConvertor = new fbISO639_Map;

    $languages = array();
    $scripts = array();

    // amalgamate language and script data
    foreach($langNodeList as $langNode)
    {
      if ($langNode->hasAttributes())
      {
        if (0 < strlen($lang = $langNode->getAttribute('langcode')))
        {
          if ($langCode = $langCodeConvertor->getID1($lang, false))
          {
            array_push($languages, strtolower($langCode));
          }
          else
          {
            array_push($languages, strtolower($lang));
          }
        }

        if (0 < strlen($scriptCode = $langNode->getAttribute('scriptcode')))
        {
          array_push($scripts, $scriptCode);
        }
      }
    }

    // add language(s), if any
    if (count($languages))
    {
      $this->addProperty('language', serialize($languages));
    }

    // add script(s), if any
    if (count($scripts))
    {
      $this->addProperty('script', serialize($scripts));
    }

    // get language and script note
    $noteContent = '';
    foreach($langmaterialNode->childNodes as $child)
    {
      if ($child->nodeType == XML_TEXT_NODE)
      {
        $noteContent .= trim($child->textContent);
      }
      else if ($child->nodeName == 'lb')
      {
        $noteContent .= "\n";
      }
    }

    // add language and script note, if so
    if (0 < strlen($noteContent))
    {
      $newNote = new QubitNote;
      $newNote->setScope('QubitInformationObject');
      $newNote->setContent(trim($noteContent));
      $newNote->setTypeId(QubitTerm::LANGUAGE_NOTE_ID);

      $this->notes[] = $newNote;
    }
  }

  public function importDCLanguage($code)
  {
    $langCodeConvertor = new fbISO639_Map;

    if ($langCodeConvertor->getID1($code, true))
    {
      $code = strtolower($langCode);
    }

    $this->language[] = $code;
  }

  /**
   * Import creator data from an <origination> tag in EAD2002
   *
   * @param $node  DOMNode  EAD origination DOM node
   */
  public function importOriginationEadData($node)
  {
    $imported = array();
    $entityTypes = array(
      'persname' => QubitTerm::PERSON_ID,
      'corpname' => QubitTerm::CORPORATE_BODY_ID,
      'famname'  => QubitTerm::FAMILY_ID,
      'name'     => null
    );

    foreach ($entityTypes as $type => $typeId)
    {
      $nameNodes = $node->getElementsByTagName($type);

      if ($nameNodes->length)
      {
        foreach ($nameNodes as $node)
        {
          $actor = $this->setActorByName(
            $node->nodeValue,
            array('entity_type_id' => $typeId, 'event_type_id' => QubitTerm::CREATION_ID)
          );

          $imported[] = array('node' => $node, 'actor' => $actor);
        }
      }
    }

    return $imported;
  }

  /**
   * Import creation-related data from an <bioghist> tag in EAD2002
   *
   * @param $biogHistNode  DOMNode  EAD bioghist DOM node
   * @param $key Position of the current bioghist node
   */
  public function importBioghistEadData($biogHistNode, $key)
  {
    // Support legacy versions of AtoM that employed chronlist tags inside bioghist
    $chronNodeList = $biogHistNode->getElementsByTagName('chronlist');
    if ($chronNodeList->length)
    {
      $this->parseChronlist($chronNodeList);
      return;
    }

    // Obtain creators (we can't use criteria because they're not saved yet)
    $creators = array();
    foreach ($this->eventsRelatedByobjectId as $existingEvent)
    {
      if ($existingEvent->typeId == QubitTerm::CREATION_ID && isset($existingEvent->actor))
      {
        $creators[] = $existingEvent->actor;
      }
    }

    // Add bioghist
    if (strlen($history = QubitXmlImport::normalizeNodeValue($biogHistNode)) > 0)
    {
      // Options:
      // 1. If there isn't an actor in the current position:
      //   - Create new 'Untitled' actor with bioghist value as history and new event
      // 2. If the actor in the current position doesn't have history:
      //   - Add bioghist value to the actor's history
      // 3. If the actor in the current position has history and it's different to the bioghist value:
      //   - Create new 'Untitled' actor with bioghist value as history and new event
      // 4. If the actor in the current position has history and it's equal to the bioghist value:
      //   - Do nothing
      if (!isset($creators[$key]) ||
        (isset($creators[$key]->history) && $creators[$key]->history !== $history))
      {
        $actor = new QubitActor;
        $actor->parentId = QubitActor::ROOT_ID;
        $actor->setHistory($history);
        $actor->save();

        $event = new QubitEvent;
        $event->setActorId($actor->id);
        $event->setTypeId(QubitTerm::CREATION_ID);

        $this->eventsRelatedByobjectId[] = $event;
      }
      else if (!isset($creators[$key]->history))
      {
        $creators[$key]->history = $history;
        $creators[$key]->save();
      }
    }
  }

  /**
   * Parse chronlist tags from within a bioghist tag.
   *
   * @param DOMNodeList $chronlistNodeList  A node list containing the chronlist tag and its children.
   */
  private function parseChronlist($chronlistNodeList)
  {
    foreach ($chronlistNodeList as $chronlistNode)
    {
      // Get chronitem elements in chronlist element
      $chronitemNodeList = $chronlistNode->getElementsByTagName('chronitem');
      foreach ($chronitemNodeList as $chronitemNode)
      {
        // Get creation date element contents
        $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/date[@type='creation']");
        foreach ($dateNodeList as $dateNode)
        {
          $date = $dateNode->nodeValue;
        }

        // Get creation start and end date from "normal" attribute
        $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/date[@type='creation']/@normal");
        foreach ($dateNodeList as $dateNormalAttr)
        {
          $normalizedDates = sfEadPlugin::parseEadDenormalizedDateData($dateNormalAttr->value);

          $date_start = $normalizedDates['start'];

          if ($normalizedDates['end'])
          {
            $date_end = $normalizedDates['end'];
          }
        }

        // Get dates of existence element contents
        $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/eventgrp/event/date[@type='existence']");
        foreach ($dateNodeList as $dateNode)
        {
          $datesValue = $dateNode->nodeValue;
        }

        // Get creation end date element contents
        $history = '';
        $dateNodeList = QubitXmlImport::queryDomNode($chronitemNode, '/xml/chronitem/eventgrp/event/note[not(@type="eventNote")]/p');
        foreach ($dateNodeList as $noteNode)
        {
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

        foreach ($possibleNameFields as $fieldName => $fieldTypeId)
        {
          $fieldValue = '';
          $nameNodeList = QubitXmlImport::queryDomNode($chronitemNode, "/xml/chronitem/eventgrp/event/origination/". $fieldName);
          foreach ($nameNodeList as $nameNode)
          {
            $fieldValue = $nameNode->nodeValue;
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
        foreach ($eventNoteList as $eventNoteNode)
        {
          $eventNote = $eventNoteNode->nodeValue;
        }

        $eventSpec = array(
          'event_type_id' => QubitTerm::CREATION_ID,
          'entity_type_id' => $typeId,
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

  /**
   * Import actor history from on <bioghist> tag in EAD2002
   *
   * @param $history string actor history
   */
  public function setHistoryByOrigination($history)
  {
    // Check events array for related events/actors (we may not have saved this
    // data to the database yet)
    if (0 < count($relatedEvents = $this->eventsRelatedByobjectId))
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
      $criteria->add(QubitTermI18n::CULTURE, sfContext::getInstance()->user->getCulture());
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

  /**
   * Returns a date string YYYY-MM-DD when given a date from an EAD <unitdate> @normal attribute
   *
   * @param $date  A date string from an EAD file, e.g. 19601103
   * @return Will return a MySQL friendly YYYY-MM-DD date string (uses '-0' if missing a field)
   */
  private function getNormalizedDate($date)
  {
    if (strpos($date, '-') !== false)
    {
      return $date; // Already in YYYY-MM-DD format (hopefully)
    }

    // Check to see if date is proper length, either 4 for year only,
    // 6 for year & month, or 8 for year & month & day
    if (!in_array(strlen($date), array(4, 6, 8)))
    {
      return null;
    }

    $year = $month = $day = 0;

    switch (true)
    {
      case strlen($date) >= 4: $year  = (int)substr($date, 0, 4);
      case strlen($date) >= 6: $month = (int)substr($date, 4, 2);
      case strlen($date) == 8: $day   = (int)substr($date, 6, 2);
    }

    if ($year === 0)
    {
      return null; // Garbage date
    }

    return "$year-$month-$day";
  }

  public function setDates($date, $options = array())
  {
    $normalizedDate = array('start' => null, 'end' => null);

    if (isset($options['normalized_dates']))
    {
      $dates = explode('/', $options['normalized_dates']);
      $normalizedDate['start'] = $this->getNormalizedDate($dates[0]);
      $normalizedDate['end'] = (count($dates) > 1) ? $this->getNormalizedDate($dates[1]) : null;
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

    $this->eventsRelatedByobjectId[] = $event;

    return $event;
  }

  protected function getDefaultDateValue($date)
  {
    $dateArray = explode("-", $date);

    $defaultDateValue = str_pad($dateArray[0], 4, "0", STR_PAD_LEFT);

    if (isset($dateArray[1]))
    {
      $defaultDateValue .= '-'.str_pad($dateArray[1], 2, '0', STR_PAD_LEFT);
    }

    if (isset($dateArray[2]))
    {
      $defaultDateValue .= '-'.str_pad($dateArray[2], 2, '0', STR_PAD_LEFT);
    }

    return $defaultDateValue;
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

    // In EAD import, the term relation is not always created at this point;
    // it might be created afterwards.
    if (isset($options['createRelation']) && false !== $options['createRelation'])
    {
      $this->addTermRelation($term->id);
    }

    return $term;
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

  public function importPhysicalObject($location, $name, $options = array())
  {
    if (!empty($options['label']) && !empty($options['type']))
    {
      $fullType = ucfirst($options['label']).' '.$options['type'];
    }
    else if (!empty($options['type']))
    {
      $fullType = ucfirst($options['type']);
    }
    else if (!empty($options['label']))
    {
      $fullType = ucfirst($options['label']);
    }

    $name = trim($name);
    $location = trim($location);

    // If a type has been provided, look it up
    $term = ($fullType)
      ? QubitFlatfileImport::createOrFetchTerm(
          QubitTaxonomy::PHYSICAL_OBJECT_TYPE_ID,
          $fullType
        )
      : false;

    // Check for an existing physical object within this collection with the same name
    if (isset($options['collectionId']))
    {
      $object = QubitPhysicalObject::checkPhysicalObjectExistsInCollection(
        $name,
        $location,
        ($term) ? $term->id : null,
        $options['collectionId']
      );
    }

    // There was no existing physical object to attach, create a new one
    if (!isset($object))
    {
      $object = new QubitPhysicalObject();
      $object->name = $name;

      if ($location)
      {
        $object->location = $location;
      }

      if ($term)
      {
        $object->typeId = $term->id;
      }

      $object->save();
    }

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

  /**
   * Try to match informationObject to an existing one in system.
   *
   * @param string $identifier  informationObject identifier
   * @param string $title       informationObject title
   * @param string $repoName    repository authorizedFormOfName
   *
   * @return integer InfoObj id
   */
  public static function getByTitleIdentifierAndRepo ($identifier, $title, $repoName)
  {
    if (null !== $identifier && null !== $title)
    {
      $sf_user = sfContext::getInstance()->user;
      $currentCulture = $sf_user->getCulture();

      $queryBool = new \Elastica\Query\BoolQuery;

      // Use match query for exact matches.
      $queryText = new \Elastica\Query\Match;
      $queryBool->addMust($queryText->setFieldQuery('identifier', $identifier));

      $queryText = new \Elastica\Query\Match;
      $queryBool->addMust($queryText->setFieldQuery(sprintf('i18n.%s.title.untouched', $currentCulture), $title));

      if (null !== $repoName)
      {
        $queryText = new \Elastica\Query\Match;
        $queryBool->addMust($queryText->setFieldQuery(sprintf('repository.i18n.%s.authorizedFormOfName.untouched', $currentCulture), $repoName));
      }

      $query = new \Elastica\Query($queryBool);
      $query->setSize(1);
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

      if ($resultSet->count())
      {
        return $resultSet[0]->getId();
      }
    }
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

  public function getTreeViewChildren(array $options = array(), &$childrenRemaining = null)
  {
    $numberOfPreviousOrNextSiblings = 4;
    if (isset($options['numberOfPreviousOrNextSiblings']))
    {
      $numberOfPreviousOrNextSiblings = $options['numberOfPreviousOrNextSiblings'];
    }

    // Find first child visible
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::PARENT_ID, $this->id);

    // If not authenticated, restrict access to published descriptions
    if (!sfContext::getInstance()->user->isAuthenticated())
    {
      $criteria->addJoin(QubitInformationObject::ID, QubitStatus::OBJECT_ID);
      $criteria->add(QubitStatus::STATUS_ID, QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);
    }

    $criteria = QubitInformationObject::addTreeViewSortCriteria($criteria);

    $firstChild = QubitInformationObject::getOne($criteria);

    $items = array();
    if (isset($firstChild))
    {
      $firstChildSiblings = $firstChild->getTreeViewSiblings(
        array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'next'),
        $childrenRemaining
      );

      // Merge the first child found and its potential siblings
      $items = array_merge(array($firstChild), $firstChildSiblings);

      $hasNextSiblings = count($items) > $numberOfPreviousOrNextSiblings + 1;
      if ($hasNextSiblings)
      {
        array_pop($items);
      }
    }

    return array($items, $hasNextSiblings);
  }

  public function getTreeViewSiblings(array $options = array(), &$siblingsRemaining = null)
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

          $concatCurrent  = $current->__get('identifier') ? str_pad($current->__get('identifier'), 12, '0', STR_PAD_RIGHT) : " ";
          $concatCurrent .= $current->getTitle(array('sourceCulture' => true));
          $concatCurrent .= str_pad($current->lft, 12, '0', STR_PAD_LEFT);
          $concatCurrent = Propel::getConnection()->quote($concatCurrent);

          if ('next' == $position)
          {
            $criteria->add('title',
              'CONVERT(CONCAT(
                  RPAD(COALESCE(identifier, " "), 12, 0),
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
                RPAD(COALESCE(identifier, " "), 12, 0),
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

          $concatCurrent = $current->getTitle(array('sourceCulture' => true));
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

      // If not authenticated, restrict access to published descriptions
      if (!sfContext::getInstance()->user->isAuthenticated())
      {
        $criteria->addJoin(QubitInformationObject::ID, QubitStatus::OBJECT_ID);
        $criteria->add(QubitStatus::STATUS_ID, QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID);
      }

      // This is the number of items we were asked for.
      $rows = QubitInformationObject::get($criteria);

      // Take note of row count before we do a limited version of the query
      $rowsCount = $rows->count();

      // Perform limited version of query
      $criteria->setLimit($limit);
      $rows = QubitInformationObject::get($criteria);

      if ($siblingsRemaining !== null)
      {
        $siblingsRemaining = $rowsCount - $limit + 1;
      }

      // Iterate over results and store them in the $results array
      foreach ($rows as $item)
      {
        // Avoid to add the same element, this may happen when sorting by title
        // or identifierTitle for unknown reasons
        if ($item->id == $this->id)
        {
          continue;
        }

        // We will need this later to control the loop
        $last = $item;

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
  public static function getStandardsBasedInstance($resource)
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

  /**
   * Return the extentAndMedium field without XML tags for the index pages.
   * strip_tags() is not working in the templates after the escaping changes
   *
   * @return string Cleaned extentAndMedium field
   */
  public function getCleanExtentAndMedium($options = array())
  {
    return strip_tags($this->getExtentAndMedium($options));
  }

  /**
   * Return the absolute link to the digital object master unless the user has
   * no permission (readMaster). Text objects are always allowed for reading.
   *
   * @return string Absolute link to the digital object master
   */
  public function getDigitalObjectLink()
  {
    if (count($this->digitalObjects) <= 0)
    {
      return;
    }

    $digitalObject = $this->digitalObjects[0];
    if (QubitTerm::OFFLINE_ID === $digitalObject->usageId)
    {
      throw new sfException('getDigitalObjectLink() is not available for offline digital objects');
    }

    $isText = in_array($digitalObject->mediaTypeId, array(QubitTerm::TEXT_ID));
    $hasReadMaster = QubitAcl::check($this, 'readMaster');

    if (QubitGrantedRight::checkPremis($this->id, 'readMaster') && ($hasReadMaster || $isText))
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

  /*
   * Generate a slug for this information object. This might be based
   * on title, identifier (reference code), or other properties in the future.
   *
   * @return string  The generated slug.
   */
  private function generateSlug()
  {
    if (null === $slugBasis = QubitSetting::getByName('slug_basis_informationobject'))
    {
      $slugBasis = QubitSlug::SLUG_BASIS_TITLE; // Fall back to title as slug basis
    }
    else
    {
      $slugBasis = $slugBasis->getValue();
    }

    $stringToSlugify = null;

    switch ($slugBasis)
    {
      case QubitSlug::SLUG_BASIS_REFERENCE_CODE:
        $stringToSlugify = $this->getInheritedReferenceCode();
        break;

      case QubitSlug::SLUG_BASIS_TITLE:
        $stringToSlugify = $this->getTitle(array('sourceCulture' => true));
        break;

      case QubitSlug::SLUG_BASIS_REFERENCE_CODE_NO_COUNTRY_REPO:
        $stringToSlugify = $this->getInheritedReferenceCode(false);
        break;

      case QubitSlug::SLUG_BASIS_IDENTIFIER:
        $stringToSlugify = $this->identifier;
        break;

      default:
        throw new sfException('Unsupported slug basis specified in settings: '.$slugBasis);
    }

    // Blank string or null returned, attempt to fall back to slug based on title
    if ($slugBasis != QubitSlug::SLUG_BASIS_TITLE && !$stringToSlugify)
    {
      $stringToSlugify = $this->getTitle(array('sourceCulture' => true));
    }

    // If we still have a blank or null value here, QubitObject will eventually create a random
    // slug for us. See QubitObject::insertSlug().
    return QubitSlug::slugify($stringToSlugify);
  }

  /**
   * Return this information object's full, inherited reference code.
   *
   * @param bool $includeRepoAndCountry  Whether to include the repository identifier and country
   * code.
   *
   * @return string
   */
  public function getInheritedReferenceCode($includeRepoAndCountry = true)
  {
    if (!isset($this->identifier))
    {
      return;
    }

    $identifier = array();
    $repository = null;

    $item = $this;

    // Ascend the hierarchy to build the inherited identifier manually,
    // as this method may be called before saving and getAncestors() can work.
    while ($item && $item->id != QubitInformationObject::ROOT_ID)
    {
      if (isset($item->identifier))
      {
        array_unshift($identifier, $item->identifier);
      }

      if (isset($item->repository))
      {
        $repository = $item->repository;
      }

      $item = $item->parent;
    }

    $identifier = implode(sfConfig::get('app_separator_character', '-'), $identifier);

    if ($includeRepoAndCountry)
    {
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
    }

    return $identifier;
  }

  /**
   * Delete this information object as well as all children information objects.
   *
   * @return int  Number of QubitInformationObjects deleted.
   */
  public function deleteFullHierarchy()
  {
    $n = 0;
    foreach ($this->descendants->andSelf()->orderBy('rgt') as $item)
    {
      $item->delete();
      $n++;
    }

    return $n;
  }

  /**
   * Get current identifier counter for identifier mask from database.
   *
   * @return QubitSetting  The identifier counter setting (use ->value to get value).
   */
  public static function getIdentifierCounter()
  {
    if (null === $counter = QubitSetting::getByName('identifier_counter'))
    {
      throw new sfException('identifier_counter setting not found--is your database upgraded?');
    }

    return $counter;
  }

  /**
   * Generate identifier based on identifier mask and current counter.
   *
   * @return string  The generated identifier.
   */
  public static function generateIdentiferFromMask()
  {
    $counter = self::getIdentifierCounter();

    return preg_replace_callback('/([#%])([A-z]+)/', function($match) use ($counter)
    {
      if ('%' == $match[1])
      {
        return strftime('%'.$match[2]);
      }
      else if ('#' == $match[1])
      {
        if (0 < preg_match('/^i+$/', $match[2], $matches))
        {
          return str_pad($counter->value, strlen($matches[0]), 0, STR_PAD_LEFT);
        }

        return $match[2];
      }
    }, sfConfig::get('app_identifier_mask', ''));
  }
}
