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

class TermIndexAction extends DefaultBrowseAction
{
  const INDEX_TYPE = 'QubitInformationObject';

  // Arrays not allowed in class constants
  public static
    $AGGS = array(
      'languages' =>
        array('type'  => 'term',
              'field' => 'i18n.languages',
              'size'  => 10),
      'places' =>
        array('type'  => 'term',
              'field' => 'places.id',
              'size'  => 10),
      'subjects' =>
        array('type'  => 'term',
              'field' => 'subjects.id',
              'size'  => 10),
      'genres' =>
        array('type'  => 'term',
              'field' => 'genres.id',
              'size'  => 10),
      'direct' =>
        array('type' => 'filter',
              'field'  => '',
              'populate' => false));

  protected function populateAgg($name, $buckets)
  {
    switch ($name)
    {
      case 'places':
      case 'subjects':
      case 'genres':
        $ids = array_column($buckets, 'key');
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $buckets[array_search($item->id, $ids)]['display'] = $item->getName(array('cultureFallback' => true));
        }

        break;

      default:
        return parent::populateAgg($name, $buckets);
    }

    return $buckets;
  }

  public function checkForRepeatedNames($validator, $value)
  {
    $criteria = new Criteria;
    $criteria->add(QubitTerm::ID, $this->resource->id, Criteria::NOT_EQUAL);
    $criteria->add(QubitTerm::TAXONOMY_ID, $this->resource->taxonomyId);
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::CULTURE, $this->culture);
    $criteria->add(QubitTermI18n::NAME, $value);

    if (0 < intval(BasePeer::doCount($criteria)->fetchColumn(0)))
    {
      throw new sfValidatorError($validator, $this->context->i18n->__('Name - A term with this name already exists.'));
    }
  }

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;
    if (!$this->resource instanceof QubitTerm)
    {
      $this->forward404();
    }

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    if (sfConfig::get('app_enable_institutional_scoping'))
    {
      // Remove search-realm
      $this->context->user->removeAttribute('search-realm');
    }

    if (isset($request->languages))
    {
      $this->culture = $request->languages;
    }
    else
    {
      $this->culture = $this->context->user->getCulture();
    }

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->name = new sfValidatorCallback(array('callback' => array($this, 'checkForRepeatedNames')));
      $values['name'] = $this->resource->getName(array('cultureFallback' => true));

      try
      {
        $validatorSchema->clean($values);
      }
      catch (sfValidatorErrorSchema $e)
      {
        $this->errorSchema = $e;
      }
    }

    // Add browse elements for places and subjects and genres
    $this->addBrowseElements = ($this->resource->taxonomyId == QubitTaxonomy::PLACE_ID || $this->resource->taxonomyId == QubitTaxonomy::SUBJECT_ID || $this->resource->taxonomyId == QubitTaxonomy::GENRE_ID);
    if ($this->addBrowseElements)
    {
      // Return special response in JSON for XHR requests
      if ($request->isXmlHttpRequest())
      {
        $this->loadListTerms($request);

        $total = $this->listResultSet->getTotalHits();
        if (1 > $total)
        {
          $this->forward404();

          return;
        }

        sfContext::getInstance()->getConfiguration()->loadHelpers('Url');

        $response = array('results' => array());
        foreach ($this->listResultSet->getResults() as $item)
        {
          $data = $item->getData();

          $result = array(
            'url' => url_for(array('module' => 'term', 'slug' => $data['slug'])),
            'title' => $data['i18n'][$this->culture]['name']);

          $response['results'][] = $result;
        }

        if ($this->listPager->haveToPaginate())
        {
          $resultCount = $this->context->i18n->__('Results %1% to %2% of %3%', array('%1%' => $this->listPager->getFirstIndice(), '%2%' => $this->listPager->getLastIndice(), '%3%' => $this->listPager->getNbResults()));

          $previous = $next = '';
          if (1 < $this->listPager->getPage())
          {
            $url = url_for(array($this->resource, 'module' => 'term', 'listPage' => $this->listPager->getPage() - 1, 'listLimit' => $request->listLimit));
            $link = '&laquo; ' . $this->context->i18n->__('Previous');

            $previous = <<<EOF
<li class="previous">
  <a href="$url">
    $link
  </a>
</li>
EOF;
          }

          if ($this->listPager->getLastPage() > $this->listPager->getPage())
          {
            $url = url_for(array($this->resource, 'module' => 'term', 'listPage' => $this->listPager->getPage() + 1, 'listLimit' => $request->listLimit));
            $link = $this->context->i18n->__('Next') . ' &raquo;';

            $next = <<<EOF
<li class="next">
  <a href="$url">
    $link
  </a>
</li>
EOF;
          }

          $response['more'] = <<<EOF
<section>
  <div class="result-count">
    $resultCount
  </div>
  <div>
    <div class="pager">
      <ul>
        $previous
        $next
      </ul>
    </div>
  </div>
</section>
EOF;
        }

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode($response));
      }
      // Not XHR requests
      else
      {
        switch ($this->resource->taxonomyId)
        {
          case QubitTaxonomy::PLACE_ID:
            $query = new \Elastica\Query\Terms('places.id', array($this->resource->id));
            $this::$AGGS['direct']['field'] = array('directPlaces' => $this->resource->id);

            if (isset($request->onlyDirect))
            {
              $queryDirect = new \Elastica\Query\Terms('directPlaces', array($this->resource->id));
            }

            break;

          case QubitTaxonomy::SUBJECT_ID:
            $query = new \Elastica\Query\Terms('subjects.id', array($this->resource->id));
            $this::$AGGS['direct']['field'] = array('directSubjects' => $this->resource->id);

            if (isset($request->onlyDirect))
            {
              $queryDirect = new \Elastica\Query\Terms('directSubjects', array($this->resource->id));
            }

            break;

          case QubitTaxonomy::GENRE_ID:
            $query = new \Elastica\Query\Terms('genres.id', array($this->resource->id));
            $this::$AGGS['direct']['field'] = array('directGenres' => $this->resource->id);

            if (isset($request->onlyDirect))
            {
              $queryDirect = new \Elastica\Query\Terms('directGenres', array($this->resource->id));
            }

            break;
        }

        parent::execute($request);

        $this->search->queryBool->addMust($query);

        if(isset($queryDirect))
        {
          $this->search->queryBool->addMust($queryDirect);
        }

        switch ($request->sort)
        {
          case 'referenceCode':
            $this->search->query->setSort(array('referenceCode.untouched' => 'asc'));
            break;

          case 'alphabetic':
            $field = sprintf('i18n.%s.title.untouched', $this->culture);
            $this->search->query->setSort(array($field => 'asc'));
            break;

          case 'date':
            $this->search->query->setSort(array('dates.startDate' => 'asc'));
            break;

          case 'lastUpdated':
          default:
            $this->search->query->setSort(array('updatedAt' => 'desc'));
        }

        QubitAclSearch::filterDrafts($this->search->queryBool);
        $this->search->query->setQuery($this->search->queryBool);

        $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

        // Page results
        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($this->limit);
        $this->pager->init();

        $this->populateAggs($resultSet);

        // Load list terms
        $this->loadListTerms($request);
      }
    }
  }

  protected function loadListTerms($request)
  {
    if (!isset($request->listLimit))
    {
      $request->listLimit = sfConfig::get('app_hits_per_page');
    }

    $listQuery = new \Elastica\Query();
    $listQuery->setSize($request->listLimit);
    $listQuery->setSort(array(sprintf('i18n.%s.name.untouched', $this->culture) => 'asc'));

    if (!empty($request->listPage))
    {
      $listQuery->setFrom(($request->listPage - 1) * $request->listLimit);
    }

    $listQueryBool = new \Elastica\Query\BoolQuery;
    $listQueryBool->addMust(new \Elastica\Query\Term(array('taxonomyId' => $this->resource->taxonomyId)));

    $listQuery->setQuery($listQueryBool);
    $this->listResultSet = QubitSearch::getInstance()->index->getType('QubitTerm')->search($listQuery);

    // Page list results
    $this->listPager = new QubitSearchPager($this->listResultSet);
    $this->listPager->setPage($request->listPage ? $request->listPage : 1);
    $this->listPager->setMaxPerPage($request->listLimit);
    $this->listPager->init();
  }
}
