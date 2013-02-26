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
class QubitAccessLog extends BaseAccessLog
{
  public static function getPopularLastWeek(array $options = array())
  {
    $sql  = 'SELECT object_id, COUNT(object_id) AS count';
    $sql .= ' FROM access_log';
    $sql .= ' WHERE access_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 WEEK) AND NOW()';
    $sql .= ' GROUP BY (object_id)';
    $sql .= ' ORDER BY count DESC';

    if (isset($options['limit']))
    {
      $sql .= ' LIMIT 0,?';
      $parameters = array($options['limit']);
    }
    else
    {
      $parameters = array();
    }

    $statement = QubitPdo::prepareAndExecute($sql, $parameters);

    return $statement->fetchAll();
  }
}
