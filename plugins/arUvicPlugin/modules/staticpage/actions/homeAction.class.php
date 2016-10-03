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

class StaticPageHomeAction extends StaticPageIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    /* Watched recently... how to ignore repeated items properly?
    $sql  = 'SELECT s.slug, d2.path, d2.name';
    $sql .= ' FROM access_log';
    $sql .= ' JOIN digital_object AS d1 ON (access_log.object_id = d1.information_object_id)';
    $sql .= ' JOIN digital_object AS d2 ON (d1.id = d2.parent_id)';
    $sql .= ' JOIN slug AS s ON (d1.information_object_id = s.object_id)';
    $sql .= ' WHERE d2.usage_id = 141';
    $sql .= ' LIMIT 10';
    */

    $sql  = 'SELECT s.slug, d2.path, d2.name, COUNT(access_log.object_id) AS count';
    $sql .= ' FROM access_log';
    $sql .= ' JOIN digital_object AS d1 ON (access_log.object_id = d1.information_object_id)';
    $sql .= ' JOIN digital_object AS d2 ON (d1.id = d2.parent_id)';
    $sql .= ' JOIN slug AS s ON (access_log.object_id = s.object_id)';
    $sql .= ' WHERE d2.usage_id = 141';
    $sql .= '   AND access_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()';
    $sql .= ' GROUP BY (access_log.object_id)';
    $sql .= ' ORDER BY count DESC';
    $sql .= ' LIMIT 16';

    $stmt = QubitPdo::prepare($sql);
    $stmt->execute();

    $this->mostPopularLastMonth = $stmt->fetchAll();
  }
}
