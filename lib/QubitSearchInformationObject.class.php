<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Manage information objects in search index
 *
 * @package    Qubit
 * @subpackage QubitSearch
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitSearchInformationObject
{
  public
    $ancestors,
    $index,
    $doc,
    $repository,
    $sourceCulture;

  protected
    $data = array(),
    $languages,
    $scripts;

  protected static
    $conn,
    $lookups,
    $statements,
    $fields = array(
      'id',
      'access_conditions',
      'accruals',
      'acquisition',
      'creator_history',
      'alternate_title',
      'appraisal',
      'archival_history',
      'arrangement',
      'creator',
      'creator_serialized',
      'date',
      'date_serialized',
      'edition',
      'end_date',
      'extent_and_medium',
      'class_name',
      'collection_root_slug',
      'copyright_status_id',
      'culture',
      'filename',
      'finding_aids',
      'has_digital_object',
      'identifier',
      'language',
      'level_of_description',
      'level_of_description_id',
      'location_of_copies',
      'location_of_originals',
      'material_type_id',
      'media_type',
      'media_type_id',
      'name',
      'notes',
      'parent',
      'part_of',
      'physical_characteristics',
      'physical_storage',
      'place',
      'publication_status_id',
      'reference_code',
      'related_units_of_description',
      'repository',
      'repository_id',
      'repository_slug',
      'reproduction_conditions',
      'script',
      'scope_and_content',
      'slug',
      'sources',
      'start_date',
      'subject',
      'thumbnail_path',
      'title',
      'transcript'
    );

  /**
   * METHODS
   */
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
      $resource->repository = $options['repository'];
    }

    $this->index = QubitSearch::getInstance()->getEngine()->getIndex();
  }

  private function func_get_culture($args)
  {
    if (1 < count($args))
    {
      $culture = $args[1];
    }
    else
    {
      $culture = $this->sourceCulture;
    }

    return $culture;
  }

  public function __isset($name)
  {
    $culture = $this->func_get_culture(func_get_args());

    if ('events' == $name)
    {
      return isset($this->data['events']);
    }

    return isset($this->data[$culture][$name]);
  }

  public function __get($name)
  {
    $culture = $this->func_get_culture(func_get_args());

    if ('events' == $name)
    {
      return $this->data['events'];
    }

    if (isset($this->data[$culture][$name]))
    {
      return $this->data[$culture][$name];
    }
  }

  public function __set($name, $value)
  {
    $culture = $this->func_get_culture(func_get_args());

    $this->data[$culture][$name] = $value;
  }

  protected function loadData($id, $options = array())
  {
    if (!isset(self::$statements['informationObject']))
    {
      $sql = 'SELECT
         io.*,
         i18n.*,
         slug.slug,
         pubstat.status_id as publication_status_id,
         do.id as digital_object_id,
         do.media_type_id as media_type_id,
         do.name as filename
       FROM '.QubitInformationObject::TABLE_NAME.' io
       JOIN '.QubitInformationObjectI18n::TABLE_NAME.' i18n
         ON io.id = i18n.id
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

    // Get all rows (one per culture)
    $results = self::$statements['informationObject']->fetchAll(PDO::FETCH_ASSOC);

    if (false === $results)
    {
      throw new sfException("Couldn't find information object (id: $id)");
    }

    // Key by culture
    foreach ($results as $item)
    {
      $this->data[$item['culture']] = $item;

      // Set source culture
      if (!isset($this->sourceCulture))
      {
        $this->sourceCulture = $item['source_culture'];
      }
    }

    // Load event data
    $this->loadEvents();

    return $this;
  }

  public function addToIndex()
  {
    // Pre-populate
    $this->getAncestors();
    $this->getRepository();
    $this->getLanguagesAndScripts();

    // Add fields
    foreach (array_keys($this->data) as $culture)
    {
      $this->doc = new Zend_Search_Lucene_Document;

      foreach (self::$fields as $name)
      {
        $this->addField($name, $culture);
      }

      $this->addDocument();
    }
  }

  public function addDocument()
  {
    $this->index->addDocument($this->doc);
  }

  protected function addField($name, $culture)
  {
    $camelName = lcfirst(sfInflector::camelize($name));
    $field = $value = null;

    switch ($name)
    {
      case 'class_name':
        $field = Zend_Search_Lucene_Field::Keyword($camelName, 'QubitInformationObject');

        break;

      case 'collection_root_slug':
        $field = Zend_Search_Lucene_Field::Keyword($camelName, $this->getCollectionRoot()->slug);

        break;

      case 'creator':
        $names = array();
        foreach ($this->getActors(array('typeId' => QubitTerm::CREATION_ID)) as $item)
        {
          $names[] = $item->getAuthorizedFormOfName(array('culture' => $culture));
        }

        // Add field
        $field = Zend_Search_Lucene_Field::Unstored($camelName, implode(' ', $names));
        $field->boost = 8; // Boost the relevance

        break;

      case 'creator_history':
        $histories = array();
        foreach ($this->getActors(array('typeId' => QubitTerm::CREATION_ID)) as $item)
        {
          $histories[] = $item->getHistory(array('culture' => $culture));
        }

        $field = Zend_Search_Lucene_Field::Unstored($camelName, implode(' ', $histories));

        break;

      // Serialized creator data for creating links in search results
      case 'creator_serialized':
        $creators = array();
        foreach ($this->getActors(array('typeId' => QubitTerm::CREATION_ID)) as $item)
        {
          $creators[] = array(
            'name' => $item->getAuthorizedFormOfName(array(
              'culture' => $culture,
              'fallback' => true)),
            'slug' => $item->slug
          );
        }

        $field = Zend_Search_Lucene_Field::UnIndexed($camelName, serialize($creators));

        break;

      case 'copyright_status_id':
        $statusId = null;
        foreach ($this->getRights() as $item)
        {
          if (isset($item->copyright_status_id))
          {
            $statusId = $item->copyright_status_id;

            break;
          }
        }

        $field = Zend_Search_Lucene_Field::Unstored($camelName, $statusId);

        break;

      case 'culture':
        $field = Zend_Search_Lucene_Field::Keyword($camelName, $culture);

        break;

      // Serialized date array for display in search results
      case 'date_serialized':
        $field = Zend_Search_Lucene_Field::UnIndexed($camelName, serialize($this->getDates('array', $culture)));

        break;

      case 'has_digital_object':
        $field = Zend_Search_Lucene_Field::Keyword($camelName, $this->__isset('digital_object_id') ? 'true' : 'false');

        break;

      case 'identifier':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->__get('identifier'));
        $field->boost = 5;

        break;

      case 'language':
        if (0 < count($this->languages))
        {
          $value = implode(' ', $this->languages);
        }

        $field = Zend_Search_Lucene_Field::Unstored($camelName, $value);

        break;

      case 'level_of_description':
        $field = Zend_Search_Lucene_Field::Text($camelName, $this->getLevelOfDescription());

        break;

      case 'material_type_id':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getMaterialTypeId());

        break;

      case 'media_type':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getMediaTypeName());

        break;

      case 'thumbnail_path':
        $field = Zend_Search_Lucene_Field::UnIndexed($camelName, $this->getThumbnailPath());

        break;

      case 'name':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getNameAccessPoints($culture));
        $field->boost = 3;

        break;

      case 'notes':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getNotes());

        break;

      case 'parent':
        // Use "Keyword" so value is no broken on hyphens by tokenizer
        $field = Zend_Search_Lucene_Field::Keyword($camelName, $this->ancestors[count($this->ancestors)-1]->slug);

        break;

      case 'part_of':
        $field = Zend_Search_Lucene_Field::Text($camelName, $this->getCollectionRoot()->getTitle(array('culture' => $culture)));

        break;

      case 'physical_storage':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getStorageNames());

        break;

      case 'place':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getPlaceAccessPoints());
        $field->boost = 3;

        break;

      case 'reference_code':
        $field = Zend_Search_Lucene_Field::Text($camelName, $this->getReferenceCode());

        break;

      case 'repository':
        if (isset($this->repository))
        {
          $value = $this->repository->getAuthorizedFormOfName(array('culture' => $culture, 'fallback' => true));
        }

        $field = Zend_Search_Lucene_Field::Text($camelName, $value);

        break;

      case 'repository_id':
        if (isset($this->repository))
        {
          $value = $this->repository->id;
        }

        $field = Zend_Search_Lucene_Field::Keyword($camelName, $value);

        break;

      case 'repository_slug':
        if (isset($this->repository))
        {
          $value = $this->repository->slug;
        }

        $field = Zend_Search_Lucene_Field::Keyword($camelName, $value);

        break;

      case 'subject':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getSubjectAccessPoints());
        $field->boost = 5;

        break;

      case 'script':
        if (0 < count($this->scripts))
        {
          $value = implode(' ', $this->scripts);
        }

        $field = Zend_Search_Lucene_Field::Unstored($camelName, $value);

        break;

      case 'title':
        $value = $this->__get('title', $culture);
        if (0 == strlen($value))
        {
          // Include an i18n fallback for proper search result display in case the
          // title field was not translated
          $value = $this->getFallbackTitle();
        }

        $field = Zend_Search_Lucene_Field::Text($camelName, $value);
        $field->boost = 10;

        break;

      case 'transcript':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->getTranscript());
        $field->boost = 0.5;

        break;

      // DATES
      case 'start_date':
      case 'end_date':
      case 'date':
        $field = Zend_Search_Lucene_Field::Unstored($camelName, implode(' ', $this->getDates($name, $culture)));

        break;

      // TEXT fields
      case 'scope_and_content':
        $field = Zend_Search_Lucene_Field::Text($camelName, $this->__get($name, $culture));

        break;

      // KEYWORD fields (internal ids, slugs, etc.)
      case 'culture':
      case 'id':
      case 'media_type_id':
      case 'publication_status_id':
      case 'slug':
        if ($this->__isset($name))
        {
          $field = Zend_Search_Lucene_Field::Keyword($camelName, $this->__get($name, $culture));
        }

        break;

      // UNSTORED fields
      default:
        if ($this->__isset($name))
        {
          $field = Zend_Search_Lucene_Field::Unstored($camelName, $this->__get($name, $culture));
        }
    }

    if (isset($field))
    {
      $this->doc->addField($field);
    }
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

    return $this->ancestors;
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

  protected function getFallbackTitle()
  {
    $sql  = 'SELECT i18n.title';
    $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' node';
    $sql .= ' JOIN '.QubitInformationObjectI18n::TABLE_NAME.' i18n
                ON node.id = i18n.id';
    $sql .= ' WHERE node.id = ?';
    $sql .= ' AND node.source_culture = i18n.culture';

    return QubitPdo::fetchOne($sql, array($this->__get('id')))->title;
  }

  public function getLevelOfDescription()
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
        'culture' => $this->__get('culture'),
        'fallback' => true));
    }
  }

  public function getMediaTypeName()
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
        'culture' => $this->__get('culture'),
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
    if (!isset($this->data['events']))
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
        if (!isset($events[$id]))
        {
          $event = new stdClass;
          $event->id = $item['id'];
          $event->start_date = $item['start_date'];
          $event->end_date = $item['end_date'];
          $event->actor_id = $item['actor_id'];
          $event->type_id = $item['type_id'];

          $events[$item['id']] = $event;
        }

        $events[$item['id']]->dates[$item['culture']]= $item['date'];
      }

      $this->data['events'] = $events;
    }

    return $this->data['events'];
  }

  protected function getDates($field, $culture)
  {
    $dates = array();

    $events = $this->__get('events');

    if (is_array($events) && 0 < count($events))
    {
      foreach ($events as $item)
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
            if (isset($item->dates[$culture]) || isset($item->start_date) || isset($item->end_date))
            {
              $dates[] = Qubit::renderDateStartEnd($item->dates[$culture], $item->start_date, $item->end_date);
            }

            break;

          case 'array':
            if (isset($item->date) || isset($item->start_date) || isset($item->end_date))
            {
              $dates[] = array(
                'date' => $item->dates[$culture],
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

  public function getActors($options = array())
  {
    $actors = array();

    if (0 < count($this->__get('events')))
    {
      foreach ($this->__get('events') as $item)
      {
        if (isset($item->actor_id))
        {
          // Filter by type
          if (isset($options['typeId']) && $options['typeId'] != $item->type_id)
          {
            continue;
          }

          $actor = QubitActor::getById($item->actor_id);

          $actors[] = $actor;
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
    foreach ($this->getActors() as $item)
    {
      $name = $item->getAuthorizedFormOfName(array('culture' => $culture));

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

  public function getSubjectAccessPoints()
  {
    return $this->getRelatedTerms(QubitTaxonomy::SUBJECT_ID);
  }

  public function getPlaceAccessPoints()
  {
    return $this->getRelatedTerms(QubitTaxonomy::PLACE_ID);
  }

  protected function getRelatedTerms($typeId)
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
      $this->__get('culture'),
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
    // Get lookup tables
    if (!isset(self::$lookups['languages'][$this->__get('culture')]))
    {
      self::$lookups['languages'][$this->__get('culture')] = sfCultureInfo::getInstance($this->__get('culture'))->getLanguages();
    }

    if (!isset(self::$lookups['scripts'][$this->__get('culture')]))
    {
      self::$lookups['scripts'][$this->__get('culture')] = sfCultureInfo::getInstance($this->__get('culture'))->getScripts();
    }

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
            foreach ($codes as $code)
            {
              $this->languages[] = self::$lookups['languages'][$this->__get('culture')][$code];
            }

            break;

          case 'script':
            foreach ($codes as $code)
            {
              $this->scripts[] = self::$lookups['scripts'][$this->__get('culture')][$code];
            }

            break;
        }
      }
    }

    return $this;
  }

  public function getNotes()
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
      $this->__get('culture')));

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

  public function getStorageNames()
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
      ':culture' => $this->__get('culture')));

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
}
