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

/**
 * Use _msearch to query ES multiple times at once, using query_string.
 */
class SearchAutocompleteAction extends sfAction
{
    public function execute($request)
    {
        // Store user query string, erase wildcards
        $this->queryString = strtr($request->query, ['*' => '', '?' => '']);

        // If the query is empty, don't query and return blank response
        if (1 === preg_match('/^[\s\t\r\n]*$/', $this->queryString)) {
            return sfView::NONE;
        }

        // Should I be doing this in ES with search_analyzer?
        $this->queryString = mb_strtolower($this->queryString);

        // Current culture
        $culture = $this->context->user->getCulture();

        $client = QubitSearch::getInstance()->client;

        // Multisearch object
        $mSearch = new \Elastica\Multi\Search($client);

        $items = [
            [
                'type' => 'qubitinformationobject',
                'field' => sprintf('i18n.%s.title', $culture),
                'fields' => ['slug', sprintf('i18n.%s.title', $culture), 'levelOfDescriptionId'],
            ],
            [
                'type' => 'qubitrepository',
                'field' => sprintf('i18n.%s.authorizedFormOfName', $culture),
                'fields' => ['slug', sprintf('i18n.%s.authorizedFormOfName', $culture)],
            ],
            [
                'type' => 'qubitactor',
                'field' => sprintf('i18n.%s.authorizedFormOfName', $culture),
                'fields' => ['slug', sprintf('i18n.%s.authorizedFormOfName', $culture)],
            ],
            [
                'type' => 'qubitterm',
                'field' => sprintf('i18n.%s.name', $culture),
                'fields' => ['slug', sprintf('i18n.%s.name', $culture)],
                'term_filter' => ['taxonomyId' => QubitTaxonomy::PLACE_ID],
            ],
            [
                'type' => 'qubitterm',
                'field' => sprintf('i18n.%s.name', $culture),
                'fields' => ['slug', sprintf('i18n.%s.name', $culture)],
                'term_filter' => ['taxonomyId' => QubitTaxonomy::SUBJECT_ID],
            ],
        ];

        foreach ($items as $item) {
            $index = QubitSearch::getInstance()->index[$item['type']]->getInstance();
            $search = new \Elastica\Search($client);
            $search->addIndex($index)->addType($index->getType($item['type']));

            $query = new \Elastica\Query();
            $query->setSize(3)->setSource($item['fields']);

            $queryBool = new \Elastica\Query\BoolQuery();

            // Match in autocomplete
            $queryText = new \Elastica\Query\Match();
            $queryText->setFieldQuery($item['field'].'.autocomplete', $this->queryString);
            $queryBool->addMust($queryText);

            // Add term_fitler
            if (isset($item['term_filter']) && is_array($item['term_filter'])) {
                $queryBool->addMust(new \Elastica\Query\Term($item['term_filter']));
            }

            if (isset($request->repos) && ctype_digit($request->repos) && 'qubitinformationobject' == $item['type']) {
                $queryBool->addMust(new \Elastica\Query\Term(['repository.id' => $request->repos]));

                // Store realm in user session
                $this->context->user->setAttribute('search-realm', $request->repos);
            } elseif (sfConfig::get('app_enable_institutional_scoping')) {
                // Remove search-realm
                $this->context->user->removeAttribute('search-realm');
            }

            if ('qubitinformationobject' == $item['type']) {
                QubitAclSearch::filterDrafts($queryBool);
            }

            $query->setQuery($queryBool);
            $search->setQuery($query);
            $mSearch->addSearch($search);
        }

        $resultSets = $mSearch->search();

        $this->descriptions = $resultSets[0];
        $this->repositories = $resultSets[1];
        $this->actors = $resultSets[2];
        $this->places = $resultSets[3];
        $this->subjects = $resultSets[4];

        // Return a blank response if there are no results
        if (0 == $this->descriptions->getTotalHits() + $this->repositories->getTotalHits() + $this->actors->getTotalHits() + $this->places->getTotalHits() + $this->subjects->getTotalHits()) {
            return sfView::NONE;
        }

        // Fix route params for "all matching ..." links, IO browse uses
        // the query param but all the others use subquery
        $this->allMatchingIoParams = $request->getParameterHolder()->getAll();
        $this->allMatchingParams = $this->allMatchingIoParams;
        $this->allMatchingParams['subquery'] = $this->allMatchingParams['query'];
        unset($this->allMatchingParams['query'], $this->allMatchingParams['repos']);

        // Preload levels of descriptions
        if (0 < $this->descriptions->getTotalHits()) {
            $sql = '
                SELECT
                    t.id,
                    ti18n.name
                FROM
                    '.QubitTerm::TABLE_NAME.' AS t
                LEFT JOIN '.QubitTermI18n::TABLE_NAME.' AS ti18n ON (t.id = ti18n.id AND ti18n.culture = ?)
                WHERE
                    t.taxonomy_id = ?
            ';

            $this->levelsOfDescription = [];
            foreach (QubitPdo::fetchAll($sql, [$this->context->user->getCulture(), QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID]) as $item) {
                $this->levelsOfDescription[$item->id] = $item->name;
            }
        }
    }
}
