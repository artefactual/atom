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
  public static function getPopularThisWeek(array $options = array())
  {
    $sql  = 'SELECT access_log.object_id, COUNT(access_log.object_id) AS count';
    $sql .= ' FROM access_log';
    $sql .= ' LEFT JOIN status ON (access_log.object_id = status.object_id)';
    $sql .= ' WHERE access_date BETWEEN DATE_SUB(:now, INTERVAL 1 WEEK) AND :now';
    $sql .= ' AND (status_id != :draft OR status_id IS NULL)';
    $sql .= ' GROUP BY (object_id)';
    $sql .= ' ORDER BY count DESC';

    if (isset($options['limit']))
    {
      $sql .= ' LIMIT :skip, :max';
    }

    $stmt = QubitPdo::prepare($sql);

    // As we don't store dates in UTC
    $stmt->bindValue(':now', date('Y-m-d H:i:s'), PDO::PARAM_STR);

    $stmt->bindValue(':draft', QubitTerm::PUBLICATION_STATUS_DRAFT_ID, PDO::PARAM_INT);

    if (isset($options['limit']))
    {
      $stmt->bindValue(':skip', 0, PDO::PARAM_INT);
      $stmt->bindValue(':max', (int) $options['limit'], PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchAll();
  }
}
