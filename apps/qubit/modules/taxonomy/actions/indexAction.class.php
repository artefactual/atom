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

class TaxonomyIndexAction extends sfAction
{
  public function execute($request)
  {
    if (sfConfig::get('app_enable_institutional_scoping'))
    {
      // Remove search-realm
      $this->context->user->removeAttribute('search-realm');
    }

    // HACK Use id deliberately, vs. slug, because "Subjects" and "Places"
    // menus still use id
    if (isset($request->id))
    {
      $this->resource = QubitTaxonomy::getById($request->id);
    }
    else
    {
      $this->resource = $this->getRoute()->resource;
    }

    if (!$this->resource instanceof QubitTaxonomy)
    {
      $this->redirect(array('module' => 'taxonomy', 'action' => 'list'));
    }

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if ($this->getUser()->isAuthenticated())
    {
      $this->sortSetting = sfConfig::get('app_sort_browser_user');
    }
    else
    {
      $this->sortSetting = sfConfig::get('app_sort_browser_anonymous');
    }

    if (!isset($request->sort))
    {
      $request->sort = $this->sortSetting;
    }

    $this->resource = $this->getRoute()->resource;

    $this->addResultsColumn = false;

    switch ($this->resource->id)
    {
      case QubitTaxonomy::PLACE_ID:
        $this->icon = 'places';
        $this->addResultsColumn = true;

        break;

      case QubitTaxonomy::SUBJECT_ID:
        $this->icon = 'subjects';
        $this->addResultsColumn = true;

        break;
    }

    $culture = $this->context->user->getCulture();

    $this->query = new \Elastica\Query();
    $this->query->setSize($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new \Elastica\Query\BoolQuery;

    $query = new \Elastica\Query\Term;
    $query->setTerm('taxonomyId', $this->resource->id);
    $this->queryBool->addMust($query);

    if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->subquery))
    {
      $queryString = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($request->subquery));

      switch ($request->subqueryField)
      {
        case 'preferredLabel':
          $queryString->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.name'));

          break;

        case 'useForLabels':
          $queryString->setFields(arElasticSearchPluginUtil::getI18nFieldNames('useFor.i18n.%s.name'));

          break;

        case 'allLabels':
        default:
          // Search over preferred label (boosted by five) and "Use for" labels
          $fields = array('i18n.%s.name', 'useFor.i18n.%s.name');
          $boost = array('i18n.%s.name' => 5);
          $queryString->setFields(arElasticSearchPluginUtil::getI18nFieldNames($fields, null, $boost));
          $queryString->setDefaultOperator('AND');

          break;
      }

      // Filter results by subquery
      $this->queryBool->addMust($queryString);
    }

    // Set query
    $this->query->setQuery($this->queryBool);

    // Set order
    switch ($request->sort)
    {
      // I don't think that this is going to scale, but let's leave it for now
      case 'alphabetic':
        $field = sprintf('i18n.%s.name.untouched', $culture);
        $this->query->setSort(array($field => 'asc'));

        break;

      case 'lastUpdated':
      default:
        $this->query->setSort(array('updatedAt' => 'desc'));
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitTerm')->search($this->query);

    // Return special response in JSON for XHR requests
    if ($request->isXmlHttpRequest())
    {
      $total = $resultSet->getTotalHits();
      if (1 > $total)
      {
        $this->forward404();

        return;
      }

      sfContext::getInstance()->getConfiguration()->loadHelpers('Url');

      $response = array('results' => array());
      foreach ($resultSet->getResults() as $item)
      {
        $data = $item->getData();

        $result = array(
          'url' => url_for(array('module' => 'term', 'slug' => $data['slug'])),
          'title' => $data['i18n'][$culture]['name'],
          'identifier' => '',
          'level' => '');

        $response['results'][] = $result;
      }

      $url = url_for(array($this->resource, 'module' => 'taxonomy', 'subquery' => $request->subquery));
      $link = $this->context->i18n->__('Browse all terms');
      $response['more'] = <<<EOF
<div class="more">
  <a href="$url">
    <i class="fa fa-search"></i>
    $link
  </a>
</div>
EOF;

      $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

      return $this->renderText(json_encode($response));
    }

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
