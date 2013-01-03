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
 * Manage information objects in search index
 *
 * @package    AccesstoMemory
 * @subpackage arElasticSearchPlugin
 * @author     David Juhasz <david@artefactual.com>
 */
class arElasticSearchInformationObjectPdo
{
  public
    $ancestors,
    $doc,
    $repository,
    $sourceCulture;

  protected
    $data = array(),
    $events,
    $languages = array(),
    $scripts = array();

  protected static
    $conn,
    $lookups,
    $statements;

  public function __construct($id, $options = array())
  {
    if (isset($options['conn']))
    {
      self::$conn = $options['conn'];
    }

    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $this->loadData($id, $options);

    // Get inherited ancestors
    if (isset($options['ancestors']))
    {
      $this->ancestors = $options['ancestors'];
    }

    // Get inherited repository, unless a repository is set at current level
    if (isset($options['repository']) && !$this->__isset('repository_id'))
    {
      $this->repository = $options['repository'];
    }
  }

  public function __isset($name)
  {
    if ('events' == $name)
    {
      return isset($this->events);
    }

    return isset($this->data[$name]);
  }

  public function __get($name)
  {
    if ('events' == $name)
    {
      return $this->events;
    }

    if (isset($this->data[$name]))
    {
      return $this->data[$name];
    }
  }

  public function __set($name, $value)
  {
    if ('events' == $name)
    {
      $this->events = $value;

      return;
    }

    $this->data[$name] = $value;
  }

  protected function loadData($id, $options = array())
  {
    if (!isset(self::$statements['informationObject']))
    {
      $sql = 'SELECT
         io.*,
         slug.slug,
         pubstat.status_id as publication_status_id,
         do.id as digital_object_id,
         do.media_type_id as media_type_id,
         do.name as filename
       FROM '.QubitInformationObject::TABLE_NAME.' io
       JOIN '.QubitSlug::TABLE_NAME.' slug
         ON io.id = slug.object_id
       JOIN '.QubitStatus::TABLE_NAME.' pubstat
         ON io.id = pubstat.object_id
       LEFT JOIN '.QubitDigitalObject::TABLE_NAME.' do
         ON io.id = do.information_object_id
       WHERE io.id = :id';

      self::$statements['informationObject'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['informationObject']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['informationObject']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find information object (id: $id)");
    }

    // Load event data
    $this->loadEvents();

    return $this;
  }

  /**
   * Return an array of ancestors
   *
   * @return array of ancestors
   */
  public function getAncestors()
  {
    if (!isset($this->ancestors))
    {
      // Find ancestors
      $sql  = 'SELECT
                  node.id,
                  identifier,
                  repository_id,
                  slug';
      $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' node';
      $sql .= ' JOIN '.QubitSlug::TABLE_NAME.' slug
                  ON node.id = slug.object_id';
      $sql .= ' WHERE node.lft < ? AND node.rgt > ?';
      $sql .= ' ORDER BY lft';

      $this->ancestors = QubitPdo::fetchAll($sql, array($this->__get('lft'), $this->__get('rgt')));
    }

    if (!isset($this->ancestors) || 0 == count($this->ancestors))
    {
      throw new sfException(sprintf("%s: Couldn't find ancestors, please make sure lft and rgt values are correct", get_class($this)));
    }

    return $this->ancestors;
  }

  /**
   * Return an array of children
   *
   * @return array of children
   */
  public function getChildren()
  {
    if (!isset($this->children))
    {
      // Find children
      $sql  = 'SELECT
                  node.id';
      $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' node';
      $sql .= ' WHERE node.parent_id = :id';
      $sql .= ' ORDER BY lft';

      $this->children = QubitPdo::fetchAll($sql, array(':id' => $this->id));
    }

    return $this->children;
  }

  /**
   * Return the closest repository
   *
   * @return QubitRepository
   */
  public function getRepository()
  {
    if (!isset($this->repository))
    {
      if ($this->__isset('repository_id'))
      {
        $this->repository = QubitRepository::getById($this->__get('repository_id'));
      }
      else
      {
        foreach (array_reverse($this->ancestors) as $item)
        {
          if (isset($item->repository_id))
          {
            $this->repository = QubitRepository::getById($item->repository_id);

            break;
          }
        }
      }
    }

    return $this->repository;
  }

  public function getCollectionRoot()
  {
    if (QubitInformationObject::ROOT_ID == $this->__get('parent_id'))
    {
      return QubitInformationObject::getById($this->__get('id'));
    }
    else
    {
      return QubitInformationObject::getById($this->ancestors[1]->id);
    }
  }

  public function getLevelOfDescription($culture)
  {
    if (!isset(self::$lookups['levelOfDescription']))
    {
      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
      foreach (QubitTerm::get($criteria) as $item)
      {
        self::$lookups['levelOfDescription'][$item->id] = $item;
      }
    }

    if (isset(self::$lookups['levelOfDescription'][$this->__get('level_of_description_id')]))
    {
      return self::$lookups['levelOfDescription'][$this->__get('level_of_description_id')]->getName(array(
        'culture' => $culture,
        'fallback' => true));
    }
  }

  public function getMediaTypeName($culture)
  {
    if (!$this->__isset('media_type_id'))
    {
      return;
    }

    if (0 == count(self::$lookups['mediaType']))
    {
      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);
      foreach (QubitTerm::get($criteria) as $item)
      {
        self::$lookups['mediaType'][$item->id] = $item;
      }
    }

    if (isset(self::$lookups['mediaType'][$this->__get('media_type_id')]))
    {
      return self::$lookups['mediaType'][$this->__get('media_type_id')]->getName(array(
        'culture' => $culture,
        'fallback' => true));
    }
  }

