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
  const INDEX_TYPE = 'QubitInformationObject';

  // Arrays not allowed in class constants
  public static
    $FACETS = array(
      'languages' =>
        array('type' => 'term',
              'field' => 'i18n.languages',
              'filter' => 'hideDrafts',
              'size' => 10),
      'levels' =>
        array('type' => 'term',
              'field' => 'levelOfDescriptionId',
              'filter' => 'hideDrafts',
              'size' => 10),
      'mediatypes' =>
        array('type' => 'term',
              'field' => 'digitalObject.mediaTypeId',
              'filter' => 'hideDrafts',
              'size' => 10),
      'digitalobjects' =>
        array('type' => 'query',
              'field' => array('hasDigitalObject' => true),
              'filter' => 'hideDrafts',
              'populate' => false),
      'repos' =>
        array('type' => 'term',
              'field' => 'repository.id',
              'filter' => 'hideDrafts',
              'size' => 10),
      'places' =>
        array('type'   => 'term',
              'field'  => 'places.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'subjects' =>
        array('type'   => 'term',
              'field'  => 'subjects.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'genres' =>
        array('type'   => 'term',
              'field'  => 'genres.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'creators' =>
        array('type'   => 'term',
              'field'  => 'creators.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'names' =>
        array('type'   => 'term',
              'field'  => 'names.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'collection' =>
        array('type'   => 'term',
              'field'  => 'partOf.id',
              'size'   => 10),
      'toplevel' =>
        array('type'   => 'query',
              'field' => array('parentId' => QubitInformationObject::ROOT_ID),
              'filter' => 'hideDrafts',
              'populate' => false));

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
      case 'genres':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'creators':
      case 'names':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'collection':
        $criteria = new Criteria;
        $criteria->add(QubitInformationObject::ID, array_keys($ids), Criteria::IN);

        foreach (QubitInformationObject::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      default:
        parent::populateFacet($name, $ids);
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    // Print noResults template if query is empty
    if (empty($request->query))
    {
      $this->setTemplate('noResults');

      return sfView::SUCCESS;
    }

    $queryText = new \Elastica\Query\QueryString($request->query);
    $queryText->setDefaultOperator('OR');
    arElasticSearchPluginUtil::setAllFields($queryText, 'informationObject');

    $this->search->queryBool->addMust($queryText);

    // Realm filter
    if (isset($request->repos) && ctype_digit($request->repos) && null !== $this->repos = QubitRepository::getById($request->repos))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $request->repos)));

      // Store realm in user session
      $this->context->user->setAttribute('search-realm', $request->repos);
    }

    if (isset($request->collection) && ctype_digit($request->collection))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('ancestors' => $request->collection)));
    }

    if (isset($request->onlyMedia))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('hasDigitalObject' => true)));
    }

    $this->handleTopLevelDescriptionsOnlyFilter();
    $this->search->query->setQuery($this->search->queryBool);

    // Add suggestion
    // Using setParam since Elastica does not support the suggest API yet
    $this->search->query->setParam('suggest', array(
      'text' => $request->query,
      'suggestions' => array(
        'term' => array(
          'size' => 1,
          'sort' => 'frequency',
          'field' => sprintf('i18n.%s.title', $this->selectedCulture)))));

    // Filter drafts
    QubitAclSearch::filterDrafts($this->search->filterBool);

    // Set filter
    if (0 < count($this->search->filterBool->toArray()))
    {
      $this->search->query->setFilter($this->search->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

    // Return special response in JSON for XHR requests
    if ($request->isXmlHttpRequest() && ctype_digit($request->collection))
    {
      $total = $resultSet->getTotalHits();
      if (1 > $total)
      {
        $this->forward404();

        return;
      }

      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Escaping'));

      $response = array('results' => array());
      foreach ($resultSet->getResults() as $item)
      {
        $data = $item->getData();
        $levelOfDescription = QubitTerm::getById($data['levelOfDescriptionId']);

        $result = array(
          'url' => url_for(array('module' => 'informationobject', 'slug' => $data['slug'])),
          'title' => esc_specialchars($data['i18n'][$this->context->user->getCulture()]['title']),
          'identifier' => isset($data['identifier']) && !empty($data['identifier']) ? esc_specialchars($data['identifier']).' - ' : '',
          'level' => null !== $levelOfDescription ? esc_specialchars($levelOfDescription->getName()) : '');

        $response['results'][] = $result;
      }

      $url = url_for(array('module' => 'informationobject', 'action' => 'browse', 'collection' =>  $request->collection, 'query' => $request->query, 'topLod' => '0'));
      $link = $this->context->i18n->__('Browse all descriptions');
      $response['more'] = <<<EOF
<div class="more">
  <a href="$url">
    <i class="icon-search"></i>
    $link
  </a>
</div>
EOF;

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
