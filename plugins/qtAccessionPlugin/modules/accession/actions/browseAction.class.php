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

class AccessionBrowseAction extends sfAction
{
    public function execute($request)
    {
        $title = $this->context->i18n->__(ucfirst($this->context->getModuleName()));
        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        if (!isset($request->page)) {
            $request->page = 1;
        }

        // Avoid pagination over ES' max result window config (default: 10000)
        $maxResultWindow = arElasticSearchPluginConfiguration::getMaxResultWindow();

        if ((int) $request->limit * (int) $request->page > $maxResultWindow) {
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

        $this->sortOptions = [
            'lastUpdated' => $this->context->i18n->__('Date modified'),
            'accessionNumber' => $this->context->i18n->__('Accession number'),
            'title' => $this->context->i18n->__('Title'),
            'acquisitionDate' => $this->context->i18n->__('Acquisition date'),
        ];

        if (!isset($request->sort)) {
            if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->subquery)) {
                $request->sort = 'relevance';
            } elseif ($this->getUser()->isAuthenticated()) {
                $request->sort = sfConfig::get('app_sort_browser_user');
            } else {
                $request->sort = sfConfig::get('app_sort_browser_anonymous');
            }
        }

        // Default sort direction
        $sortDir = 'asc';
        if ('lastUpdated' == $request->sort) {
            $sortDir = 'desc';
        }

        // Set default sort direction in request if not present or not valid
        if (!isset($request->sortDir) || !in_array($request->sortDir, ['asc', 'desc'])) {
            $request->sortDir = $sortDir;
        }

        $culture = $this->context->user->getCulture();

        $this->query = new \Elastica\Query();
        $this->query->setSize($request->limit);
        $this->query->setFrom(($request->page - 1) * $request->limit);

        $this->queryBool = new \Elastica\Query\BoolQuery();

        if (1 === preg_match('/^[\s\t\r\n]*$/', $request->subquery)) {
            $this->queryBool->addMust(new \Elastica\Query\MatchAll());
        } else {
            $fields = [
                'identifier' => 10,
                'donors.i18n.%s.authorizedFormOfName' => 10,
                'i18n.%s.title' => 10,
                'i18n.%s.scopeAndContent' => 10,
                'i18n.%s.locationInformation' => 5,
                'i18n.%s.processingNotes' => 5,
                'i18n.%s.sourceOfAcquisition' => 5,
                'i18n.%s.archivalHistory' => 5,
                'i18n.%s.appraisal' => 1,
                'i18n.%s.physicalCharacteristics' => 1,
                'i18n.%s.receivedExtentUnits' => 1,
                'alternativeIdentifiers.i18n.%s.name' => 1,
                'creators.i18n.%s.authorizedFormOfName' => 1,
                'alternativeIdentifiers.i18n.%s.note' => 1,
                'alternativeIdentifiers.type.i18n.%s.name' => 1,
                'accessionEvents.i18n.%s.agent' => 1,
                'accessionEvents.type.i18n.%s.name' => 1,
                'accessionEvents.notes.i18n.%s.content' => 1,
                'donors.contactInformations.contactPerson' => 1,
                'accessionEvents.dateString' => 1,
            ];

            $this->queryBool->addMust(
                arElasticSearchPluginUtil::generateQueryString(
                    $request->subquery, $fields
                )
            );

            $this->sortOptions['relevance'] = $this->context->i18n->__('Relevance');
        }

        // Set query
        $this->query->setQuery($this->queryBool);

        // Set order
        switch ($request->sort) {
            case 'identifier': // For backward compatibility
            case 'accessionNumber':
                $this->query->setSort(['identifier.untouched' => $request->sortDir]);

                break;

            case 'title':
            case 'alphabetic': // For backward compatibility
                $field = sprintf('i18n.%s.title.alphasort', $this->context->user->getCulture());
                $this->query->addSort([$field => $request->sortDir]);

                break;

            case 'acquisitionDate':
                $this->query->addSort(['date' => ['order' => $request->sortDir, 'missing' => '_last']]);

                break;

            case 'relevance':
                // Keep boost options
                break;

            case 'lastUpdated':
            default:
                $this->query->setSort(['updatedAt' => $request->sortDir]);

                break;
        }

        $resultSet = QubitSearch::getInstance()->index->getIndex('QubitAccession')->search($this->query);

        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->init();
    }
}