  public function getReferenceCode()
  {
    if (null == $this->__get('identifier'))
    {
      return;
    }

    // Check if identifiers inherit from higher levels
    if ('1' == sfConfig::get('app_inherit_code_informationobject', 1))
    {
      $refcode = '';
      if (isset($this->repository))
      {
        if (null != $cc = $this->repository->getCountryCode(array('culture' => $this->__get('culture'))))
        {
          $refcode .= $cc.' ';
        }

        if (isset($this->repository->identifier))
        {
          $refcode .= $this->repository->identifier.' ';
        }
      }

      $identifiers = array();
      foreach (array_merge($this->ancestors, array($this)) as $item)
      {
        if (isset($item->identifier))
        {
          $identifiers[] = $item->identifier;
        }
      }

      $refcode .= implode(sfConfig::get('app_separator_character', '-'), $identifiers);
    }
    else
    {
      $refcode = $this->__get('identifier');
    }

    return $refcode;
  }

  protected function loadEvents()
  {
    if (!isset($this->events))
    {
      $events = array();

      if (!isset(self::$statements['event']))
      {
        $sql  = 'SELECT
                    event.id,
                    event.start_date,
                    event.end_date,
                    event.actor_id,
                    event.type_id,
                    i18n.date,
                    i18n.culture';
        $sql .= ' FROM '.QubitEvent::TABLE_NAME.' event';
        $sql .= ' JOIN '.QubitEventI18n::TABLE_NAME.' i18n
                    ON event.id = i18n.id';
        $sql .= ' WHERE event.information_object_id = ?';

        self::$statements['event'] = self::$conn->prepare($sql);
      }

      self::$statements['event']->execute(array($this->__get('id')));

      foreach (self::$statements['event']->fetchAll() as $item)
      {
        if (!isset($events[$item['id']]))
        {
          $event = new stdClass;
          $event->id = $item['id'];
          $event->start_date = $item['start_date'];
          $event->end_date = $item['end_date'];
          $event->actor_id = $item['actor_id'];
          $event->type_id = $item['type_id'];

          $events[$item['id']] = $event;
        }

        $events[$item['id']]->dates[$item['culture']] = $item['date'];
      }

      $this->events = $events;
    }

    return $this->events;
  }

