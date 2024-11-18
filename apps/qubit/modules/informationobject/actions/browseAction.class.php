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
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class InformationObjectBrowseAction extends DefaultBrowseAction
{
    public const INDEX_TYPE = 'QubitInformationObject';

    // Arrays not allowed in class constants
    public static $NAMES = [
        'copyrightStatus',
        'onlyMedia',
        'levels',
        'materialType',
        'repos',
        'collection',
        'startDate',
        'endDate',
        'topLod',
        'rangeType',
        'findingAidStatus',
    ];
    public static $FILTERTAGS = [
        'repos' => ['model' => 'QubitRepository'],
        'collection' => ['model' => 'QubitInformationObject'],
        'creators' => ['model' => 'QubitActor'],
        'names' => ['model' => 'QubitActor'],
        'places' => ['model' => 'QubitTerm'],
        'levels' => ['model' => 'QubitTerm'],
        'subjects' => ['model' => 'QubitTerm'],
        'mediatypes' => ['model' => 'QubitTerm'],
        'copyrightStatus' => ['model' => 'QubitTerm'],
        'materialType' => ['model' => 'QubitTerm'],
        'onlyMedia' => [],
        'languages' => [],
        'dateRange' => ['params' => ['startDate', 'endDate'], 'operator' => 'or'],
        'findingAidStatus' => [],
        'ancestor' => ['model' => 'QubitInformationObject'],
    ];
    public static $AGGS = [
        'languages' => [
            'type' => 'term',
            'field' => 'i18n.languages',
            'size' => 10,
        ],
        'levels' => [
            'type' => 'term',
            'field' => 'levelOfDescriptionId',
            'size' => 10,
        ],
        'mediatypes' => [
            'type' => 'term',
            'field' => 'digitalObject.mediaTypeId',
            'size' => 10,
        ],
        'digitalobjects' => [
            'type' => 'filter',
            'field' => ['hasDigitalObject' => true],
            'populate' => false,
        ],
        'repos' => [
            'type' => 'term',
            'field' => 'repository.id',
            'size' => 10,
        ],
        'places' => [
            'type' => 'term',
            'field' => 'places.id',
            'size' => 10,
        ],
        'subjects' => [
            'type' => 'term',
            'field' => 'subjects.id',
            'size' => 10,
        ],
        'genres' => [
            'type' => 'term',
            'field' => 'genres.id',
            'size' => 10,
        ],
        'creators' => [
            'type' => 'term',
            'field' => 'creators.id',
            'size' => 10,
        ],
        'names' => [
            'type' => 'term',
            'field' => 'names.id',
            'size' => 10,
        ],
        'collection' => [
            'type' => 'term',
            'field' => 'partOf.id',
            'size' => 10,
        ],
    ];

    public function execute($request)
    {
        // To keep the top level descriptions filter an agg in sync
        // the autocomplete value is converted to the resource id
        // before the agg filters are added to the query
        $this->getParameters = $request->getGetParameters();
        if (isset($this->getParameters['collection']) && !ctype_digit($this->getParameters['collection'])) {
            $params = sfContext::getInstance()->routing->parse(Qubit::pathInfo($this->getParameters['collection']));
            $this->collection = $params['_sf_route']->resource;

            unset($this->getParameters['collection']);

            if ($this->collection instanceof QubitInformationObject) {
                $this->getParameters['collection'] = $this->collection->id;
            }
        } elseif (isset($this->getParameters['collection']) && ctype_digit($this->getParameters['collection'])) {
            $this->collection = QubitInformationObject::getById($this->getParameters['collection']);
        }

        // Set search realm if searching by repository
        if (isset($request->repos) && ctype_digit($request->repos)) {
            // Add repo to the user session as realm
            if (sfConfig::get('app_enable_institutional_scoping')) {
                $this->context->user->setAttribute('search-realm', $request->repos);
            }
        } elseif (sfConfig::get('app_enable_institutional_scoping')) {
            // Remove realm
            $this->context->user->removeAttribute('search-realm');
        }

        // Add first criterion to the search box if it's over any field
        if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->sq0) && !isset($request->sf0)) {
            $request->query = $request->sq0;
            $this->getParameters['query'] = $request->sq0;
        }

        // If finding aids are disabled, remove search options and params
        if ('1' !== sfConfig::get('app_findingAidsEnabled', '1')) {
            $this->disableFindingAidSearch();
        }

        // And search box query to the first criterion
        if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->query)) {
            $request->sq0 = $request->query;
            $this->getParameters['sq0'] = $request->query;
        }

        // Create the query and filter it with the selected aggs
        parent::execute($request);

        // Create advanced search form (without CSRF protection)
        $this->form = new sfForm([], [], false);
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }

        // Get actual information object template to check archival history
        // visibility in _advancedSearch partial and in parseQuery function
        $this->template = 'isad';
        if (null !== $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template')) {
            $this->template = $infoObjectTemplate->getValue(['sourceCulture' => true]);
        }

        // Add print preview style
        if ('print' == $request->media) {
            $this->getResponse()->addStylesheet('print-preview', 'last');
        }

        // Default to show only top level descriptions
        $this->topLod = true;
        if (isset($request->topLod) && !filter_var($request->topLod, FILTER_VALIDATE_BOOLEAN)) {
            $this->topLod = false;
        }

        $this->addHiddenFields($request);
        $this->setFilterTags($request);

        // Add advanced form filter to the query
        $this->search->addAdvancedSearchFilters($this::$NAMES, $this->getParameters, $this->template);

        // Stop if the input is not valid. It must be after the query is created but before
        // it's executed to keep the boolean search and other params for the next request
        $this->form->bind($request->getRequestParameters() + $request->getGetParameters());
        if (!$this->form->isValid()) {
            return;
        }

        // Sort
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

            case 'alphabetic':
                $field = sprintf('i18n.%s.title.alphasort', $this->selectedCulture);
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

        $this->setView($request);

        $resultSet = QubitSearch::getInstance()->index->getIndex('QubitInformationObject')->search($this->search->getQuery(false, true));

        // Page results
        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($this->limit);
        $this->pager->init();

        $this->populateAggs($resultSet);
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'copyrightStatus':
                $this->form->setValidator($name, new sfValidatorString());

                $choices = [];
                $choices[null] = null;
                foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item) {
                    $choices[$item->id] = $item->__toString();
                }

                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'onlyMedia':
                $choices = [
                    '' => '',
                    '1' => $this->context->i18n->__('Yes'),
                    '0' => $this->context->i18n->__('No'),
                ];

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'levels':
                $criteria = new Criteria();
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);

                // Do source culture fallback
                $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
                $criteria->addAscendingOrderByColumn('name');

                $choices = [];
                $choices[null] = null;
                foreach (QubitTerm::get($criteria) as $item) {
                    $choices[$item->id] = $item->__toString();
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'materialType':
                $criteria = new Criteria();
                $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MATERIAL_TYPE_ID);

                // Do source culture fallback
                $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
                $criteria->addAscendingOrderByColumn('name');

                $choices = [];
                $choices[null] = null;
                foreach (QubitTerm::get($criteria) as $item) {
                    $choices[$item->id] = $item->__toString();
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'repos':
                // Get list of repositories
                $criteria = new Criteria();

                // Do source culture fallback
                $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

                // Ignore root repository
                $criteria->add(QubitActor::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);

                $criteria->addAscendingOrderByColumn('authorized_form_of_name');

                $cache = QubitCache::getInstance();
                $cacheKey = 'search:list-of-repositories:'.$this->context->user->getCulture();
                if ($cache->has($cacheKey)) {
                    $choices = $cache->get($cacheKey);
                } else {
                    $choices = [];
                    $choices[null] = null;
                    foreach (QubitRepository::get($criteria) as $repository) {
                        $choices[$repository->id] = $repository->__toString();
                    }

                    $cache->set($cacheKey, $choices, 3600);
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'collection':
                $this->form->setValidator($name, new sfValidatorString());

                $choices = [];
                if (isset($this->getParameters['collection']) && ctype_digit($this->getParameters['collection'])
                && null !== $collection = QubitInformationObject::getById($this->getParameters['collection'])) {
                    sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);
                    $collectionUrl = url_for($collection);

                    $this->form->setDefault($name, $collectionUrl);
                    $choices[$collectionUrl] = $collection;
                }

                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'startDate':
            case 'endDate':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput([], ['placeholder' => 'YYYY-MM-DD']));
                $this->form->setValidator($name, new sfValidatorDate([
                    'date_format' => '/^(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})$/',
                    'date_format_error' => 'YYYY-MM-DD',
                ]));

                break;

            case 'findingAidStatus':
                $choices = [
                    '' => '',
                    'yes' => $this->context->i18n->__('Yes'),
                    'no' => $this->context->i18n->__('No'),
                    'generated' => $this->context->i18n->__('Generated'),
                    'uploaded' => $this->context->i18n->__('Uploaded'),
                ];

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                break;
        }
    }

    protected function populateAgg($name, $buckets)
    {
        switch ($name) {
            case 'repos':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitRepository::ID, $ids, Criteria::IN);

                foreach (QubitRepository::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
                }

                break;

            case 'levels':
            case 'mediatypes':
            case 'places':
            case 'subjects':
            case 'genres':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

                foreach (QubitTerm::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->getName(['cultureFallback' => true]);
                }

                break;

            case 'creators':
            case 'names':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitActor::ID, $ids, Criteria::IN);

                foreach (QubitActor::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
                }

                break;

            case 'collection':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitInformationObject::ID, $ids, Criteria::IN);

                foreach (QubitInformationObject::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
                }

                break;

            default:
                return parent::populateAgg($name, $buckets);
        }

        return $buckets;
    }

    protected function addHiddenFields($request)
    {
        // Keep control of which hidden fields are added to avoid
        // Cross-Site Scripting vulnerability. Only allow:
        // - Aggregations
        // - Sort, view and media options
        // - actorId, eventTypeId and ancestor params
        $allowed = array_merge(
            array_keys($this::$AGGS),
            ['view', 'sort', 'media'],
            ['actorId', 'eventTypeId', 'ancestor']
        );

        // But ignore aggs already included in the form:
        // - repos, collection and levels
        $ignored = ['repos', 'collection', 'levels'];

        $this->setHiddenFields($request, $allowed, $ignored);
    }

    protected function setFilterTags($request)
    {
        $this->populateFilterTags($request);

        $i18n = $this->context->i18n;

        // Set search realm, if needed
        if (isset($request->repos) && ctype_digit($request->repos)) {
            // Add repo to the user session as realm
            if (sfConfig::get('app_enable_institutional_scoping')) {
                $this->context->user->setAttribute('search-realm', $request->repos);
            }
        } elseif (sfConfig::get('app_enable_institutional_scoping')) {
            // Remove realm
            $this->context->user->removeAttribute('search-realm');
        }

        // Set label for has digital object filter tag
        if (filter_var($request->onlyMedia, FILTER_VALIDATE_BOOLEAN)) {
            $this->setFilterTagLabel('onlyMedia', $i18n->__('With digital objects'));
        } elseif ('0' === $request->onlyMedia) {
            $this->setFilterTagLabel('onlyMedia', $i18n->__('Without digital objects'));
        } else {
            unset($request->onlyMedia);
        }

        // Set label for languages filter tag
        if (!empty($request->languages)) {
            $language = ucfirst(sfCultureInfo::getInstance($this->context->user->getCulture())->getLanguage($request->languages));
            $this->setFilterTagLabel('languages', $language);
        }

        // Set label for date range
        if (isset($request->startDate) || isset($request->endDate)) {
            $dateRangeLabel = '[ '.$request->startDate.' - '.$request->endDate.' ]';
            $this->setFilterTagLabel('dateRange', $dateRangeLabel);
        }

        // Set label for finding aid status
        if (!empty($request->findingAidStatus)) {
            $labels = [
                'yes' => $i18n->__('With finding aid'),
                'no' => $i18n->__('Without finding aid'),
                'generated' => $i18n->__('With generated finding aid'),
                'uploaded' => $i18n->__('With uploaded finding aid'),
            ];

            $this->setFilterTagLabel('findingAidStatus', $labels[$request->findingAidStatus]);
        }
    }

    /**
     * Disable searching by Finding Aid status or for Finding Aid data.
     */
    protected function disableFindingAidSearch()
    {
        // Remove findingAidStatus from field and filtertag lists
        unset(
            self::$NAMES['findingAidStatus'],
            self::$FILTERTAGS['findingAidStatus']
        );

        // Delete the findingAidStatus GET parameter to prevent direct searches
        if (isset($this->getParameters['findingAidStatus'])) {
            unset($this->getParameters['findingAidStatus']);
        }

        // Restrict string searches to exclude finding aid transcript data
        foreach ($this->getParameters as $key => &$val) {
            if (
                preg_match('/sf[0-9]+/', $key)
                && (empty($val) || 'findingAidTranscript' === $val)
            ) {
                $val = 'allExceptFindingAidTranscript';
            }
        }
    }

    /**
     * Set browse page layout view.
     *
     * @param mixed $request
     */
    private function setView($request)
    {
        $this->cardView = 'card';
        $this->tableView = 'table';
        $allowedViews = [$this->cardView, $this->tableView];

        if (isset($request->view) && in_array($request->view, $allowedViews)) {
            $this->view = $request->view;
        } else {
            $this->view = sfConfig::get('app_default_archival_description_browse_view', $this->tableView);
        }
    }
}
