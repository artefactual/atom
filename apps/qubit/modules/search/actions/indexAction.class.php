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

/*
 * Notice that his is also used in XHR context (see treeview search)
 */
class SearchIndexAction extends DefaultBrowseAction
{
  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'levels' =>
        array('type' => 'term',
              'field' => 'levelOfDescriptionId',
              'size' => 10),
      'mediatypes' =>
        array('type' => 'term',
              'field' => 'digitalObject.mediaTypeId',
              'size' => 10),
      'digitalobjects' =>
        array('type' => 'query',
              'field' => array('hasDigitalObject' => true),
              'populate' => false),
      'repos' =>
        array('type' => 'term',
              'field' => 'repository.id',
              'size' => 10),
      'places' =>
        array('type'   => 'term',
              'field'  => 'places.id',
              'size'   => 10),
      'subjects' =>
        array('type'   => 'term',
              'field'  => 'subjects.id',
              'size'   => 10),
      'creators' =>
        array('type'   => 'term',
              'field'  => 'creators.id',
              'size'   => 10),
      'names' =>
        array('type'   => 'term',
              'field'  => 'names.id',
              'size'   => 10));

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'levels':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'repos':
        $criteria = new Criteria;
        $criteria->add(QubitRepository::ID, array_keys($ids), Criteria::IN);

        foreach (QubitRepository::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'mediatypes':
      case 'places':
      case 'subjects':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->name;
        }

        break;

      case 'creators':
      case 'names':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->authorizedFormOfName;
        }

        break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    // Print noResults template if query is empty
    if (empty($request->query))
    {
      $this->setTemplate('noResults');

      return sfView::SUCCESS;
    }

    $queryText = new \Elastica\Query\QueryString($request->query);
    $queryText->setDefaultOperator('AND');
    $this->queryBool->addMust($queryText);

    // Realm filter
    if (isset($request->realm) && ctype_digit($request->realm) && null !== $this->realm = QubitRepository::getById($request->realm))
    {
      $this->queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $request->realm)));

      // Store realm in user session
      $this->context->user->setAttribute('search-realm', $request->realm);
    }

    if (isset($request->collection) && ctype_digit($request->collection))
    {
      $this->queryBool->addMust(new \Elastica\Query\Term(array('ancestors' => $request->collection)));
    }

    if (isset($request->onlyMedia))
    {
      $this->queryBool->addMust(new \Elastica\Query\Term(array('hasDigitalObject' => true)));
    }

    $this->query->setQuery($this->queryBool);

    QubitAclSearch::filterDrafts($this->query);

    // Add suggestion
    // Using setParam since Elastica does not support the suggest API yet
    $this->query->setParam('suggest', array(
      'text' => $request->query,
      'suggestions' => array(
        'term' => array(
          'size' => 1,
          'sort' => 'frequency',
          'field' => sprintf('i18n.%s.title', $this->context->user->getCulture())))));

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Return special response in JSON for XHR requests
    if ($request->isXmlHttpRequest() && ctype_digit($request->collection))
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
          'url' => url_for(array('module' => 'informationobject', 'slug' => $data['slug'])),
          'title' => $data['i18n'][$this->context->user->getCulture()]['title']);

        $response['results'][] = $result;
      }

      if ($resultSet->getTotalHits() > $resultSet->count())
      {
        $url = url_for(array('module' => 'informationobject', 'action' => 'browse', 'collection' =>  $request->collection));
        $link = $this->context->i18n->__('Browse %1% holdings', array('%1%' => $resultSet->getTotalHits()));
        $response['more'] = <<<EOF
<div class="more">
  <a href="$url">
    <i class="icon-search"></i>
    $link
  </a>
</div>
EOF;
      }

      $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

      return $this->renderText(json_encode($response));
    }

    // Capture best suggestion
    $esResponse = $resultSet->getResponse()->getData();
    if (isset($esResponse['suggest'])
        && isset($esResponse['suggest']['suggestions'])
        && 0 < count($esResponse['suggest']['suggestions']))
    {
      $firstSuggestion = array_pop($esResponse['suggest']['suggestions']);

      $this->suggestion = array_pop($firstSuggestion['options']);
    }

    if (0 == $resultSet->getTotalHits())
    {
      $this->setTemplate('noResults');

      return sfView::SUCCESS;
    }

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
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