  protected function getDates($field, $culture)
  {
    $dates = array();

    if (0 < count($this->events))
    {
      foreach ($this->events as $item)
      {
        switch($field)
        {
          case 'start_date':
          case 'end_date':
            if (isset($item->$field))
            {
              $date = new DateTime($item->$field);
              $dates[] = $date->format('Ymd');
            }

            break;

          case 'date':
            if (isset($item->dates[$culture]))
            {
              $dates[] = $item->dates[$culture];
            }
            else if (isset($item->start_date) || isset($item->end_date))
            {
              $dates[] = Qubit::renderDateStartEnd(null, $item->start_date, $item->end_date);
            }

            break;

          case 'array':
            if (isset($item->date) || isset($item->start_date) || isset($item->end_date))
            {
              $dates[] = array(
                'date' => isset($item->dates[$culture]) ? $item->dates[$culture] : null,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'type_id' => $item->type_id);
            }

            break;
        }
      }
    }

    return $dates;
  }

  public function getActors($culture, $options = array())
  {
    $actors = array();

    if (!isset(self::$statements['actor']))
    {
      $sql  = 'SELECT
                  actor.id,
                  slug.slug,
                  i18n.authorized_form_of_name,
                  i18n.history,
                  i18n.culture';
      $sql .= ' FROM '.QubitActor::TABLE_NAME.' actor';
      $sql .= ' JOIN '.QubitSlug::TABLE_NAME.' slug
                  ON actor.id = slug.object_id';
      $sql .= ' JOIN '.QubitActorI18n::TABLE_NAME.' i18n
                  ON actor.id = i18n.id';
      $sql .= ' WHERE actor.id = ?
                  AND i18n.culture = ?;';

      self::$statements['actor'] = self::$conn->prepare($sql);
    }

    if (0 < count($this->events))
    {
      foreach ($this->events as $item)
      {
        if (isset($item->actor_id))
        {
          // Filter by type
          if (isset($options['typeId']) && $options['typeId'] != $item->type_id)
          {
            continue;
          }

          self::$statements['actor']->execute(array(
            $item->actor_id,
            $culture));

          if ($actor = self::$statements['actor']->fetch(PDO::FETCH_OBJ))
          {
            $actors[] = $actor;
          }
        }
      }
    }

    return $actors;
  }

  public function getNameAccessPoints($culture)
  {
    $names = array();

    // Subject relations
    if (!isset(self::$statements['actorRelation']))
    {
      $sql  = 'SELECT
                  i18n.authorized_form_of_name';
      $sql .= ' FROM '.QubitActor::TABLE_NAME.' actor';
      $sql .= ' JOIN '.QubitActorI18n::TABLE_NAME.' i18n
                  ON actor.id = i18n.id';
      $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' relation
                  ON actor.id = relation.object_id';
      $sql .= ' WHERE i18n.culture = :culture
                  AND relation.subject_id = :resourceId
                  AND relation.type_id = :typeId';

      self::$statements['actorRelation'] = self::$conn->prepare($sql);
    }

    self::$statements['actorRelation']->execute(array(
      ':culture' => $culture,
      ':resourceId' => $this->__get('id'),
      ':typeId' => QubitTerm::NAME_ACCESS_POINT_ID));

    foreach (self::$statements['actorRelation']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      if (!in_array($item->authorized_form_of_name, $names))
      {
        $names[] = $item->authorized_form_of_name;
      }
    }

    // Get actors linked via the "event" table (e.g. creators)
    foreach ($this->getActors($culture) as $item)
    {
      $name = $item->authorized_form_of_name;

      if (!in_array($name, $names))
      {
        $names[] = $name;
      }
    }

    if (0 < count($names))
    {
      return implode(' ', $names);
    }
  }

