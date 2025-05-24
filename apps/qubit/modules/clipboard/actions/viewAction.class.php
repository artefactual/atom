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

class ClipboardViewAction extends DefaultBrowseAction
{
    public function execute($request)
    {
        parent::execute($request);

        if ('print' == $request->getGetParameter('media')) {
            $this->getResponse()->addStylesheet('print-preview', 'last');

            // Negate paging when printing
            $maxPerPage = arElasticSearchPluginConfiguration::getMaxResultWindow();
        } else {
            $maxPerPage = $this->limit;
        }

        // Get entity type and class name
        $this->type = $request->getGetParameter('type', 'informationObject');
        $this->entityType = 'Qubit'.ucfirst($this->type);

        $slugs = $request->getPostParameter('slugs', []);

        if (empty($slugs)) {
            $resultSet = new \Elastica\ResultSet(new Elastica\Response(null), new Elastica\Query(), []);
        } else {
            $this->search->queryBool->addMust(new \Elastica\Query\Terms('slug', $slugs));
            $this->setSortOptions();
            $this->setESSort($request);

            if ('QubitInformationObject' == $this->entityType) {
                QubitAclSearch::filterDrafts($this->search->queryBool);
            }

            $this->search->query->setQuery($this->search->queryBool);

            $resultSet = QubitSearch::getInstance()->index->getIndex($this->entityType)->search($this->search->query);
        }

        // Page results
        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($maxPerPage);
        $this->pager->init();

        $this->uiLabels = [
            'informationObject' => sfConfig::get('app_ui_label_informationobject'),
            'actor' => sfConfig::get('app_ui_label_actor'),
            'repository' => sfConfig::get('app_ui_label_repository'),
        ];

        // Remove slugs parameter. In some templates (entity type dropdown
        // for example) the links are generated with all the request params
        // (including POST) which appends the slugs from the Ajax request.
        unset($request['slugs']);
    }

    /**
     * Set available sorting options based on entity type.
     */
    private function setSortOptions()
    {
        $this->sortOptions = [
            'lastUpdated' => $this->context->i18n->__('Date modified'),
            'alphabetic' => $this->context->i18n->__('Name'),
        ];

        // IOs and Repos have identifier sort option in common
        if (in_array($this->entityType, ['QubitInformationObject', 'QubitRepository'])) {
            $this->sortOptions['identifier'] = $this->context->i18n->__('Identifier');
        }

        // IO specific sort options
        if ('QubitInformationObject' === $this->entityType) {
            $this->sortOptions['alphabetic'] = $this->context->i18n->__('Title');
            $this->sortOptions['referenceCode'] = $this->context->i18n->__('Reference code');
            $this->sortOptions['startDate'] = $this->context->i18n->__('Start date');
            $this->sortOptions['endDate'] = $this->context->i18n->__('End date');
        }
    }

    /**
     * Set which field to sort by for current ES query.
     *
     * @param sfRequest $request current request object
     */
    private function setESSort($request)
    {
        // Prevent selecting an inappropriate sort field when switching entity types.
        // e.g.: if we are sorting by start date for archival descriptions, but switch to auth recs we
        // will default to sort by relevance since authority records don't have start dates to sort over.
        $request->sort = isset($this->sortOptions[$request->sort]) ? $request->sort : 'relevance';

        switch ($request->sort) {
            // Sort by highest ES score
            case 'relevance':
                $this->search->query->addSort(['_score' => $request->sortDir]);

                break;

            case 'identifier':
                $this->search->query->addSort(['identifier.untouched' => $request->sortDir]);

                break;

            case 'referenceCode':
                $this->search->query->addSort(['referenceCode.untouched' => $request->sortDir]);

                break;

            // Sort by title if information object, go with authorized form of name if repository / actor
            case 'alphabetic':
                $fieldName = 'QubitInformationObject' === $this->entityType ? 'title' : 'authorizedFormOfName';
                $field = sprintf('i18n.%s.%s.untouched', $this->selectedCulture, $fieldName);
                $this->search->query->addSort([$field => $request->sortDir]);

                break;

            case 'startDate':
                $this->search->query->setSort(['startDateSort' => $request->sortDir]);

                break;

            case 'endDate':
                $this->search->query->setSort(['endDateSort' => $request->sortDir]);

                break;

            case 'lastUpdated':
            default:
                $this->search->query->setSort(['updatedAt' => $request->sortDir]);
        }
    }
}
