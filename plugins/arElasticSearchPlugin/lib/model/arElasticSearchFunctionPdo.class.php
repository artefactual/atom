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
 * Manage functions in search index
 *
 * @package    AccesstoMemory
 * @subpackage arElasticSearchPlugin
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class arElasticSearchFunctionPdo
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
    if (!isset(self::$statements['function']))
    {
      $sql = 'SELECT
                function.*,
                slug.slug,
                object.created_at,
                object.updated_at
              FROM '.QubitFunction::TABLE_NAME.' function
              JOIN '.QubitSlug::TABLE_NAME.' slug
                ON function.id = slug.object_id
              JOIN '.QubitObject::TABLE_NAME.' object
                ON function.id = object.id
              WHERE function.id = :id';

      self::$statements['function'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['function']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['function']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find function (id: $id)");
    }

    self::$statements['function']->closeCursor();

    return $this;
  }

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['slug'] = $this->slug;
    $serialized['decriptionStatusId'] = $this->description_status_id;
    $serialized['decriptionDetailId'] = $this->description_detail_id;
    $serialized['decriptionIdentifier'] = $this->description_identifier;

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

    $serialized['createdAt'] = arElasticSearchPluginUtil::convertDate($this->created_at);
    $serialized['updatedAt'] = arElasticSearchPluginUtil::convertDate($this->updated_at);

    $serialized['sourceCulture'] = $this->source_culture;
    $serialized['i18n'] = arElasticSearchModelBase::serializeI18ns($this->id, array('QubitFunction'));

    return $serialized;
  }
}
