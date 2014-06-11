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

class ApiInformationObjectsTreeAssociationsAction extends QubitApiAction
{
  protected function get($request)
  {
    $sql = <<<EOL
SELECT
  node.id,
  relation.id as relation_id,
  relation.type_id,
  relation.object_id
FROM
 information_object AS node,
 information_object AS parent,
 relation
WHERE
  node.lft BETWEEN parent.lft AND parent.rgt
  AND parent.id = ?
  AND relation.subject_id = node.id
  AND relation.type_id IN (SELECT id FROM term WHERE taxonomy_id = ?);
EOL;

    $results = QubitPdo::fetchAll($sql, array(
      $request->id,
      sfConfig::get('app_drmc_taxonomy_associative_relationship_types_id')));

    if (0 === count($results))
    {
      throw new QubitApi404Exception('Information object not found');
    }
    else if (false === $results)
    {
      throw new QubitApiException;
    }

    $data = array();
    foreach ($results as $item)
    {
      $data[] = array(
        'id' => (int)$item->relation_id,
        'type_id' => (int)$item->type_id,
        'subject_id' => (int)$item->id,
        'object_id' => (int)$item->object_id
      );
    }

    return $data;
  }
}
