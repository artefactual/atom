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

class TermBrowseTermAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->resource = $this->getRoute()->resource;

    $queryBool = new Elastica_Query_Bool();

    switch ($this->resource->taxonomyId)
    {
      case QubitTaxonomy::PLACE_ID:
        $this->icon = 'places';
        $queryBool->addMust(new Elastica_Query_Term(array('places.id' => $this->resource->id)));

        break;

      case QubitTaxonomy::SUBJECT_ID:
        $this->icon = 'subjects';
        $queryBool->addMust(new Elastica_Query_Term(array('subjects.id' => $this->resource->id)));

        break;

      default:
        // pass
        $queryFilter = new Elastica_Query_MatchAll();

        break;
    }

    $queryBool->addMust(new Elastica_Query_Term(array('publicationStatusId' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)));

    $this->filters = array();
    foreach ($this->request->getGetParameters() as $param => $value)
    {
      if (in_array(strtr($param, '_', '.'), array('digitalObject.mediaTypeId')))
      {
        foreach (explode(',', $value) as $facetValue)
        {
          // don't include empty filters (querystring sanitization)
          if ('' != preg_replace('/[\s\t\r\n]*/', '', $facetValue))
          {
            $this->filters[$param][] = $facetValue;

            $queryBool->addMust(new Elastica_Query_Term(array(
              strtr($param, '_', '.') => $facetValue)));
          }
        }
      }
    }

    $query = new Elastica_Query();
    $query->setSort(array('_score' => 'desc', 'slug' => 'asc'));
    $query->setLimit($request->limit);
    $query->setQuery($queryBool);

    if (!empty($request->page))
    {
      $query->setFrom(($request->page - 1) * $request->limit);
    }

    $facet = new Elastica_Facet_Terms('digitalObject.mediaTypeId');
    $facet->setField('digitalObject.mediaTypeId');
    $facet->setSize(50);
    $query->addFacet($facet);

    $facet = new Elastica_Facet_Range('dates.startDate');
    $facet->setField('dates.startDate');
    $facet->addRange(null, null);
    $query->addFacet($facet);

    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return;
    }

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);

    if ($this->pager->hasResults())
    {
      foreach ($resultSet->getFacets() as $name => $facet)
      {
        if (isset($facet['terms']))
        {
          $ids = array();
          foreach ($facet['terms'] as $term)
          {
            $ids[$term['term']] = $term['count'];
          }
        }

        switch ($name)
        {
          case 'digitalObject.mediaTypeId':
            $criteria = new Criteria;
            $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

            $mediaTypes = QubitTerm::get($criteria);

            foreach ($mediaTypes as $mediaType)
            {
              $mediaTypeNames[$mediaType->id] = $mediaType->getName(array(
                'cultureFallback' => true,
                'culture' => $this->context->user->getCulture()));
            }

            foreach ($facet['terms'] as $term)
            {
              $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
                'count' => $term['count'],
                'term' => $mediaTypeNames[$term['term']]);
            }

            break;

          case 'dates.startDate':
            // FIXME: is this the best way?
            $facets[strtr($name, '.', '_')] = $facet['ranges'][0];

            break;
        }
      }

      $this->pager->facets = $facets;

      $this->pager->levelsOfDescription = array();
      foreach (QubitTerm::getLevelsOfDescription() as $lod)
      {
        $this->pager->levelsOfDescription[$lod->id] = $lod->getName(array(
          'cultureFallback' => true,
          'culture' => $this->context->user->getCulture()));
      }

      $ancestorsIds = array();
      foreach ($this->pager->getResults() as $hit)
      {
        $doc = $hit->getData();
        foreach ($doc['ancestors'] as $item)
        {
          if (in_array($item, $ancestorsIds))
          {
            continue;
          }

          $ancestorsIds[] = $item;
        }
      }

      $sql = 'SELECT
          io.id,
          o.slug,
          io.title
        FROM '.QubitInformationObjectI18n::TABLE_NAME.' AS io
        LEFT JOIN '.QubitSlug::TABLE_NAME.' AS o ON (o.object_id = io.id AND io.culture = ?)
        WHERE o.object_id IN ('.implode(',', $ancestorsIds).')';
      $this->pager->ancestors = array();
      foreach (QubitPdo::fetchAll($sql, array($this->context->user->getCulture())) as $ancestor)
      {
        $this->pager->ancestors[$ancestor->id] = array(
          'slug' => $ancestor->slug,
          'title' => $ancestor->title);
      }
    }
  }
}
