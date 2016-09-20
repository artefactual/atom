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
 * Manage terms in search index
 *
 * @package    AccesstoMemory
 * @subpackage arElasticSearchPlugin
 */
class arElasticSearchTermPdo
{
  public
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
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function __get($name)
  {
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
    if (!isset(self::$statements['term']))
    {
      $sql = 'SELECT
                term.*,
                slug.slug,
                object.created_at,
                object.updated_at
              FROM '.QubitTerm::TABLE_NAME.' term
              JOIN '.QubitSlug::TABLE_NAME.' slug
                ON term.id = slug.object_id
              JOIN '.QubitObject::TABLE_NAME.' object
                ON term.id = object.id
              WHERE term.id = :id';

      self::$statements['term'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['term']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['term']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find term (id: $id)");
    }

    self::$statements['term']->closeCursor();

    return $this;
  }

  public function getNumberOfDescendants()
  {
    $sql  = 'SELECT COUNT(*) AS count';
    $sql .= ' FROM '.QubitTerm::TABLE_NAME;
    $sql .= ' WHERE lft > :lft';
    $sql .= ' AND rgt < :rgt';

    return QubitPdo::fetchOne($sql, array(':lft' => $this->lft, ':rgt' => $this->rgt))->count;
  }

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['slug'] = $this->slug;

    $serialized['taxonomyId'] = $this->taxonomy_id;

    $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($this->id, QubitTerm::ALTERNATIVE_LABEL_ID)) as $item)
    {
      $serialized['useFor'][] = arElasticSearchOtherName::serialize($item);
    }

    $sql = 'SELECT id, source_culture FROM '.QubitNote::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
    foreach (QubitPdo::fetchAll($sql, array($this->id, QubitTerm::SCOPE_NOTE_ID)) as $item)
    {
      $serialized['scopeNotes'][] = arElasticSearchNote::serialize($item);
    }

    $serialized['isProtected'] = QubitTerm::isProtected($this->id);
    $serialized['numberOfDescendants'] = $this->getNumberOfDescendants();

    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($this->updated_at);

    $serialized['sourceCulture'] = $this->source_culture;
    $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($this->id, array('QubitTerm'));

    return $serialized;
  }
}
