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

class arElasticSearchTerm extends arElasticSearchModelBase
{
  public function populate()
  {
    $errors = array();

    $sql  = 'SELECT term.id';
    $sql .= ' FROM '.QubitTerm::TABLE_NAME.' term';
    $sql .= ' JOIN '.QubitObject::TABLE_NAME.' object ON term.id = object.id';
    $sql .= ' WHERE term.id != ? AND object.class_name = ?';
    $sql .= ' ORDER BY term.lft';

    $terms = QubitPdo::fetchAll($sql, array(QubitTerm::ROOT_ID, 'QubitTerm'));

    $this->count = count($terms);

    // Loop through results, and add to search index
    foreach ($terms as $key => $item)
    {
      try
      {
        $node = new arElasticSearchTermPdo($item->id);
        $data = $node->serialize();

        QubitSearch::getInstance()->addDocument($data, 'QubitTerm');

        $this->logEntry($data['i18n'][$data['sourceCulture']]['name'], $key + 1);
      }
      catch (sfException $e)
      {
        $errors[] = $e->getMessage();
      }
    }

    return $errors;
  }

  public static function update($object)
  {
    $node = new arElasticSearchTermPdo($object->id);

    QubitSearch::getInstance()->addDocument($node->serialize(), 'QubitTerm');

    return true;
  }
}
