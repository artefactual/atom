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
    $view = sfView::NONE;

    try
    {
      $view = $this->process($request);
    }
    catch (QubitApi404Exception $e)
    {
      $this->response->setStatusCode(404, $e->getMessage());
    }
    catch (QubitApiException $e)
    {
      $this->response->setStatusCode($e->getCode(), $e->getMessage());
    }
    catch (Exception $e)
    {
      $this->response->setStatusCode(500);

      throw $e;
    }

    return $view;
  }

  public function process($request)
  {
    $method = strtoupper($request->getMethod());
    if (!method_exists($this, $method))
    {
      return $this->forward404();
    }

    // Define function callable
    $fnCallable = array($this, $method);
    $fnParamaters = array($request);

    // Modern frameworks support application/json, Symfony1 is too old :)
    // AngularJS doesn't use application/x-www-form-urlencoded
    if (('PUT' == $method || 'POST' == $method) && 'application/json' == $request->getContentType())
    {
      $fnParamaters[] = json_decode($request->getContent());
    }

    // Load Qubit helper before calling it
    ProjectConfiguration::getActive()->loadHelpers(array('Asset', 'Qubit'));

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

  protected function prepareEsPagination(\Elastica\Query &$query, $limit = 10)
  {
    // Limit
    if (isset($this->request->limit) && ctype_digit($this->request->limit))
    {
      $limit = $this->request->limit;
      if ($limit > 100)
      {
        $limit = 100;
      }
    }
    else
    {
      $limit = sfConfig::get('app_hits_per_page', 10);
    }

    // Skip
    if (isset($this->request->skip) && ctype_digit($this->request->skip))
    {
      $query->setFrom($this->request->skip);
    }
  }

  protected function prepareEsSorting(\Elastica\Query &$query, $fields = array())
  {
    // Stop if preferred option is not set or $fields empty
    if (1 > count($fields) || !isset($this->request->sort))
    {
      return;
    }

    // Stop if the preferred option can't be found
    if (false === array_search($this->request->sort, array_keys($fields)))
    {
      return;
    }

    $sortDirection = 'asc';
    if (isset($this->request->sort_direction))
    {
      if ('desc' == $this->request->sort_direction)
      {
        $sortDirection = 'desc';
      }
    }

    // TODO: allow $request->sort to be multi-value
    $query->setSort(array($fields[$this->request->sort] => $sortDirection));
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

  protected function addItemToArray(&$array, $key, $value)
  {
    if (empty($value))
    {
      return;
    }

    $array[$key] = $value;
  }
}
