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
 * Manage actors in search index
 *
 * @package    AccesstoMemory
 * @subpackage arElasticSearchPlugin
 * @author     MJ Suhonos <mj@suhonos.ca>
 */
class arElasticSearchActorPdo
{
  public
//      $ancestors,
    $i18ns;

  protected
    $data = array();

  protected static
    $conn,
    $lookups,
    $statements;

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
/*
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
*/
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function __get($name)
  {
    if ('events' == $name && !isset($this->data[$name]))
    {
      $this->data[$name] = $this->getEvents();
    }

    if (isset($this->data[$name]))
    {
      return $this->data[$name];
    }
  }

  public function __set($name, $value)
  {
    $this->data[$name] = $value;
  }

  protected function loadData($id)
  {
    if (!isset(self::$statements['actor']))
    {
      $sql = 'SELECT
                actor.*,
                slug.slug,
                object.created_at,
                object.updated_at
              FROM '.QubitActor::TABLE_NAME.' actor
              JOIN '.QubitSlug::TABLE_NAME.' slug
                ON actor.id = slug.object_id
              JOIN '.QubitObject::TABLE_NAME.' object
                ON actor.id = object.id
              WHERE actor.id = :id';

      self::$statements['actor'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['actor']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['actor']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find actor (id: $id)");
    }

    self::$statements['actor']->closeCursor();

    return $this;
  }

  protected function getMaintainingRepositoryId()
  {
    if (!isset(self::$statements['maintainingRepository']))
    {
      $sql  = 'SELECT rel.subject_id';
      $sql .= ' FROM '.QubitRelation::TABLE_NAME.' rel';
      $sql .= ' WHERE rel.object_id = :object_id';
      $sql .= '   AND rel.type_id = :type_id';

      self::$statements['maintainingRepository'] = self::$conn->prepare($sql);
    }

    self::$statements['maintainingRepository']->execute(array(
      ':object_id' => $this->id,
      ':type_id' => QubitTerm::MAINTAINING_REPOSITORY_RELATION_ID));

    return self::$statements['maintainingRepository']->fetchColumn();
  }

  protected function getOccupations()
  {
    if (!isset(self::$statements['occupations']))
    {
      $sql  = 'SELECT term.id as term_id, note.id as note_id';
      $sql .= ' FROM '.QubitObjectTermRelation::TABLE_NAME.' rel';
      $sql .= ' JOIN '.QubitTerm::TABLE_NAME.' term
                  ON rel.term_id = term.id';
      $sql .= ' LEFT JOIN '.QubitNote::TABLE_NAME.' note
                  ON rel.id = note.object_id
                  AND note.type_id = :type_id';
      $sql .= ' WHERE rel.object_id = :object_id';
      $sql .= ' AND term.taxonomy_id = :taxonomy_id';

      self::$statements['occupations'] = self::$conn->prepare($sql);
    }

    self::$statements['occupations']->execute(array(
      ':type_id' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID,
      ':object_id' => $this->id,
      ':taxonomy_id' => QubitTaxonomy::ACTOR_OCCUPATION_ID));

    return self::$statements['occupations']->fetchAll(PDO::FETCH_OBJ);
  }

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['slug'] = $this->slug;

    $serialized['entityTypeId'] = $this->entity_type_id;

    $serialized['descriptionIdentifier'] = $this->description_identifier;
    $serialized['corporateBodyIdentifiers'] = $this->corporate_body_identifiers;

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($this->id, QubitTerm::OTHER_FORM_OF_NAME_ID)) as $item)
    {
      $serialized['otherNames'][] = arElasticSearchOtherName::serialize($item);
    }

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($this->id, QubitTerm::PARALLEL_FORM_OF_NAME_ID)) as $item)
    {
      $serialized['parallelNames'][] = arElasticSearchOtherName::serialize($item);
    }

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($this->id, QubitTerm::STANDARDIZED_FORM_OF_NAME_ID)) as $item)
    {
      $serialized['standardizedNames'][] = arElasticSearchOtherName::serialize($item);
    }

    if (false !== $maintainingRepositoryId = $this->getMaintainingRepositoryId())
    {
      $serialized['maintainingRepositoryId'] = (integer)$maintainingRepositoryId;
    }

    foreach ($this->getOccupations() as $occupation)
    {
      $occupationArray = array();

      $i18nFields = arElasticSearchModelBase::serializeI18ns(
        $occupation->term_id,
        array('QubitTerm'),
        array('fields' => array('name'))
      );

      if (isset($occupation->note_id))
      {
        $i18nFields = arElasticSearchModelBase::serializeI18ns(
          $occupation->note_id,
          array('QubitNote'),
          array('fields' => array('content'), 'merge' => $i18nFields)
        );
      }

      $occupationArray['id'] = $occupation->term_id;
      $occupationArray['i18n'] = $i18nFields;

      $serialized['occupations'][] = $occupationArray;
    }

    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($this->updated_at);

    $serialized['sourceCulture'] = $this->source_culture;
    $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($this->id, array('QubitActor'));

    return $serialized;
  }
}
