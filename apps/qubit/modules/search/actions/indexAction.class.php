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

  public function execute($request)
  {
    parent::execute($request);

    $queryText = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($request->query));
    $queryText->setDefaultOperator('AND');
    arElasticSearchPluginUtil::setFields($queryText, 'informationObject');

    $this->search->queryBool->addMust($queryText);

    // Realm filter
    if (isset($request->repos) && ctype_digit($request->repos))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $request->repos)));

      // Store realm in user session
      $this->context->user->setAttribute('search-realm', $request->repos);
    }

    if (isset($request->collection) && ctype_digit($request->collection))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('ancestors' => $request->collection)));
    }

    QubitAclSearch::filterDrafts($this->search->queryBool);
    $this->search->query->setQuery($this->search->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

    $total = $resultSet->getTotalHits();
    if (1 > $total)
    {
      $this->forward404();

      return;
    }

    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url', 'Escaping', 'Qubit'));

    $response = array('results' => array());
    foreach ($resultSet->getResults() as $item)
    {
      $data = $item->getData();
      $levelOfDescription = QubitTerm::getById($data['levelOfDescriptionId']);

      $result = array(
        'url' => url_for(array('module' => 'informationobject', 'slug' => $data['slug'])),
        'title' => esc_specialchars(get_search_i18n($data, 'title', array('allowEmpty' => false))),
        'identifier' => isset($data['identifier']) && !empty($data['identifier']) ? esc_specialchars($data['identifier']).' - ' : '',
        'level' => null !== $levelOfDescription ? esc_specialchars($levelOfDescription->getName()) : '');

      $response['results'][] = $result;
    }

    if (sfConfig::get('app_enable_institutional_scoping') && $this->context->user->hasAttribute('search-realm'))
    {
      $url = url_for(array('module' => 'informationobject', 'action' => 'browse', 'collection' =>  $request->collection, 'repos' => $this->context->user->getAttribute('search-realm'), 'query' => $request->query, 'topLod' => '0'));
    }
    else
    {
      $url = url_for(array('module' => 'informationobject', 'action' => 'browse', 'collection' =>  $request->collection, 'query' => $request->query, 'topLod' => '0'));
    }

    $link = $this->context->i18n->__('Browse all descriptions');
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
}
