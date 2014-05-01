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
 * Manage DRMC queries in search index
 *
 * @package    AccesstoMemory
 * @subpackage arElasticSearchPlugin
 */
class arElasticSearchDrmcQueryPdo
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
    if (!isset(self::$statements['drmcQuery']))
    {
      $sql  = 'SELECT *';
      $sql .= ' FROM '.QubitDrmcQuery::TABLE_NAME;
      $sql .= ' WHERE id = :id';

      self::$statements['drmcQuery'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['drmcQuery']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['drmcQuery']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find DRMC query (id:'.$id.')");
    }

    self::$statements['drmcQuery']->closeCursor();

    return $this;
  }

  protected function getDigitalObjects()
  {
    $sql  = 'SELECT
                prop.object_id';
    $sql .= ' FROM '.QubitProperty::TABLE_NAME.' prop';
    $sql .= ' JOIN '.QubitPropertyI18n::TABLE_NAME.' prop_i18n
                ON prop.id = prop_i18n.id';
    $sql .= ' WHERE prop_i18n.value = ?
                AND prop.name = ?';

    self::$statements['do'] = self::$conn->prepare($sql);
    self::$statements['do']->execute(array($this->uuid, 'aipUUID'));

    return self::$statements['do']->fetchAll(PDO::FETCH_OBJ);
  }

  protected function getUserName($id)
  {
    $sql  = 'SELECT
                username';
    $sql .= ' FROM '.QubitUser::TABLE_NAME;
    $sql .= ' WHERE id = ?';

    self::$statements['userName'] = self::$conn->prepare($sql);
    self::$statements['userName']->execute(array($id));

    return self::$statements['userName']->fetchColumn();
  }

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['name'] = $this->name;
    $serialized['description'] = $this->description;
    $serialized['type'] = $this->type;
    $serialized['query'] = $this->query;
    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($this->updated_at);

    if (null !== $this->user_id)
    {
      $serialized['user']['id'] = $this->user_id;

      if (null !== $name = $this->getUserName($this->user_id))
      {
        $serialized['user']['name'] = $name;
      }
    }

    $serialized['slug'] = QubitSlug::slugify($this->created_at.' '.$this->name);

    return $serialized;
  }
}
