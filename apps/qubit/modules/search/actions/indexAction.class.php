<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class SearchIndexAction extends sfAction
{
  public static
    $NAMES = array(
      );

  public static
    $FACETS = array(
      'repository.id',
      'subjects.id',
      'digitalObject.mediaTypeId',
      'places.id',
      'names.id');

  protected function addField($name)
  {
  }

  protected function parseQuery()
  {
    // if querystring is empty, use match_all
    if ('' == preg_replace('/[\s\t\r\n]*/', '', $this->request->query))
    {
      return new Elastica_Query_MatchAll();
    }

    try
    {
      // Parse query string, Elastica_Query_QueryString
      $query = QubitSearch::getInstance()->parse($this->request->query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return null;
    }

    return $query;
  }

  protected function filterQuery($query)
  {
    $this->filters = array();

    $queryTerm = new Elastica_Query_Term();
    $queryBool = new Elastica_Query_Bool();

    if ($query instanceof Elastica_Query_QueryString)
    {
      $queryBool->addMust($query);
    }

    foreach ($this->request->getGetParameters() as $param => $value)
    {
      if (in_array(strtr($param, '_', '.'), self::$FACETS))
      {
        foreach (explode(',', $value) as $facetValue)
        {
          // don't include empty filters (querystring sanitization)
          if ('' != preg_replace('/[\s\t\r\n]*/', '', $facetValue))
          {
            $this->filters[$param][] = $facetValue;
            $query = $queryBool->addMust($queryTerm->setTerm(strtr($param, '_', '.'), $facetValue));
          }
        }
      }
    }

    return $query;
  }

  protected function facetQuery($query)
  {
    foreach (self::$FACETS as $field)
    {
      $facet = new Elastica_Facet_Terms($field);
      $facet->setField($field);
      $facet->setSize(50);
      $query->addFacet($facet);
    }

    $facet = new Elastica_Facet_Range('dates.startDate');
    $facet->setField('dates.startDate');
    $facet->addRange(null, null);
    $query->addFacet($facet);

    $facet = new Elastica_Facet_Range('dates.endDate');
    $facet->setField('dates.endDate');
    $facet->addRange(null, null);
    $query->addFacet($facet);

    return $query;
  }

  protected function buildQuery()
  {
    // Parse query string - Elastica_Query_{MatchAll,QueryString}
    $query = $this->parseQuery();

    // Filter by selected facets - Elastica_Query_Bool
    $query = $this->filterQuery($query);

    // Elastica_Query
    $query = new Elastica_Query($query);

    // Add facets - Elastica_Query
    $query = $this->facetQuery($query);

    $query->setSort(array('_score' => 'desc', 'slug' => 'asc'));
    $query->setLimit($request->limit);

    if (!empty($request->page))
    {
      $query->setFrom(($request->page - 1) * $request->limit);
    }

    if (isset($request->realm) && is_int($request->realm))
    {
      $query->setQuery(new Elastica_Query_Term(array('repository._id' => $request->realm)));
    }

    return $query;
  }

  protected function populateFacets()
  {
    if (!$this->pager->hasFacets())
    {
      return false;
    }

    $facets = array();

    foreach ($this->pager->getFacets() as $name => $facet)
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
        case 'repository.id':
          $criteria = new Criteria;
          $criteria->add(QubitRepository::ID, array_keys($ids), Criteria::IN);

          $repos = QubitRepository::get($criteria);

          foreach ($repos as $repo)
          {
            $reponames[$repo->id] = $repo->getAuthorizedFormOfName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
          }

          foreach ($facet['terms'] as $term)
          {
            $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
              'count' => $term['count'],
              'term' => $reponames[$term['term']]);
          }

          break;

        case 'subjects.id':
          $criteria = new Criteria;
          $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

          $subjectAPs = QubitTerm::get($criteria);

          foreach ($subjectAPs as $subjectAP)
          {
            $subjectAPnames[$subjectAP->id] = $subjectAP->getName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
          }

          foreach ($facet['terms'] as &$term)
          {
            $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
              'count' => $term['count'],
              'term' => $subjectAPnames[$term['term']]);
          }

          break;

        case 'digitalObject.mediaTypeId':
          $criteria = new Criteria;
          $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

          $mediaTypes = QubitTerm::get($criteria);

          foreach ($mediaTypes as $mediaType)
          {
            $mediaTypeNames[$mediaType->id] = $mediaType->getName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
          }

          foreach ($facet['terms'] as $term)
          {
            $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
              'count' => $term['count'],
              'term' => $mediaTypeNames[$term['term']]);
          }

          break;

        case 'dates.startDate':
          $facets[strtr($name, '.', '_')] = $facet['ranges'][0];  // FIXME: is this the best way?

          break;

        case 'places.id':
          $criteria = new Criteria;
          $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

          $placeAPs = QubitTerm::get($criteria);

          foreach ($placeAPs as $placeAP)
          {
            $placeAPnames[$placeAP->id] = $placeAP->getName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
          }

          foreach ($facet['terms'] as &$term)
          {
            $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
              'count' => $term['count'],
              'term' => $placeAPnames[$term['term']]);
          }

          break;

        case 'names.id':
          $criteria = new Criteria;
          $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

          $nameAPs = QubitActor::get($criteria);

          foreach ($nameAPs as $nameAP)
          {
            $nameAPnames[$nameAP->id] = $nameAP->getAuthorizedFormOfName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
          }

          foreach ($facet['terms'] as &$term)
          {
            $facets[strtr($name, '.', '_')]['terms'][$term['term']] = array(
              'count' => $term['count'],
              'term' => $nameAPnames[$term['term']]);
          }

          break;

      }

      $this->pager->facets = $facets;
    }

    // Populate level of descriptions
    $this->pager->levelsOfDescription = array();
    foreach (QubitTerm::getLevelsOfDescription() as $lod)
    {
      $this->pager->levelsOfDescription[$lod->id] = $lod->getName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
    }

    // Populate ancestors
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

  public function execute($request)
  {
    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    $this->form->bind($request->getGetParameters());

    if ($this->form->isValid())
    {
      try
      {
        $query = $this->buildQuery();

        $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
      }
      catch (Exception $e)
      {
        $this->error = $e->getMessage();

        return;
      }

      // Build pager
      $this->pager = new QubitSearchPager($resultSet);
      $this->pager->setPage($request->page ? $request->page : 1);

      if ('print' != $request->getGetParameter('media'))
      {
        $this->pager->setMaxPerPage($request->limit);
      }
      else
      {
        $this->pager->setMaxPerPage(500);
      }

      if ($this->pager->hasResults())
      {
        // Populate facets
        $this->populateFacets();
      }
      else if (empty($this->error))
      {
        $this->error = $this->context->i18n->__('No results found.');
      }
    }
  }
}