  public function getSubjectAccessPoints($culture)
  {
    return $this->getRelatedTerms(QubitTaxonomy::SUBJECT_ID, $culture);
  }

  public function getPlaceAccessPoints($culture)
  {
    return $this->getRelatedTerms(QubitTaxonomy::PLACE_ID, $culture);
  }

  protected function getRelatedTerms($typeId, $culture)
  {
    $terms = array();

    if (!isset(self::$statements['relatedTerms']))
    {
      $sql  = 'SELECT
                  term.taxonomy_id,
                  i18n.name';
      $sql .= ' FROM '.QubitObjectTermRelation::TABLE_NAME.' otr';
      $sql .= ' JOIN '.QubitTerm::TABLE_NAME.' term
                  ON otr.term_id = term.id';
      $sql .= ' JOIN '.QubitTermI18n::TABLE_NAME.' i18n
                  ON term.id = i18n.id';
      $sql .= ' WHERE otr.object_id = ?
                  AND i18n.culture = ?
                  AND term.taxonomy_id = ?';

      self::$statements['relatedTerms'] = self::$conn->prepare($sql);
    }

    self::$statements['relatedTerms']->execute(array(
      $this->__get('id'),
      $culture,
      $typeId
    ));

    foreach (self::$statements['relatedTerms']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      $terms[] = $item->name;
    }

    if (0 < count($terms))
    {
      return implode(' ', $terms);
    }
  }

  protected function getLanguagesAndScripts()
  {
    // Find langs and scripts
    if (!isset(self::$statements['langAndScript']))
    {
      $sql  = 'SELECT
                  node.name,
                  i18n.value';
      $sql .= ' FROM '.QubitProperty::TABLE_NAME.' node';
      $sql .= ' JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
                  ON node.id = i18n.id';
      $sql .= ' WHERE node.source_culture = i18n.culture
                  AND node.object_id = ?
                  AND (node.name = ? OR node.name = ?)';

      self::$statements['langAndScript'] = self::$conn->prepare($sql);
    }

    self::$statements['langAndScript']->execute(array(
      $this->__get('id'),
      'language',
      'script'));

    // Add to arrays
    foreach (self::$statements['langAndScript']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      $codes = unserialize($item->value);

      if (0 < count($codes))
      {
        switch ($item->name)
        {
          case 'language':
            $this->languages = $codes;

            break;

          case 'script':
            $this->scripts = $codes;

            break;
        }
      }
    }

    return $this;
  }

  public function getNotes($culture)
  {
    $notes = array();

    // Subject relations
    if (!isset(self::$statements['note']))
    {
      $sql  = 'SELECT
                  i18n.content';
      $sql .= ' FROM '.QubitNote::TABLE_NAME.' note';
      $sql .= ' JOIN '.QubitNoteI18n::TABLE_NAME.' i18n
                  ON note.id = i18n.id';
      $sql .= ' WHERE note.object_id = ?
                  AND i18n.culture = ?';

      self::$statements['note'] = self::$conn->prepare($sql);
    }

    self::$statements['note']->execute(array(
      $this->__get('id'),
      $culture));

    foreach (self::$statements['note']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      if (0 < strlen($item->content))
      {
        $notes[] = $item->content;
      }
    }

    if (0 < count($notes))
    {
      return implode(' ', $notes);
    }
  }

