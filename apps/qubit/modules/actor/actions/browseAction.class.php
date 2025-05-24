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
 * Show paginated list of actors.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class ActorBrowseAction extends DefaultBrowseAction
{
    public const INDEX_TYPE = 'QubitActor';

    // Arrays not allowed in class constants
    public static $NAMES = [
        'repository',
        'hasDigitalObject',
        'entityType',
        'emptyField',
        'relatedType',
        'relatedAuthority',
    ];
    public static $FILTERTAGS = [
        'hasDigitalObject' => [],
        'repository' => ['model' => 'QubitRepository'],
        'entityType' => ['model' => 'QubitTerm'],
        'relatedType' => ['model' => 'QubitTerm'],
        'occupation' => ['model' => 'QubitTerm'],
        'place' => ['model' => 'QubitTerm'],
        'subject' => ['model' => 'QubitTerm'],
        'mediatypes' => ['model' => 'QubitTerm'],
        'emptyField' => [],
    ];
    public static $AGGS = [
        'languages' => [
            'type' => 'term',
            'field' => 'i18n.languages',
            'size' => 10,
        ],
        'mediatypes' => [
            'type' => 'term',
            'field' => 'digitalObject.mediaTypeId',
            'size' => 10,
        ],
        'entityType' => [
            'type' => 'term',
            'field' => 'entityTypeId',
            'size' => 10,
        ],
        'repository' => [
            'type' => 'term',
            'field' => 'maintainingRepositoryId',
            'size' => 10,
        ],
        'occupation' => [
            'type' => 'term',
            'field' => 'occupations.id',
            'size' => 10,
        ],
        'place' => [
            'type' => 'term',
            'field' => 'places.id',
            'size' => 10,
        ],
        'subject' => [
            'type' => 'term',
            'field' => 'subjects.id',
            'size' => 10,
        ],
    ];

    /**
     * Return ElasticSearch field(s) for a given "friendly" field name.
     *
     * We do this as we don't want to expose our ElasticSearch schema.
     *
     * @param mixed $field
     *
     * @return array Array containing ElasticSearch fields
     */
    public function getEsFields($field)
    {
        $esFields = [];

        switch ($field) {
            case 'authorizedFormOfName':
            case 'datesOfExistence':
            case 'history':
            case 'legalStatus':
            case 'places':
            case 'generalContext':
            case 'institutionResponsibleIdentifier':
            case 'sources':
                $esFields = arElasticSearchPluginUtil::getI18nFieldNames(sprintf('i18n.%%s.%s', $field));

                break;

            case 'parallelNames':
            case 'otherNames':
            case 'occupations':
                $esFields = arElasticSearchPluginUtil::getI18nFieldNames(sprintf('%s.i18n.%%s.name', $field));

                break;

            case 'subject':
                $esFields = arElasticSearchPluginUtil::getI18nFieldNames('subjects.i18n.%s.name');

                break;

            case 'place':
                $esFields = arElasticSearchPluginUtil::getI18nFieldNames('places.i18n.%s.name');

                break;

            case 'occupationNotes':
                $esFields = arElasticSearchPluginUtil::getI18nFieldNames('occupations.i18n.%s.content');

                break;

            case 'maintenanceNotes':
                $esFields = arElasticSearchPluginUtil::getI18nFieldNames('maintenanceNotes.i18n.%s.content');

                break;

            case 'descriptionIdentifier':
                $esFields = ['descriptionIdentifier'];

                break;
        }

        return $esFields;
    }

    public function execute($request)
    {
        // Translate field labels
        $this->i18n = $this->context->i18n;

        $this->fieldOptions = [
            'authorizedFormOfName' => $this->i18n->__('Authorized form of name'),
            'parallelNames' => $this->i18n->__('Parallel form(s) of name'),
            'otherNames' => $this->i18n->__('Other form(s) of name'),
            'datesOfExistence' => $this->i18n->__('Dates of existence'),
            'history' => $this->i18n->__('History'),
            'legalStatus' => $this->i18n->__('Legal status'),
            'places' => $this->i18n->__('Places'),
            'generalContext' => $this->i18n->__('General context'),
            'occupations' => $this->i18n->__('Occupation access points'),
            'occupationNotes' => $this->i18n->__('Occupation access point notes'),
            'subject' => $this->i18n->__('Subject access points'),
            'place' => $this->i18n->__('Place access points'),
            'descriptionIdentifier' => $this->i18n->__('Authority record identifier'),
            'institutionResponsibleIdentifier' => $this->i18n->__('Institution identifier'),
            'sources' => $this->i18n->__('Sources'),
            'maintenanceNotes' => $this->i18n->__('Maintenance notes'),
        ];

        // If a global search has been requested, translate that into an advanced search
        if (isset($request->subquery)) {
            $request->sq0 = $request->subquery;
        }

        parent::execute($request);

        // Look up related authority, if specified
        $this->relatedAuthority = $this->getRelatedAuthorityUsingSlug($request->relatedAuthority);

        // Prepare filter tags, form, and hidden fields/values
        $this->setFilterTagsAndForm($request);

        $this->form->bind($request->getRequestParameters() + $request->getGetParameters());
        if (!$this->form->isValid()) {
            return;
        }

        // Perform search and paging
        $resultSet = $this->doSearch($request);

        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($request->page ? $request->page : 1);
        $this->pager->setMaxPerPage($this->limit);
        $this->pager->init();

        $this->populateAggs($resultSet);

        // Default to hide the advanced search panel
        $this->showAdvanced = filter_var(
            $request->showAdvanced,
            FILTER_VALIDATE_BOOLEAN
        );

        // If an advanced search has been requested of all fields, put the query text into the global search field
        if (!isset($request->subquery) && isset($request->sq0) && !isset($request->sf0)) {
            $request->subquery = $request->sq0;
        }
    }

    protected function addField($name, $request)
    {
        switch ($name) {
            case 'repository':
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
                    $choices = [null => null];

                    foreach (QubitRepository::get($criteria) as $repository) {
                        $choices[$repository->id] = $repository->__toString();
                    }

                    $cache->set($cacheKey, $choices, 3600);
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                // Set field defaults based on filter data values
                if (!empty($this->getFilterTagObject('repository'))) {
                    $this->form->setDefault('repository', $this->getFilterTagObject('repository')->id);
                }

                break;

            case 'hasDigitalObject':
                $choices = [
                    '' => '',
                    '1' => $this->context->i18n->__('Yes'),
                    '0' => $this->context->i18n->__('No'),
                ];

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                if (isset($request->hasDigitalObject)) {
                    $this->form->setDefault('hasDigitalObject', $request->hasDigitalObject);
                }

                break;

            case 'entityType':
                $choices = [null => null];

                foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::ACTOR_ENTITY_TYPE_ID) as $item) {
                    $choices[$item->id] = $item->__toString();
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                if (!empty($this->getFilterTagObject('entityType'))) {
                    $this->form->setDefault('entityType', $this->getFilterTagObject('entityType')->id);
                }

                break;

            case 'emptyField':
                $choices = [null => null];

                foreach ($this->fieldOptions as $field => $label) {
                    $choices[$field] = $label;
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                if (!empty($request->emptyField)) {
                    $this->form->setDefault('emptyField', $request->emptyField);
                }

                break;

            case 'relatedType':
                $choices = [null => null];

                foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::ACTOR_RELATION_TYPE_ID) as $item) {
                    // Omit category terms
                    if (QubitTerm::ROOT_ID != $item->parentId) {
                        $choices[$item->id] = $item->__toString();
                    }
                }

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($choices)]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $choices]));

                if (!empty($this->getFilterTagObject($name))) {
                    $this->form->setDefault($name, $this->getFilterTagObject($name)->id);
                }

                break;

            case 'relatedAuthority':
                $defaultChoices = [];

                if (!empty($this->relatedAuthority)) {
                    $defaultChoices = [$request->{$name} => $this->relatedAuthority->getAuthorizedFormOfName(['cultureFallback' => true])];
                }

                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $defaultChoices]));

                break;
        }
    }

    protected function populateAgg($name, $buckets)
    {
        switch ($name) {
            case 'mediatypes':
            case 'entityType':
            case 'occupation':
            case 'place':
            case 'subject':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

                foreach (QubitTerm::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->getName(['cultureFallback' => true]);
                }

                break;

            case 'repository':
                $ids = array_column($buckets, 'key');
                $criteria = new Criteria();
                $criteria->add(QubitActor::ID, $ids, Criteria::IN);

                foreach (QubitActor::get($criteria) as $item) {
                    $buckets[array_search($item->id, $ids)]['display'] = $item->getAuthorizedFormOfName(['cultureFallback' => true]);
                }

                break;

            default:
                return parent::populateAgg($name, $buckets);
        }

        return $buckets;
    }

    protected function setFilterTags($request)
    {
        $this->populateFilterTags($request);

        // Set label for has digital object filter tag
        if (filter_var($request->hasDigitalObject, FILTER_VALIDATE_BOOLEAN)) {
            $this->setFilterTagLabel('hasDigitalObject', $this->i18n->__('With digital objects'));
        } elseif ('0' === $request->hasDigitalObject) {
            $this->setFilterTagLabel('hasDigitalObject', $this->i18n->__('Without digital objects'));
        } else {
            unset($request->hasDigitalObject);
        }

        if (!empty($request->emptyField)) {
            // Set label for empty field filter tag
            $labelText = $this->i18n->__('Empty: %1%', ['%1%' => $this->fieldOptions[$request->emptyField]]);
            $this->setFilterTagLabel('emptyField', $labelText);
        }
    }

    protected function setSort($request)
    {
        switch ($request->sort) {
            // I don't think that this is going to scale, but let's leave it for now
            case 'alphabetic':
                $field = sprintf('i18n.%s.authorizedFormOfName.alphasort', $this->selectedCulture);
                $this->search->query->setSort([$field => $request->sortDir]);

                break;

            case 'identifier':
                $this->search->query->setSort(['descriptionIdentifier.untouched' => $request->sortDir]);

                break;

            case 'lastUpdated':
            default:
                $this->search->query->setSort(['updatedAt' => $request->sortDir]);
        }
    }

    protected function setFilterTagsAndForm($request)
    {
        $this->setFilterTags($request);

        // Set search realm, if needed
        if (isset($request->repository) && ctype_digit($request->repository)) {
            // Add repo to the user session as realm
            if (sfConfig::get('app_enable_institutional_scoping')) {
                $this->context->user->setAttribute('search-realm', $request->repository);
            }
        } elseif (sfConfig::get('app_enable_institutional_scoping')) {
            // Remove search realm
            $this->context->user->removeAttribute('search-realm');
        }

        // Set up form (without CSRF protection), using the request,
        // and data fetched by filter tags, to provide defaults
        $this->form = new sfForm([], [], false);
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

        foreach ($this::$NAMES as $name) {
            $this->addField($name, $request);
        }

        // Set which values will be relayed as hidden fields via the advanced search form
        $allowed = array_merge(
            array_keys($this::$AGGS),
            ['sort', 'sortDir']
        );

        // These get relayed as form field default values
        $ignored = ['repository', 'entityType', 'relatedType', 'relatedAuthority'];

        $this->setHiddenFields($request, $allowed, $ignored);
    }

    protected function doSearch($request)
    {
        $this->setSort($request);

        // Add advanced form filter to the query
        $this->search->addAdvancedSearchFilters($this::$NAMES + array_keys($this->fieldOptions), $this->request->getParameterHolder()->getAll(), 'isaar');

        // Filter by whether or not an actor has a digital object attached
        if (isset($this->request->hasDigitalObject)) {
            $queryField = new \Elastica\Query\Term();
            $queryField->setTerm('hasDigitalObject', $this->request->hasDigitalObject);
            $this->search->queryBool->addMust($queryField);
        }

        // Filter by related authority and/or relation type
        if (!empty($this->relatedAuthority) && !empty($request->relatedType)) {
            // Include actors that relate to the specified actor and have a relation
            // of the specified type
            $queryBool = new \Elastica\Query\BoolQuery();

            $queryBool->addMust($this->actorRelationsQueryForActor($this->relatedAuthority->id));
            $queryBool->addMust($this->actorRelationsQueryForType($this->request->relatedType));

            $this->search->queryBool->addMust($this->actorRelationsNestedQuery($queryBool));

            // Omit the specified actor
            $this->search->queryBool->addMust($this->actorExcludeQuery($this->relatedAuthority->id));
        } elseif (!empty($this->relatedAuthority)) {
            // Include actors that relate to the specified actor
            $queryBool = new \Elastica\Query\BoolQuery();

            $queryBool->addMust($this->actorRelationsQueryForActor($this->relatedAuthority->id));

            $this->search->queryBool->addMust($this->actorRelationsNestedQuery($queryBool));

            // Omit the specified actor
            $this->search->queryBool->addMust($this->actorExcludeQuery($this->relatedAuthority->id));
        } elseif (!empty($this->request->relatedType)) {
            // Include actors with a direct relation of a specified type
            $queryField = new \Elastica\Query\Term();
            $queryField->setTerm('actorDirectRelationTypes', $request->relatedType);

            $this->search->queryBool->addMust($queryField);
        }

        // Filter out results if a specific field isn't empty
        if (!empty($request->emptyField)) {
            foreach ($this->getEsFields($request->emptyField) as $esField) {
                $this->search->queryBool->addMustNot(new \Elastica\Query\Exists($esField));
            }
        }

        $this->search->query->setQuery($this->search->queryBool);

        return QubitSearch::getInstance()->index->getIndex('QubitActor')->search($this->search->getQuery(false));
    }

    private function getRelatedAuthorityUsingSlug($slug)
    {
        if (!empty($slug)) {
            $params = $this->context->routing->parse(Qubit::pathInfo($slug));

            return 'QubitActor' == get_class($params['_sf_route']->resource) ? $params['_sf_route']->resource : null;
        }
    }

    private function actorRelationsQueryForActor($actorId)
    {
        // Result relations must have either a related object or subject ID
        $queryRelatedBool = new \Elastica\Query\BoolQuery();

        $queryField = new \Elastica\Query\Term();
        $queryField->setTerm('actorRelations.objectId', $actorId);
        $queryRelatedBool->addShould($queryField);

        $queryField = new \Elastica\Query\Term();
        $queryField->setTerm('actorRelations.subjectId', $actorId);
        $queryRelatedBool->addShould($queryField);

        return $queryRelatedBool;
    }

    private function actorRelationsQueryForType($typeId)
    {
        $queryTypeBool = new \Elastica\Query\BoolQuery();

        $queryField = new \Elastica\Query\Term();
        $queryField->setTerm('actorRelations.typeId', $typeId);
        $queryTypeBool->addShould($queryField);

        $relationTerm = QubitTerm::getById($typeId);

        // Look for converse term as well if one exists
        $converseTerm = $relationTerm->getConverseActorRelationTerm();

        if (null !== $converseTerm && $typeId != $converseTerm->id) {
            $queryField = new \Elastica\Query\Term();
            $queryField->setTerm('actorRelations.typeId', $converseTerm->id);
            $queryTypeBool->addShould($queryField);
        }

        return $queryTypeBool;
    }

    private function actorRelationsNestedQuery($query)
    {
        $queryNested = new \Elastica\Query\Nested();
        $queryNested->setPath('actorRelations');
        $queryNested->setQuery($query);

        return $queryNested;
    }

    private function actorExcludeQuery($actorId)
    {
        // Omit the actor that the others are related to
        $queryBool = new \Elastica\Query\BoolQuery();

        $queryField = new \Elastica\Query\Term();
        $queryField->setTerm('_id', $actorId);
        $queryBool->addMustNot($queryField);

        return $queryBool;
    }
}
