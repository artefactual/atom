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

class ApiInformationObjectsBrowseAction extends QubitApiAction
{
  protected function get($request)
  {
    // Create query objects
    $query = new \Elastica\Query;
    $queryBool = new \Elastica\Query\Bool;
    $queryBool->addMust(new \Elastica\Query\MatchAll);

    // Pagination and sorting
    $this->prepareEsPagination($query);
    $this->prepareEsSorting($query, array(
      'createdAt' => 'createdAt'));

    // Assign query
    $query->setQuery($queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    // Build array from results
    $results = $lodMapping = array();
    foreach ($resultSet as $hit)
    {
      $doc = $hit->getData();
      $result = array();

      if ('1' == sfConfig::get('app_inherit_code_informationobject', 1))
      {
        $this->addItemToArray($result, 'reference_code', $doc['referenceCode']);
      }
      else
      {
        $this->addItemToArray($result, 'reference_code', $doc['identifier']);
      }

      $this->addItemToArray($result, 'slug', $doc['slug']);
      $this->addItemToArray($result, 'title', get_search_i18n($doc, 'title'));
      $this->addItemToArray($result, 'repository', get_search_i18n($doc['repository'], 'authorizedFormOfName'));
      $this->addItemToArray($result, 'physical_characteristics', get_search_i18n($doc, 'physicalCharacteristics'));

      // Get LOD name, creating a mapping for other results
      if (isset($doc['levelOfDescriptionId']))
      {
        if (isset($lodMapping[$doc['levelOfDescriptionId']]))
        {
          $lodName = $lodMapping[$doc['levelOfDescriptionId']];
        }
        else
        {
          if (null !== $lod = QubitTerm::getById($doc['levelOfDescriptionId']))
          {
            $lodMapping[$doc['levelOfDescriptionId']] = $lod->name;
            $lodName = $lod->name;
          }
        }

        $this->addItemToArray($result, 'level_of_description', $lodName);
      }

      // Create array with creator names
      if (isset($doc['creators']) && count($doc['creators']) > 0)
      {
        $creators = array();
        foreach ($doc['creators'] as $creator)
        {
          $creatorName = get_search_i18n($creator, 'authorizedFormOfName');
          if (!empty($creatorName))
          {
            $creators[] = $creatorName;
          }
        }

        $this->addItemToArray($result, 'creators', $creators);
      }

      // Create array with creation dates
      if (isset($doc['dates']) && count($doc['dates']) > 0)
      {
        $dates = array();
        foreach ($doc['dates'] as $event)
        {
          if (isset($event['typeId']) && $event['typeId'] == QubitTerm::CREATION_ID)
          {
            $date = get_search_i18n($event, 'date');
            if (!empty($date))
            {
              $dates[] = $date;
            }
          }
        }

        $this->addItemToArray($result, 'creation_dates', $dates);
      }

      // Create array with place names
      if (isset($doc['places']) && count($doc['places']) > 0)
      {
        $places = array();
        foreach ($doc['places'] as $place)
        {
          $placeName = get_search_i18n($place, 'name');
          if (!empty($placeName))
          {
            $places[] = $placeName;
          }
        }

        $this->addItemToArray($result, 'place_access_points', $places);
      }

      // Add thumbnail URL
      if (isset($doc['digitalObject']['thumbnailPath']))
      {
        $this->addItemToArray($result, 'thumbnail_url', $this->siteBaseUrl . $doc['digitalObject']['thumbnailPath']);
      }

      $results[] = $result;
    }

    return
      array(
        'total' => $resultSet->getTotalHits(),
        'results' => $results);
  }
}
