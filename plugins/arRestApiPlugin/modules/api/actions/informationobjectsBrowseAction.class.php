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
    $getParameters = $request->getGetParameters();

    // Get actual information object template to check archival history
    // visibility in _advancedSearch partial and in parseQuery function
    $archivalStandard = 'isad';
    if (null !== $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template'))
    {
      $archivalStandard = $infoObjectTemplate->getValue(array('sourceCulture'=>true));
    }

    $limit = sfConfig::get('app_hits_per_page');
    if (isset($request->limit) && ctype_digit($request->limit))
    {
      $limit = $request->limit;
    }

    $skip = 0;
    if (isset($request->skip) && ctype_digit($request->skip))
    {
      $skip = $request->skip;
    }

    // Default to show all level descriptions
    if (!isset($request->topLod) || !filter_var($request->topLod, FILTER_VALIDATE_BOOLEAN))
    {
      $getParameters['topLod'] = 0;
    }

    $this->search = new arElasticSearchPluginQuery($limit, $skip);
    $this->search->addAggFilters(InformationObjectBrowseAction::$AGGS, $getParameters);
    $this->search->addAdvancedSearchFilters(InformationObjectBrowseAction::$NAMES, $getParameters, $archivalStandard);

    // Determin sort field and default order
    switch ($request->sort)
    {
      case 'identifier':
        $field = 'referenceCode.untouched';
        $order = 'asc';
        break;

      // I don't think that this is going to scale, but let's leave it for now
      case 'alphabetic':
        $field = sprintf('i18n.%s.title.untouched', sfContext::getInstance()->user->getCulture());
        $order = 'asc';
        break;

      case 'date':
        $field = 'dates.startDate';
        $order = 'asc';
        break;

      case 'lastUpdated':
      default:
        $field = 'updatedAt';
        $order = 'desc';
    }

    // Optionally reverse sort order
    if (isset($request->reverse) && !empty($request->reverse))
    {
      $order = ($order == 'asc') ? 'desc' : 'asc';
    }

    $this->search->query->setSort(array($field => $order));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->getQuery(false, true));

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
      $this->addItemToArray($result, 'physical_characteristics', get_search_i18n($doc, 'physicalCharacteristics'));

      if (isset($doc['repository']))
      {
        $this->addItemToArray($result, 'repository', get_search_i18n($doc['repository'], 'authorizedFormOfName'));
      }

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
