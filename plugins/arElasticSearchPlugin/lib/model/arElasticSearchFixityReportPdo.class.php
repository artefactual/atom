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

  public function serialize()
  {
    $serialized = array();

    $serialized['id'] = $this->id;
    $serialized['success'] = $this->success;
    $serialized['message'] = $this->message;
    $serialized['timeStarted'] = arElasticSearchPluginUtil::convertDate($this->time_started);
    $serialized['timeCompleted'] = arElasticSearchPluginUtil::convertDate($this->time_completed);

    if (null !== $this->collection_check_id)
    {
      $serialized['collectionCheckId'] = $this->collection_check_id;
    }

    $serialized['aip']['uuid'] = $this->uuid;

    if (null !== $this->aip_id && null !== $name = $this->getAipName($this->aip_id))
    {
      $serialized['aip']['name'] = $name;
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
