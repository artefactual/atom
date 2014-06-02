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

class ApiActivityIngestionAction extends QubitApiAction
{
  protected function get($request)
  {
    return $this->getResults();
  }

  protected function getResults()
  {
    $sql = <<<EOL
SELECT
  io.id,
  io18n.title,
  aip.filename,
  aip.size_on_disk,
  aip.created_at,
  digital_object.path
FROM
  aip
INNER JOIN information_object io
  ON aip.part_of = io.id
INNER JOIN information_object_i18n io18n
  ON io.id = io18n.id
INNER JOIN digital_object
  ON io.id = digital_object.information_object_id
WHERE
  io.level_of_description_id = ?
ORDER BY
  aip.created_at DESC
LIMIT 20;
EOL;

    $results = QubitPdo::fetchAll($sql, array(sfConfig::get('app_drmc_lod_artwork_record_id')));

    if (false === $results)
    {
      throw new QubitApiException;
    }

    $aipCreations = array();

    foreach ($results as $item)
    {
      $date = new DateTime($item->created_at);
      $createdAt = $date->format('Y-m-d');

      array_push($aipCreations, array(
        'id' => $item->id,
        'artwork_title' => $item->title,
        'aip_title' => $item->filename,
        'size_on_disk' => $item->size_on_disk,
        'thumbnail_path' => $item->path,
        'created_at' => $createdAt
      ));
    }

    return
      array(
        'results' => $aipCreations
      );
  }
}
