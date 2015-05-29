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

class arElasticSearchPluginQuery
{
  protected $facets;

  public $query;
  public $queryBool;
  public $filters;
  public $filterBool;


  /*
   * Constructor
   *
   * @param string $indexType  the name of the ElasticSearch document type to search
   * @param array $facets  search facets (see the addFacets method)
   * @param int $limit  how many results should be returned
   * @param int $page  how page of results should be returned
   *
   * @return void
   */
  public function __construct($indexType, $facets, $limit = 50, $page = 1)
  {
    $this->indexType = $indexType;
    $this->facets = $facets;

    $page = (isset($page)) ? $page : 1;

    $this->query = new \Elastica\Query();

    if ($limit)
    {
      $this->query->setLimit($limit);
    }

    $this->query->setFrom(($page - 1) * $limit);

    $this->queryBool = new \Elastica\Query\Bool();
    $this->filterBool = new \Elastica\Filter\Bool;

    $this->addFacets();
  }

  /*
   * Translate internal representation of facets to Elastica API, adding
   * to query.
   *
   * @return void
   */
  public function addFacets()
  {
    foreach ($this->facets as $name => $item)
    {
      if (!is_array($item))
      {
        $facet = new \Elastica\Facet\Terms($item);
        $facet->setField($item);
        $facet->setSize(10);

        $this->query->addFacet($facet);

        continue;
      }

      switch ($item['type'])
      {
        case 'range':
          $facet = new \Elastica\Facet\Range($name);
          $facet->setField($item['field']);
          $facet->addRange($item['from'], $item['to']);

          break;

        case 'term':
          $facet = new \Elastica\Facet\Terms($name);
          $facet->setField($item['field']);

          break;

        case 'query':
          $facet = new \Elastica\Facet\Query($name);
          $facet->setQuery(new \Elastica\Query\Term($item['field']));

          break;
      }

      // Sets the amount of terms to be returned
      if (isset($item['size']))
      {
        $facet->setSize($item['size']);
      }

      $filter = new \Elastica\Filter\Bool;

      // Sets a filter for this facet
      if (isset($item['filter']))
      {
        switch ($item['filter'])
        {
          case 'hideDrafts':
            QubitAclSearch::filterDrafts($filter);
            break;
        }
      }

      // Apply facet filter if exists
      if (0 < count($filter->toArray()))
      {
        $facet->setFilter($filter);
      }

      $this->query->addFacet($facet);
    }
  }

  /*
   * Add filters to search
   *
   * @return void
   */
  public function addFilters($params)
  {
    $this->filters = array();

    // Filter languages only if the languages facet is being used and languages is
    // set in the request
    if (isset($this->facets['languages']) && isset($this->request->languages))
    {
      $this->filters['languages'] = $this->request->languages;
      $term = new \Elastica\Filter\Term(array($this->facets['languages']['field'] => $this->request->languages));

      $this->filterBool->addMust($term);
    }

    // Add facet selections as search criteria
    foreach ($params as $param => $value)
    {
      if ('languages' == $param
        || !array_key_exists($param, $this->facets)
        || ('repos' == $param && (!ctype_digit($value)
        || null === QubitRepository::getById($value))))
      {
        continue;
      }

      foreach (explode(',', $value) as $facetValue)
      {
        // Don't include empty filters
        if (1 === preg_match('/^[\s\t\r\n]*$/', $facetValue))
        {
          continue;
        }

        $this->filters[$param][] = $facetValue;

        $term = new \Elastica\Query\Term(array($this->facets[$param]['field'] => $facetValue));

        $this->queryBool->addMust($term);
      }
    }
  }
}
