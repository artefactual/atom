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

// Notice that his is also used in XHR context (see treeview search)
class SearchIndexAction extends DefaultBrowseAction
{
    public const INDEX_TYPE = 'qubitinformationobject';

    public function execute($request)
    {
        parent::execute($request);

        $this->search->queryBool->addMust(
            arElasticSearchPluginUtil::generateQueryString(
                $request->query,
                arElasticSearchPluginUtil::getAllFields('informationObject')
            )
        );

        // Realm filter
        if (isset($request->repos) && ctype_digit($request->repos)) {
            $this->search->queryBool->addMust(new \Elastica\Query\Term(['repository.id' => $request->repos]));

            // Store realm in user session
            $this->context->user->setAttribute('search-realm', $request->repos);
        }

        if (isset($request->collection) && ctype_digit($request->collection)) {
            $this->search->queryBool->addMust(new \Elastica\Query\Term(['ancestors' => $request->collection]));
        }

        QubitAclSearch::filterDrafts($this->search->queryBool);
        $this->search->query->setQuery($this->search->queryBool);

        $resultSet = QubitSearch::getInstance()->index['qubitinformationobject']->search($this->search->query);

        $total = $resultSet->getTotalHits();
        if (1 > $total) {
            $this->forward404();

            return;
        }

        sfContext::getInstance()->getConfiguration()->loadHelpers(['Url', 'Escaping', 'Qubit']);

        $response = ['results' => []];
        foreach ($resultSet->getResults() as $item) {
            $data = $item->getData();
            $levelOfDescription = QubitTerm::getById($data['levelOfDescriptionId']);

            $result = [
                'url' => url_for(['module' => 'informationobject', 'slug' => $data['slug']]),
                'title' => render_title(get_search_i18n($data, 'title', ['allowEmpty' => false])),
                'identifier' => isset($data['identifier']) && !empty($data['identifier']) ? render_value_inline($data['identifier']).' - ' : '',
                'level' => null !== $levelOfDescription ? render_value_inline($levelOfDescription) : '',
            ];

            $response['results'][] = $result;
        }

        if (sfConfig::get('app_enable_institutional_scoping') && $this->context->user->hasAttribute('search-realm')) {
            $url = url_for(['module' => 'informationobject', 'action' => 'browse', 'collection' => $request->collection, 'repos' => $this->context->user->getAttribute('search-realm'), 'query' => $request->query, 'topLod' => '0']);
        } else {
            $url = url_for(['module' => 'informationobject', 'action' => 'browse', 'collection' => $request->collection, 'query' => $request->query, 'topLod' => '0']);
        }

        $link = $this->context->i18n->__('Browse all descriptions');
        $response['more'] = <<<EOF
<div class="more">
  <a href="{$url}">
    <i class="fa fa-search"></i>
    {$link}
  </a>
</div>
EOF;

        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode($response));
    }
}
