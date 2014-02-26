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

class QubitAPIAction extends sfAction
{
  public function execute($request)
  {
    $method = strtoupper($request->getMethod());
    if (!method_exists($this, $method))
    {
      $this->forward404();
    }

    // Define function callable
    $fnCallable = array($this, $method);
    $fnParamaters = array($request);

    // Modern frameworks support application/json, Symfony1 is too old :)
    // AngularJS uses application/x-www-form-urlencoded
    if ('POST' == $method && 'application/json' == $request->getContentType())
    {
      $fnParamaters[] = json_decode($request->getContent());
    }

    $result = call_user_func_array($fnCallable, $fnParamaters);

    return $this->renderData($result);
  }

  public function renderData($data)
  {
    if (count($data) == 0)
    {
      $this->response->setHeaderOnly(true);

      return sfView::NONE;
    }

    $options = 0;
    if ($this->context->getConfiguration()->isDebug() && defined('JSON_PRETTY_PRINT'))
    {
      $options |= JSON_PRETTY_PRINT;
    }

    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    return $this->renderText(json_encode($data, $options));
  }

  public function forward404($message = false, $data = array())
  {
    if (false !== $message)
    {
      $this->response->setStatusCode(404, $message);
    }
    else
    {
      $this->response->setStatusCode(404);
    }

    return $this->renderData($data);
  }

  public function forwardError()
  {
    $this->response->setStatusCode(500);
  }

  /**
   * Filter out selected facets. It uses \Elastica\Query instead of
   * \Elastica\Filter because the former happens before faceting while the
   * latter happens after faceting.
   */
  protected function filterEsFacet($name, $field, \Elastica\Query\Bool &$queryBool, $operator = 'AND', array $options = array())
  {
    if (!isset($this->request->$name))
    {
      return;
    }

    // Ensure type array
    $this->request->$name = (array) $this->request->$name;

    // Check type of the elements in the array
    foreach ($this->request->$name as $item)
    {
      if (true !== ctype_digit($item))
      {
        return;
      }
    }

    $query = new \Elastica\Query\Terms;
    $query->setTerms($field, $this->request->$name);

    switch (strtolower($operator))
    {
      case 'or':
      case 'should':
        $queryBool->addShould($query);

        break;

      case 'nor':
      case 'not':
      case 'must_not':
        $queryBool->addMustNot($query);

        break;

      case 'and':
      case 'must':
      default:
        $queryBool->addMust($query);
    }
  }

  protected function prepareEsQueryBasic(\Elastica\Query &$query, $sortField, $sortDirection = 'asc', $limit = 10)
  {
    if (!isset($this->request->sort))
    {
      $this->request->sort = $sortField;
    }

    if (!isset($this->request->sort_direction))
    {
      $this->request->sort_direction = $sortDirection;
    }

    $query->setSort(array($this->request->sort => $this->request->sort_direction));

    // Limit
    if (isset($this->request->limit) && ctype_digit($this->request->limit))
    {
      $query->setLimit($this->request->limit);
    }
    else
    {
      $query->setLimit(10);
    }

    // Skip
    if (isset($this->request->skip) && ctype_digit($this->request->skip))
    {
      $query->setFrom($this->request->skip);
    }
  }

  protected function facetEsQuery($facetType, $name, $field, \Elastica\Query &$query, array $options = array())
  {
    $className = '\\Elastica\\Facet\\'.$facetType;

    $facet = new $className($name);

    switch ($facetType)
    {
      case 'Terms':
        $facet->setField($field);
        $facet->setSize(10);

        break;

      case 'TermsStats':
        $facet->setKeyField($field);
        $facet->setValueField($options['valueField']);

        break;
    }

    $query->addFacet($facet);
  }

  protected function populateFacets(&$facets)
  {
    foreach ($facets as $name => &$facet)
    {
      foreach ($facet['terms'] as &$item)
      {
        if (method_exists($this, 'getFacetLabel') && null !== $label = $this->getFacetLabel($name, $item['term']))
        {
          $item['label'] = $label;
        }
      }
    }
  }
}
