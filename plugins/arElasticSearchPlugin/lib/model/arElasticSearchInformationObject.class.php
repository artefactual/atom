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

    QubitSearch::getInstance()->flushBatch();
    QubitSearch::getInstance()->index->refresh();

    $this->updateAncestorsDigitalObjectInfo();
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
  }

  public static function update($object)
  {
    $node = new arElasticSearchInformationObjectPdo($object->id);

    QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitInformationObject');

    return true;
  }

  private function updateAncestorsDigitalObjectInfo()
  {
    $sql = 'SELECT i.id, i.parent_id FROM digital_object d
            JOIN information_object i ON d.information_object_id = i.id
            WHERE i.parent_id != '.QubitInformationObject::ROOT_ID;

    foreach (QubitPdo::fetchAll($sql) as $row)
    {
      $this->updateDigitalObjectInfo($row->id, $row->parent_id);
    }
  }

  private function updateDigitalObjectInfo($id, $parentId)
  {
    $search = new arElasticSearchPluginQuery(1);
    $search->queryBool->addMust(new \Elastica\Query\Term(array('_id' => $parentId)));
    $results = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($search->getQuery(false, false));

    if (count($results) === 0)
    {
      // Special case: this can happen during a search:populate when the ancestor documents were
      // just inserted but the ES index hasn't refreshed yet. We handle this problem by re-calling
      // this method at the end of the search:populate task after ES refreshes.
      return;
    }
    else if (count($results) !== 1)
    {
      throw new sfException('Error: expected one result for ancestor search (id='.$parentId.'), got '.count($results));
    }

    // Normal case: the archival description's document needs updating, and the specified
    // ancestor document is present in ES. Take our 1 result and insert/remove this info object's id
    // to keep a running count of which children have/don't have digital objects.
    $io = $results->offsetGet(0)->getData();

    if (!in_array($id, $io['childDigitalObjects']))
    {
      $io['childDigitalObjects'][] = $id;
      $io['id'] = $parentId;

      QubitSearch::getInstance()->addDocument($io, 'QubitInformationObject');
    }
  }
}
