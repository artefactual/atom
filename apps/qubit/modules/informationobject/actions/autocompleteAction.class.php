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
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class InformationObjectAutocompleteAction extends sfAction
{
    /**
     * Return all information objects (not just top-level) for ajax request.
     *
     * @param mixed $request
     */
    public function execute($request)
    {
        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        $culture = $this->context->user->getCulture();

        $this->query = new \Elastica\Query();
        $this->query->setSize($request->limit);
        $this->query->setSort([
            'levelOfDescriptionId' => 'asc',
            'identifier.untouched' => 'asc',
            'i18n.'.$culture.'.title.alphasort' => 'asc',
        ]);

        $this->queryBool = new \Elastica\Query\BoolQuery();

        if (1 === preg_match('/^[\s\t\r\n]*$/', $request->query)) {
            $this->queryBool->addMust(new \Elastica\Query\MatchAll());
        } else {
            $fields = ['i18n.'.$culture.'.title.autocomplete' => 1];

            // Search for referenceCode or identifier, and title
            if (1 == sfConfig::get('app_inherit_code_informationobject', 1)) {
                $fields['referenceCode.autocomplete'] = 1;

                // Change sort order
                $this->query->setSort([
                    'levelOfDescriptionId' => 'asc',
                    'referenceCode.untouched' => 'asc',
                    'i18n.'.$culture.'.title.alphasort' => 'asc',
                ]);
            } else {
                $fields['identifier'] = 1;
            }

            $this->queryBool->addMust(
                arElasticSearchPluginUtil::generateQueryString(
                    $request->query, $fields
                )
            );
        }

        // Filter results by parent
        if (!empty($request->parent) && ctype_digit($request->parent)) {
            $queryTerm = new \Elastica\Query\Term();
            $queryTerm->setTerm('parentId', $request->parent);

            $this->queryBool->addMust($queryTerm);
        }

        // Filter results by repository
        if (!empty($request->repository) && ctype_digit($request->repository)) {
            $queryTerm = new \Elastica\Query\Term();
            $queryTerm->setTerm('repository.id', $request->repository);

            $this->queryBool->addMust($queryTerm);
        }

        // Filter drafts
        if (isset($request->filterDrafts) && $request->filterDrafts) {
            QubitAclSearch::filterDrafts($this->queryBool);
        }

        $this->query->setQuery($this->queryBool);

        $resultSet = QubitSearch::getInstance()->index->getIndex('QubitInformationObject')->search($this->query);

        // Page results
        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->init();
    }
}
