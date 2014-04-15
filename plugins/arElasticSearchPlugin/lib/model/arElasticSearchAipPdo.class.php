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
 * Manage aips in search index
 *
 * @package    AccesstoMemory
 * @subpackage arElasticSearchPlugin
 */
class arElasticSearchAipPdo
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
    if (!isset(self::$statements['aip']))
    {
      $sql  = 'SELECT *';
      $sql .= ' FROM '.QubitAip::TABLE_NAME;
      $sql .= ' WHERE id = :id';

      self::$statements['aip'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['aip']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['aip']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find aip (id:'.$id.')");
    }

    self::$statements['aip']->closeCursor();

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

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['uuid'] = $this->uuid;
    $serialized['filename'] = $this->filename;
    $serialized['sizeOnDisk'] = $this->size_on_disk;
    $serialized['digitalObjectCount'] = $this->digital_object_count;
    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);

    if (null !== $this->type_id)
    {
      $node = new arElasticSearchTermPdo($this->type_id);
      $serialized['type'] = $node->serialize();
    }

    if (null !== $this->part_of)
    {
      $serialized['partOf']['id'] = $this->part_of;
      $serialized['partOf']['i18n'] = arElasticSearchModelBase::serializeI18ns($this->part_of, array('QubitInformationObject'), array('fields' => array('title')));
    }

    /*
    foreach ($this->getDigitalObjects() as $item)
    {
      $node = new arElasticSearchInformationObjectPdo($item->object_id);
      $serialized['digitalObjects'][] = $node->serialize();
    }
    */

    return $serialized;
  }
}
