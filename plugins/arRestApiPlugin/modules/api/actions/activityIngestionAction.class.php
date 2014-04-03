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
    // TODO: check if username, that SWORD was accessed with, is in METS
    //       ...could use "Archivematica user" in METS
    $sql = <<<EOL
SELECT
  term.id
FROM
  taxonomy_i18n AS taxonomy
LEFT JOIN term AS term
  ON taxonomy.id=term.taxonomy_id
INNER JOIN term_i18n AS term_i18n
  ON term.id=term_i18n.id
WHERE
  taxonomy.name='Levels of description'
  AND taxonomy.culture='en'
  AND term_i18n.name = 'Artwork record'
  AND term_i18n.culture='en'
EOL;

    $levelOfDescription = QubitPdo::fetchOne($sql, array($this->request->id));
    if (null === $levelOfDescription)
    {
      throw new QubitApi404Exception('Level of description not found');
    }

    $artworkRecordLevelOfDescriptionId = $levelOfDescription->id;

    $sql = <<<EOL
SELECT
  ii.title,
  aip.filename,
  aip.size_on_disk,
  aip.created_at
FROM
  aip AS aip 
INNER JOIN information_object i
  ON aip.part_of=i.id
INNER JOIN information_object_i18n ii
  ON i.id=ii.id
WHERE
  i.level_of_description_id=?
ORDER BY aip.created_at DESC LIMIT 3;
EOL;

    $results = QubitPdo::fetchAll($sql, array($artworkRecordLevelOfDescriptionId));
    if (0 === count($results))
    {
      throw new QubitApi404Exception('Information object not found');
    }
    else if (false === $results)
    {
      throw new QubitApiException;
    }

    $aipCreations = array();

    foreach ($results as $item)
    {
      $date = new DateTime($item->created_at);
      $createdAt = $date->format('Y-m-d');
      
      array_push($aipCreations, array(
        'artwork_title' => $item->title,
        'aip_title' => $item->filename,
        'size_on_disk' => $item->size_on_disk,
        'created_at' => $createdAt
      ));
    }

    return
      array(
        //'total' => $resultSet->getTotalHits(),
        'results' => $aipCreations
      );
  }
}