  public function getThumbnailPath()
  {
    if (!$this->__isset('digital_object_id'))
    {
      return;
    }

    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('digital_object_id'));
    $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::THUMBNAIL_ID);

    if (null !== $thumbnail = QubitDigitalObject::getOne($criteria))
    {
      return $thumbnail->getFullPath();
    }
  }

  public function getMaterialTypeId()
  {
    if (!isset(self::$statements['materialType']))
    {
      $sql  = 'SELECT term.id';
      $sql .= ' FROM '.QubitObjectTermRelation::TABLE_NAME.' otr';
      $sql .= ' JOIN '.QubitTerm::TABLE_NAME.' term
                  ON otr.term_id = term.id';
      $sql .= ' WHERE object_id = ?';
      $sql .= ' AND term.taxonomy_id = '.QubitTaxonomy::MATERIAL_TYPE_ID;

      self::$statements['materialType'] = self::$conn->prepare($sql);
    }

    self::$statements['materialType']->execute(array(
      $this->__get('id')));

    return self::$statements['materialType']->fetchColumn(0);
  }

  public function getStorageNames($culture)
  {
    $names = array();

    // Subject relations
    if (!isset(self::$statements['storageName']))
    {
      $sql  = 'SELECT i18n.name';
      $sql .= ' FROM '.QubitRelation::TABLE_NAME.' rel';
      $sql .= ' JOIN '.QubitPhysicalObjectI18n::TABLE_NAME.' i18n
                  ON rel.subject_id = i18n.id';
      $sql .= ' WHERE rel.object_id = :resource_id';
      $sql .= '   AND rel.type_id = :type_id';
      $sql .= '   AND i18n.culture = :culture';

      self::$statements['storageName'] = self::$conn->prepare($sql);
    }

    self::$statements['storageName']->execute(array(
      ':resource_id' => $this->__get('id'),
      ':type_id' => QubitTerm::HAS_PHYSICAL_OBJECT_ID,
      ':culture' => $culture));

    foreach (self::$statements['storageName']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      if (0 < strlen($item->name))
      {
        $names[] = $item->name;
      }
    }

    if (0 < count($names))
    {
      return implode(' ', $names);
    }
  }

  public function getRights()
  {
    if (!isset(self::$statements['rights']))
    {
      $sql  = 'SELECT
                  restriction,
                  basis_id,
                  act_id,
                  rights_holder_id,
                  copyright_status_id';
      $sql .= ' FROM '.QubitRights::TABLE_NAME.' rights';
      $sql .= ' JOIN '.QubitRelation::TABLE_NAME.' rel
                  ON rights.id = rel.object_id';
      $sql .= ' WHERE rel.subject_id = ?';
      $sql .= ' AND rel.type_id = '.QubitTerm::RIGHT_ID;

      self::$statements['rights'] = self::$conn->prepare($sql);
    }

    self::$statements['rights']->execute(array(
      $this->__get('id')));

    return self::$statements['rights']->fetchAll(PDO::FETCH_CLASS);
  }

  /**
   * Get text transcript, if one exists
   */
  public function getTranscript()
  {
    if (!$this->__isset('digital_object_id'))
    {
      return;
    }

    if (!isset(self::$statements['transcript']))
    {
      $sql  = 'SELECT i18n.value
        FROM '.QubitProperty::TABLE_NAME.' property
        JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
          ON property.id = i18n.id
        WHERE property.name = "transcript"
          AND property.source_culture = i18n.culture
          AND property.object_id = ?';

      self::$statements['transcript'] = self::$conn->prepare($sql);
    }

    self::$statements['transcript']->execute(array($this->__get('digital_object_id')));

    return self::$statements['transcript']->fetchColumn();
  }

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['slug'] = $this->slug;

    $serialized['identifier'] = $this->identifier;
    $serialized['referenceCode'] = $this->getReferenceCode();
    $serialized['levelOfDescriptionId'] = $this->level_of_description_id;
    $serialized['publicationStatusId'] = $this->publication_status_id;
    $serialized['parentId'] = $this->ancestors[count($this->ancestors)-1]->id;

    // NB: this will include the ROOT_ID
    foreach ($this->getAncestors() as $ancestor)
    {
      $serialized['ancestors'][] = $ancestor->id;
    }

    // NB: this should be an ordered array
    foreach ($this->getChildren() as $child)
    {
      $serialized['children'][] = $child->id;
    }

    // Copyright status
    $statusId = null;
    foreach ($this->getRights() as $item)
    {
      if (isset($item->copyright_status_id))
      {
        $statusId = $item->copyright_status_id;
        break;
      }
    }
    if (null !== $statusId)
    {
      $serialized['copyrightStatusId'] = $statusId;
    }

    // Material type
    if (null !== ($materialTypeId = $this->getMaterialTypeId))
    {
      $serialized['materialTypeId'] = $materialTypeId;
    }

    if ($this->media_type_id)
    {
      $serialized['digitalObject']['mediaTypeId'] = $this->media_type_id;
      $serialized['digitalObject']['usageId'] = $this->usage_id;

      if (QubitTerm::EXTERNAL_URI_ID == $this->usage_id)
      {
        $serialized['digitalObject']['thumbnailPath'] = $this->path;
      }
      else
      {
        $serialized['digitalObject']['thumbnailPath'] = $this->getThumbnailPath();
      }

      $serialized['hasDigitalObject'] = true;
    }
    else
    {
      $serialized['hasDigitalObject'] = false;
    }

    /*
    $dates = $this->getDates('array');
    if (0 < count($dates))
    {
      $serialized['dates'] = $dates;
    }
    */

    /*
    // Repository (actor)
    if ($repository = $this->getRepository())
    {
      $repoI18ns = $repository->actorI18ns->indexBy('culture');
      $serializedI18ns = QubitMapping::serializeI18ns(new QubitActor(), $repoI18ns);

      $serialized['repository'] = array('id' => $repository->id, 'i18n' => $serializedI18ns);
    }

    // Subject access points (terms)
    foreach ($this->getSubjectAccessPoints() as $subject)
    {
      $term = QubitTerm::getById($subject->id);

      $serializedI18ns = QubitMapping::serializeI18ns(new QubitTerm(), $term->termI18ns->indexBy('culture'));
      $serialized['subjects'][] = array('id' => $subject->id, 'i18n' => $serializedI18ns);
    }

    // Place access points (terms)
    foreach ($this->getPlaceAccessPoints() as $place)
    {
      $term = QubitTerm::getById($place->id);

      $serializedI18ns = QubitMapping::serializeI18ns(new QubitTerm(), $term->termI18ns->indexBy('culture'));
      $serialized['places'][] = array('id' => $place->id, 'i18n' => $serializedI18ns);
    }

    // Name access points (actors)
    // TODO use QubitPdoActor class?
    foreach ($this->getNameAccessPoints() as $name)
    {
      $nameSerialized = $name->serialize();
      $nameSerialized['id'] = $name->id;
      unset($nameSerialized['slug']);
      unset($nameSerialized['sourceCulture']);

      $serialized['names'][] = $nameSerialized;
    }

    // Creators (actors)
    // TODO use QubitPdoActor class?
    foreach ($this->getCreators() as $creator)
    {
      $i18n = array();
      if (!empty($creator['name'])) $i18n['authorizedFormOfName'] = $creator['name'];
      if (!empty($creator['history'])) $i18n['history'] = $creator['history'];
      if (!empty($creator['culture'])) $i18n['culture'] = $creator['culture'];

      $serialized['creators'][] = array('id' => $creator['id'], 'i18n' => array($i18n));
    }

    // Notes
    foreach ($this->getNotes() as $note)
    {
      $i18n = array();
      if (!empty($note->content)) $i18n['content'] = $note->content;
      if (!empty($note->culture)) $i18n['culture'] = $note->culture;

      $serialized['notes'][] = array('id' => $note->id, 'i18n' => array($i18n));
    }

    */

    $serialized['createdAt'] = Elastica_Util::convertDate($this->created_at);
    $serialized['updatedAt'] = Elastica_Util::convertDate($this->updated_at);

    $serialized['sourceCulture'] = $this->source_culture;
    $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($this->id, array('QubitInformationObject'));

    return $serialized;
  }
}
