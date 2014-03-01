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

class APIInformationObjectsTreeAction extends QubitAPIAction
{
  protected function get($request)
  {
    $data = $this->getTree();

    return $data;
  }

  protected function getTree()
  {
    $sql = <<<EOL
SELECT
  node.id,
  node.level_of_description_id,
  i18n.title,
  node.lft, node.rgt, node.parent_id
FROM
  information_object AS node,
  information_object AS parent,
  information_object_i18n AS i18n
WHERE
  node.lft BETWEEN parent.lft AND parent.rgt
  AND node.id = i18n.id
  AND node.source_culture = i18n.culture
  AND parent.id = ?
ORDER BY node.lft;
EOL;

    // Build a nested set of objects.
    // Notice that fetchAll returns objects, not arrays.
    $data = new stdClass; // Here is where we are storing the nested set
    $flat = array();      // Flat hashmap (id => ref-to-obj) for quick searches
    foreach (QubitPdo::fetchAll($sql, array($this->request->id)) as $item)
    {
      if (isset($flat[$item->parent_id]))
      {
        $parent = $flat[$item->parent_id];
        $parent->children[] = $item;
      }
      else
      {
        $data = $item;
      }

      $flat[$item->id] = $item;
    }

    return $data;
  }
}
