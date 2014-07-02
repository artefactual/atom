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
class arElasticSearchFixityReportPdo
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
    if (!isset(self::$statements['fixityReport']))
    {
      $sql  = 'SELECT *';
      $sql .= ' FROM '.QubitFixityReport::TABLE_NAME;
      $sql .= ' WHERE id = :id';

      self::$statements['fixityReport'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['fixityReport']->execute(array(':id' => $id));

    // Get first result
    $this->data = self::$statements['fixityReport']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find Fixity report (id:'.$id.')");
    }

    self::$statements['fixityReport']->closeCursor();

    return $this;
  }

  protected function getAipPartOfTitle($id)
  {
    $sql  = 'SELECT
                i18n.title';
    $sql .= ' FROM '.QubitInformationObjectI18n::TABLE_NAME.' i18n';
    $sql .= ' JOIN '.QubitInformationObject::TABLE_NAME.' inf';
    $sql .= ' ON inf.id = i18n.id';
    $sql .= ' JOIN '.QubitAip::TABLE_NAME.' aip';
    $sql .= ' ON aip.part_of = inf.id';
    $sql .= ' WHERE aip.id = ?';
    $sql .= ' AND i18n.culture = ?';

    self::$statements['aipPartOfTitle'] = self::$conn->prepare($sql);
    self::$statements['aipPartOfTitle']->execute(array($id, 'en'));

    return self::$statements['aipPartOfTitle']->fetchColumn();
  }

  protected function getAipName($id)
  {
    $sql  = 'SELECT
                filename';
    $sql .= ' FROM '.QubitAip::TABLE_NAME;
    $sql .= ' WHERE id = ?';

    self::$statements['aipName'] = self::$conn->prepare($sql);
    self::$statements['aipName']->execute(array($id));

    return self::$statements['aipName']->fetchColumn();
  }

  protected function getAipAttachedTo($id)
  {
    $sql  = 'SELECT
                i18n.value';
    $sql .= ' FROM '.QubitProperty::TABLE_NAME.' node';
    $sql .= ' JOIN '.QubitPropertyI18n::TABLE_NAME.' i18n
                ON node.id = i18n.id';
    $sql .= ' WHERE node.source_culture = i18n.culture
                AND node.object_id = ?
                AND node.name = ?';

    self::$statements['aipAttachedTo'] = self::$conn->prepare($sql);
    self::$statements['aipAttachedTo']->execute(array($id, 'attachedTo'));

    return self::$statements['aipAttachedTo']->fetchColumn();
  }

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['success'] = $this->success;
    $serialized['message'] = $this->message;
    $serialized['timeStarted'] = arElasticSearchPluginUtil::convertDate($this->time_started);
    $serialized['timeCompleted'] = arElasticSearchPluginUtil::convertDate($this->time_completed);

    if (null !== $this->session_uuid)
    {
      $serialized['sessionUuid'] = $this->session_uuid;
    }

    $serialized['aip']['uuid'] = $this->uuid;

    if (isset($this->aip_id))
    {
      $serialized['aip']['id'] = $this->aip_id;

      if (false !== $name = $this->getAipName($this->aip_id))
      {
        $serialized['aip']['name'] = $name;
      }

      if (false !== $partOfTitle = $this->getAipPartOfTitle($this->aip_id))
      {
        $serialized['aip']['partOf'] = $partOfTitle;
      }

      if (false !== $attachedTo = $this->getAipAttachedTo($this->aip_id))
      {
        $serialized['aip']['attachedTo'] = $attachedTo;
      }
    }

    if (isset($this->failures))
    {
      $failures = json_decode($this->failures, true);

      foreach ($failures['files'] as $type => $fails)
      {
        foreach ($fails as $data)
        {
          $fail = array();

          $fail['type'] = $type;
          $fail['path'] = $data['path'];
          $fail['hashType'] = $data['hash_type'];
          $fail['expectedHash'] = $data['expected'];
          $fail['actualHash'] = $data['actual'];
          $fail['message'] = $data['message'];

          $serialized['failures'][] = $fail;
        }
      }
    }

    return $serialized;
  }
}
