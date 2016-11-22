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

class arElasticSearchInformationObject extends arElasticSearchModelBase
{
  protected static
    $conn,
    $statements,
    $counter = 0;

  protected $errors = array();

  public function populate()
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    // Get count of all information objects
    $sql  = 'SELECT COUNT(*)';
    $sql .= ' FROM '.QubitInformationObject::TABLE_NAME;
    $sql .= ' WHERE id > ?';

    $this->count = QubitPdo::fetchColumn($sql, array(QubitInformationObject::ROOT_ID));

    // Recursively descend down hierarchy
    $this->recursivelyAddInformationObjects(QubitInformationObject::ROOT_ID, $this->count);

    return $this->errors;
  }

  public function recursivelyAddInformationObjects($parentId, $totalRows, $options = array())
  {
    // Get information objects
    if (!isset(self::$statements['getChildren']))
    {
      $sql  = 'SELECT
                  io.id,
                  io.lft,
                  io.rgt';
      $sql .= ' FROM '.QubitInformationObject::TABLE_NAME.' io';
      $sql .= ' WHERE io.parent_id = ?';
      $sql .= ' ORDER BY io.lft';

      self::$statements['getChildren'] = self::$conn->prepare($sql);
    }

    self::$statements['getChildren']->execute(array($parentId));

    // Loop through results, and add to search index
    foreach (self::$statements['getChildren']->fetchAll(PDO::FETCH_OBJ) as $item)
    {
      try
      {
        $node = new arElasticSearchInformationObjectPdo($item->id, $options);
        $data = $node->serialize();

        QubitSearch::getInstance()->addDocument($data, 'QubitInformationObject');

        self::$counter++;

        $this->logEntry($data['i18n'][$data['sourceCulture']]['title'], self::$counter);

        // Descend hierarchy
        if (1 < ($item->rgt - $item->lft))
        {
          // Pass ancestors and repository down to descendants
          $this->recursivelyAddInformationObjects($item->id, $totalRows, array(
            'ancestors'  => array_merge($node->getAncestors(), array($node)),
            'repository' => $node->getRepository()));
        }
      }
      catch (sfException $e)
      {
        $this->errors[] = $e->getMessage();
      }
    }
  }

  public static function update($object)
  {
    $node = new arElasticSearchInformationObjectPdo($object->id);

    QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitInformationObject');

    return true;
  }

  /**
   * Set boost values for various information object fields.
   *
   * @param array &$fields  A reference to our array of fields we're adding boost values to.
   * @param array $cultures  An array specifying which cultures the i18n fields cover.
   */
  public static function setBoostValues(&$fields, $cultures)
  {
    $i18nBoostFields = array(
      'i18n.%s.title' => 10,
      'subjects.i18n.%s.name' => 5,
      'creators.i18n.%s.authorizedFormOfName' => 6,
      'names.i18n.%s.authorizedFormOfName' => 3,
      'places.i18n.%s.name' => 3,
      'i18n.%s.scopeAndContent' => 5,
    );

    $nonI18nBoostFields = array(
      'identifier' => 5,
    );

    self::addBoostValuesToFields($fields, $i18nBoostFields, $nonI18nBoostFields);
  }
}
