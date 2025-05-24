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
        if (sfConfig::get('app_enable_institutional_scoping')) {
            // Remove search-realm
            $this->context->user->removeAttribute('search-realm');
        }

        // HACK Use id deliberately, vs. slug, because "Subjects" and "Places"
        // menus still use id
        if (isset($request->id)) {
            $this->resource = QubitTaxonomy::getById($request->id);
        } else {
            $this->resource = $this->getRoute()->resource;
        }

        // Explicitly add resource to sf_route to make it available to components
        $request->getAttribute('sf_route')->resource = $this->resource;

        // Disallow access to locked taxonomies
        if (in_array($this->resource->id, QubitTaxonomy::$lockedTaxonomies)) {
            $this->getResponse()->setStatusCode(403);

            return sfView::NONE;
        }

        if (!$this->resource instanceof QubitTaxonomy) {
            $this->redirect(['module' => 'taxonomy', 'action' => 'list']);
        }

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            $this->forward404();
        }

        // Restrict access (except to places and subject taxonomies)
        $unrestrictedTaxonomies = [QubitTaxonomy::GENRE_ID, QubitTaxonomy::PLACE_ID, QubitTaxonomy::SUBJECT_ID];
        $allowedGroups = [QubitAclGroup::EDITOR_ID, QubitAclGroup::ADMINISTRATOR_ID];

        if (
            !in_array($this->resource->id, $unrestrictedTaxonomies)
            && !$this->context->user->hasGroup($allowedGroups)
        ) {
            $this->getResponse()->setStatusCode(403);

            return sfView::HEADER_ONLY;
        }

        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        if (!isset($request->page)) {
            $request->page = 1;
        }

        // Avoid pagination over ES' max result window config (default: 10000)
        $maxResultWindow = arElasticSearchPluginConfiguration::getMaxResultWindow();

        if ((int) $request->limit * (int) $request->page > $maxResultWindow) {
            // Don't show alert or redirect in XHR requests made
            // from the list tab in the terms index page. It requires
            // to go one by one to the page over 10,000 records.
            // Returning nothing doesn't break the list but it doesn't
            // show any notice.
            if ($request->isXmlHttpRequest()) {
                return;
            }

            // Show alert
            $message = $this->context->i18n->__(
                "We've redirected you to the first page of results. To avoid using vast amounts of memory, AtoM limits pagination to %1% records. To view the last records in the current result set, try changing the sort direction.",
                ['%1%' => $maxResultWindow]
            );
            $this->getUser()->setFlash('notice', $message);

            // Redirect to first page
            $params = $request->getParameterHolder()->getAll();
            unset($params['page']);
            $this->redirect($params);
        }

        if ($this->getUser()->isAuthenticated()) {
            $this->sortSetting = sfConfig::get('app_sort_browser_user');
        } else {
            $this->sortSetting = sfConfig::get('app_sort_browser_anonymous');
        }

        if (!isset($request->sort)) {
            $request->sort = $this->sortSetting;
        }

        // Default sort direction
        $sortDir = 'asc';
        if (in_array($request->sort, ['lastUpdated', 'relevance'])) {
            $sortDir = 'desc';
        }

        // Set default sort direction in request if not present or not valid
        if (!isset($request->sortDir) || !in_array($request->sortDir, ['asc', 'desc'])) {
            $request->sortDir = $sortDir;
        }

        $this->addIoCountColumn = false;
        $this->addActorCountColumn = false;

        switch ($this->resource->id) {
            case QubitTaxonomy::PLACE_ID:
                $this->icon = 'places';
                $this->addIoCountColumn = true;
                $this->addActorCountColumn = true;

                if (sfConfig::get('app_b5_theme', false)) {
                    $this->icon = 'map-marker-alt';
                }

                $title = $this->context->i18n->__('Places');
                $this->response->setTitle("{$title} - {$this->response->getTitle()}");

                break;

            case QubitTaxonomy::SUBJECT_ID:
                $this->icon = 'subjects';
                $this->addIoCountColumn = true;
                $this->addActorCountColumn = true;

                $title = $this->context->i18n->__('Subjects');
                $this->response->setTitle("{$title} - {$this->response->getTitle()}");

                if (sfConfig::get('app_b5_theme', false)) {
                    $this->icon = 'tag';
                }

                break;

            case QubitTaxonomy::GENRE_ID:
                $this->addIoCountColumn = true;
                $this->addActorCountColumn = true;

                $title = $this->context->i18n->__('Genres');
                $this->response->setTitle("{$title} - {$this->response->getTitle()}");

                break;

            default:
                $title = $this->context->i18n->__(ucwords(str_replace('-', ' ', $this->request->slug)));
                $this->response->setTitle("{$title} - {$this->response->getTitle()}");
        }

        $culture = $this->context->user->getCulture();

        $this->query = new \Elastica\Query();
        $this->query->setSize($request->limit);
        $this->query->setFrom(($request->page - 1) * $request->limit);

        $this->queryBool = new \Elastica\Query\BoolQuery();

        $query = new \Elastica\Query\Term();
        $query->setTerm('taxonomyId', $this->resource->id);
        $this->queryBool->addMust($query);

        if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->subquery)) {
            switch ($request->subqueryField) {
                case 'preferredLabel':
                    $fields = ['i18n.%s.name' => 1];

                    break;

                case 'useForLabels':
                    $fields = ['useFor.i18n.%s.name' => 1];

                    break;

                case 'allLabels':
                default:
                    // Search over preferred label (boosted by five) and "Use for" labels
                    $fields = ['i18n.%s.name' => 5, 'useFor.i18n.%s.name' => 1];

                    break;
            }

            // Filter results by subquery
            $this->queryBool->addMust(
                arElasticSearchPluginUtil::generateQueryString(
                    $request->subquery, $fields
                )
            );
        }

        // Set query
        $this->query->setQuery($this->queryBool);

        // Set order
        switch ($request->sort) {
            // I don't think that this is going to scale, but let's leave it for now
            case 'alphabetic':
                $field = sprintf('i18n.%s.name.alphasort', $culture);
                $this->query->setSort([$field => $request->sortDir]);

                break;

            case 'lastUpdated':
            default:
                $this->query->setSort(['updatedAt' => $request->sortDir]);
        }

        $resultSet = QubitSearch::getInstance()->index->getIndex('QubitTerm')->search($this->query);

        // Return special response in JSON for XHR requests
        if ($request->isXmlHttpRequest()) {
            $total = $resultSet->getTotalHits();
            if (1 > $total) {
                $this->forward404();

                return;
            }

            sfContext::getInstance()->getConfiguration()->loadHelpers(['Url', 'Qubit']);

            $response = ['results' => []];
            foreach ($resultSet->getResults() as $item) {
                $data = $item->getData();

                $result = [
                    'url' => url_for(['module' => 'term', 'slug' => $data['slug']]),
                    'title' => render_title(get_search_i18n($data, 'name')),
                    'identifier' => '',
                    'level' => '',
                ];

                $response['results'][] = $result;
            }

            $url = url_for([$this->resource, 'module' => 'taxonomy', 'subquery' => $request->subquery]);
            $link = $this->context->i18n->__('Browse all terms');
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

        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->init();
    }
}
