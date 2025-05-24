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

class arElasticSearchPluginQuery
{
    public $query;
    public $queryBool;
    public $filters;
    public $criteria;

    /**
     * Constructor.
     *
     * @param int $limit how many results should be returned
     * @param int $skip  how many results should be skipped
     */
    public function __construct($limit = 10, $skip = 0)
    {
        $this->query = new \Elastica\Query();
        $this->query->setSize($limit);
        $this->query->setFrom($skip);

        $this->queryBool = new \Elastica\Query\BoolQuery();
    }

    /**
     * Translate internal representation of aggregations
     * to Elastica API, adding them to the query.
     *
     * @param array $aggs search aggregations
     */
    public function addAggs($aggs)
    {
        foreach ($aggs as $name => $item) {
            switch ($item['type']) {
                case 'term':
                    $agg = new \Elastica\Aggregation\Terms($name);
                    $agg->setField($item['field']);

                    break;

                case 'filter':
                    $agg = new \Elastica\Aggregation\Filter($name);
                    $agg->setFilter(new \Elastica\Query\Term($item['field']));

                    break;
            }

            // Sets the amount of terms to be returned
            if (isset($item['size'])) {
                $agg->setSize($item['size']);
            }

            $this->query->addAggregation($agg);
        }
    }

    /**
     * Add filters from aggregations to the query.
     *
     * @param array $aggs   search aggregations
     * @param array $params search filters from aggregations
     */
    public function addAggFilters($aggs, $params)
    {
        $this->filters = [];

        // Filter languages only if the languages aggregation
        // is being used and languages is set in the request
        if (isset($aggs['languages'], $params['languages'])) {
            $this->filters['languages'] = $params['languages'];
            $term = new \Elastica\Query\Term(
                [$aggs['languages']['field'] => $params['languages']]
            );

            $this->queryBool->addMust($term);
        }

        // Add agg selections as search criteria
        foreach ($params as $param => $value) {
            if (
                'languages' == $param
                || !array_key_exists($param, $aggs)
                || ('repos' == $param && (!ctype_digit($value)
                || null === QubitRepository::getById($value)))
                || 1 === preg_match('/^[\s\t\r\n]*$/', $value)
            ) {
                continue;
            }

            $this->filters[$param] = $value;

            $query = new \Elastica\Query\Term(
                [$aggs[$param]['field'] => $value]
            );

            // Collection agg must select all descendants and itself
            if ('collection' == $param) {
                $collection = QubitInformationObject::getById($value);

                $querySelf = new \Elastica\Query\MatchQuery();
                $querySelf->setFieldQuery('slug', $collection->slug);

                $queryBool = new \Elastica\Query\BoolQuery();
                $queryBool->addShould($query);
                $queryBool->addShould($querySelf);

                $query = $queryBool;
            }

            $this->queryBool->addMust($query);
        }
    }

    /**
     * Add criteria to query based on advanced search form and other params.
     *
     * @param mixed $fieldNames
     * @param mixed $params
     * @param mixed $archivalStandard
     */
    public function addAdvancedSearchFilters(
        $fieldNames, $params, $archivalStandard
    ) {
        // Build query with the boolean criteria
        if (
            null !== $criteria = $this->parseQuery($params, $archivalStandard)
        ) {
            $this->queryBool->addMust($criteria);
        }

        // Process advanced search form fields
        // Some of them have the same name as a aggregation, this creates query
        // duplication but allows as to keep aggs and adv. search form
        // synchronized
        foreach ($fieldNames as $name) {
            if (
                isset($params[$name])
                && strlen(trim($params[$name])) > 0
                && (
                    null !== $criteria = $this->fieldCriteria(
                        $name, $params[$name]
                    )
                )
            ) {
                $this->queryBool->addMust($criteria);
            }
        }

        if (null !== $criteria = $this->getDateRangeQuery($params)) {
            $this->queryBool->addMust($criteria);
        }

        // Default to show only top level descriptions
        if (
            'isaar' != $archivalStandard
            && (
                !isset($params['topLod'])
                || filter_var($params['topLod'], FILTER_VALIDATE_BOOLEAN)
            )
        ) {
            $this->queryBool->addMust(
                new \Elastica\Query\Term(
                    ['parentId' => QubitInformationObject::ROOT_ID]
                )
            );
        }

        // Show descriptions related to an actor by an event type,
        // this parameters come from the actor related IOs lists
        if (
            isset($params['actorId'])
            && ctype_digit($params['actorId'])
            && isset($params['eventTypeId'])
            && ctype_digit($params['eventTypeId'])
        ) {
            $queryBool = new \Elastica\Query\BoolQuery();
            $queryBool->addMust(
                new \Elastica\Query\Term(
                    ['dates.actorId' => $params['actorId']]
                )
            );
            $queryBool->addMust(
                new \Elastica\Query\Term(
                    ['dates.typeId' => $params['eventTypeId']]
                )
            );

            // Use nested query and mapping object to allow querying
            // over the actor and event ids from the same event
            $queryNested = new \Elastica\Query\Nested();
            $queryNested->setPath('dates');
            $queryNested->setQuery($queryBool);

            $this->queryBool->addMust($queryNested);
        }

        // Show descendants from resource
        if (isset($params['ancestor']) && ctype_digit($params['ancestor'])) {
            $this->queryBool->addMust(
                new \Elastica\Query\Term(['ancestors' => $params['ancestor']])
            );
        }
    }

    /**
     * Returns the query.
     *
     * @param bool $allowEmpty   get all or none if the query is empty
     * @param bool $filterDrafts filter draft records
     *
     * @return \Elastica\Query
     */
    public function getQuery($allowEmpty = false, $filterDrafts = false)
    {
        if (!$allowEmpty && 1 > count($this->queryBool->getParams())) {
            $this->queryBool->addMust(new \Elastica\Query\MatchAll());
        }

        if ($filterDrafts) {
            QubitAclSearch::filterDrafts($this->queryBool);
        }

        $this->query->setQuery($this->queryBool);

        return $this->query;
    }

    /**
     * Translate array of search parameters to query criteria.
     *
     * Modified version of parseQuery method in the SearchAdvancedAction class
     *
     * Each set of parameters is numbered, starting at zero, and includes three
     * properties: query text (prefixed by "sq"), operation (prefixed by "so":
     * "and" or "or"), and fields (prefixed by "sf") to return (defaulting to
     * "_all").
     *
     * For example:
     *
     *   $this->searchParams = array(
     *     'so0' => 'and',
     *     'sq0' => 'cats',
     *     'sf0' => ''
     *   );
     *
     * @param mixed $params
     * @param mixed $archivalStandard
     *
     * @return object \Elastica\Query\BoolQuery instance
     */
    protected function parseQuery($params, $archivalStandard)
    {
        $this->criteria = [];
        $queryBool = new \Elastica\Query\BoolQuery();
        $count = 0;

        while (isset($params['sq'.$count])) {
            $query = $params['sq'.$count];

            if (!empty($query)) {
                $field = '_all';
                if (!empty($params['sf'.$count])) {
                    $field = $params['sf'.$count];
                }

                $operator = 'and';
                if (!empty($params['so'.$count])) {
                    $operator = $params['so'.$count];
                }

                $queryField = $this->queryField(
                    $field, $query, $archivalStandard
                );
                $this->addToQueryBool($queryBool, $operator, $queryField);

                $this->criteria[] = [
                    'query' => $query,
                    'field' => $field,
                    'operator' => $operator,
                ];
            }

            ++$count;
        }

        if (0 == count($queryBool->getParams())) {
            return;
        }

        return $queryBool;
    }

    protected function queryField($field, $query, $archivalStandard)
    {
        switch ($field) {
            case 'identifier':
            case 'referenceCode':
            case 'descriptionIdentifier':
                $fields = [$field => 1];

                break;

            case 'title':
            case 'scopeAndContent':
            case 'extentAndMedium':
            case 'authorizedFormOfName':
            case 'datesOfExistence':
            case 'history':
            case 'legalStatus':
            case 'generalContext':
            case 'institutionResponsibleIdentifier':
            case 'sources':
            case 'places':
                $fields = ['i18n.%s.'.$field => 1];

                break;

            case 'archivalHistory':
                ProjectConfiguration::getActive()->loadHelpers(
                    ['Asset', 'Qubit']
                );

                // Check archival history visibility
                if (
                    ('rad' == $archivalStandard && !check_field_visibility('app_element_visibility_rad_archival_history'))
                    || ('isad' == $archivalStandard && !check_field_visibility('app_element_visibility_isad_archival_history'))
                ) {
                    return;
                }

                $fields = ['i18n.%s.archivalHistory' => 1];

                break;

            case 'genre':
                $fields = ['genres.i18n.%s.name' => 1];

                break;

            case 'subject':
                $fields = ['subjects.i18n.%s.name' => 1];

                break;

            case 'name':
                $fields = ['names.i18n.%s.authorizedFormOfName' => 1];

                break;

            case 'creator':
                $fields = [
                    'creators.i18n.%s.authorizedFormOfName' => 1,
                    'inheritedCreators.i18n.%s.authorizedFormOfName' => 1,
                ];

                break;

            case 'place':
                $fields = [
                    'places.i18n.%s.name' => 1,
                    'places.useFor.i18n.%s.name' => 1,
                ];

                break;

            case 'findingAidTranscript':
                $fields = ['findingAid.transcript' => 1];

                break;

            case 'digitalObjectTranscript':
                $fields = ['transcript' => 1];

                break;

            case 'allExceptFindingAidTranscript':
                $fields = arElasticSearchPluginUtil::getAllFields(
                    'informationObject',
                    ['findingAid.transcript']
                );

                break;

            case 'parallelNames':
            case 'otherNames':
            case 'occupations':
                $fields = [$field.'.i18n.%s.name' => 1];

                break;

            case 'occupationNotes':
                $fields = ['occupations.i18n.%s.content' => 1];

                break;

            case 'maintenanceNotes':
                $fields = ['maintenanceNotes.i18n.%s.content' => 1];

                break;

            case '_all':
            default:
                if ('isaar' == $archivalStandard) {
                    $documentType = 'actor';
                } else {
                    $documentType = 'informationObject';
                }

                $fields = arElasticSearchPluginUtil::getAllFields($documentType);

                break;
        }

        return arElasticSearchPluginUtil::generateQueryString(
            $query, $fields
        );
    }

    protected function addToQueryBool(&$queryBool, $operator, $queryField)
    {
        switch ($operator) {
            case 'not':
                $queryBool->addMustNot($queryField);

                break;

            case 'or':
                // Build boolean query with all the previous queries
                // and the new one as 'shoulds'
                $queryOr = new \Elastica\Query\BoolQuery();
                $queryOr->addShould($queryBool);
                $queryOr->addShould($queryField);

                $queryBool = new \Elastica\Query\BoolQuery();
                $queryBool->addMust($queryOr);

                break;

            case 'and':
            default: // First criteria falls here
                $queryBool->addMust($queryField);

                break;
        }
    }

    protected function fieldCriteria($name, $value)
    {
        switch ($name) {
            case 'copyrightStatus':
                // Get unknown copyright status term
                $criteria = new Criteria();
                $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
                $criteria->add(
                    QubitTerm::TAXONOMY_ID, QubitTaxonomy::COPYRIGHT_STATUS_ID
                );
                $criteria->add(QubitTermI18n::NAME, 'Unknown');
                $term = QubitTerm::getOne($criteria);

                // If the user selected "Unknown copyright" make sure that we
                // are matching documents that either (1) copyright status is
                // unknown or (2) copyright status is not set.
                if (isset($term) && $term->id == $value) {
                    // Query for documents without copyright status
                    $exists = new \Elastica\Query\Exists('copyrightStatusId');
                    $queryBoolMissing = new \Elastica\Query\BoolQuery();
                    $queryBoolMissing->addMustNot($exists);

                    // Query for unknown copyright status
                    $query = new \Elastica\Query\Term();
                    $query->setTerm('copyrightStatusId', $value);

                    $queryBool = new \Elastica\Query\BoolQuery();
                    $queryBool->addShould($query);
                    $queryBool->addShould($queryBoolMissing);

                    return $queryBool;
                }

                $query = new \Elastica\Query\Term();
                $query->setTerm('copyrightStatusId', $value);

                return $query;

            case 'onlyMedia':
                $query = new \Elastica\Query\Term();
                $query->setTerm('hasDigitalObject', filter_var(
                    $value, FILTER_VALIDATE_BOOLEAN)
                );

                return $query;

            case 'materialType':
                $query = new \Elastica\Query\Term();
                $query->setTerm('materialTypeId', $value);

                return $query;

            case 'findingAidStatus':
                switch ($value) {
                    case 'yes':
                        $query = new \Elastica\Query\Exists(
                            'findingAid.status'
                        );

                        return $query;

                    case 'no':
                        $exists = new \Elastica\Query\Exists(
                            'findingAid.status'
                        );
                        $query = new \Elastica\Query\BoolQuery();
                        $query->addMustNot($exists);

                        return $query;

                    case 'generated':
                        $query = new \Elastica\Query\Term();
                        $query->setTerm(
                            'findingAid.status',
                            QubitFindingAid::GENERATED_STATUS
                        );

                        return $query;

                    case 'uploaded':
                        $query = new \Elastica\Query\Term();
                        $query->setTerm(
                            'findingAid.status',
                            QubitFindingAid::UPLOADED_STATUS
                        );

                        return $query;
                }

                return;
        }
    }

    /*
     * Greate date range boolean query based on the dates and type.
     * Types:
     * - 'inclusive': the event must be active inside the range (it may overlap)
     * - 'exact' (or others): the event must be active only inside range
     */
    protected function getDateRangeQuery($params)
    {
        if (empty($params['startDate']) && empty($params['endDate'])) {
            return;
        }

        // Process date range, defaults to inclusive
        $type = $params['rangeType'];
        if (empty($type)) {
            $type = 'inclusive';
        }

        $query = new \Elastica\Query\BoolQuery();
        $range = [];

        if (!empty($params['startDate'])) {
            $range['gte'] = $params['startDate'];

            if ('inclusive' == $type) {
                // Start date before range and end date missing
                $queryBool = new \Elastica\Query\BoolQuery();
                $start = new \Elastica\Query\Range(
                    'dates.startDate',
                    ['lt' => $params['startDate']]
                );
                $exists = new \Elastica\Query\Exists('dates.endDate');
                $queryBool->addMust($start);
                $queryBool->addMustNot($exists);

                $query->addShould($queryBool);
            }
        }

        if (!empty($params['endDate'])) {
            $range['lte'] = $params['endDate'];

            if ('inclusive' == $type) {
                // End date after range and start date missing
                $queryBool = new \Elastica\Query\BoolQuery();
                $end = new \Elastica\Query\Range(
                    'dates.endDate', ['gt' => $params['endDate']]
                );
                $exists = new \Elastica\Query\Exists('dates.startDate');
                $queryBool->addMust($end);
                $queryBool->addMustNot($exists);

                $query->addShould($queryBool);
            }
        }

        if (
            !empty($params['startDate'])
            && !empty($params['endDate'])
            && 'inclusive' == $type
        ) {
            // Start date before range and end date after range
            $queryBool = new \Elastica\Query\BoolQuery();
            $queryBool->addMust(
                new \Elastica\Query\Range(
                    'dates.startDate', ['lt' => $params['startDate']]
                )
            );
            $queryBool->addMust(
                new \Elastica\Query\Range(
                    'dates.endDate', ['gt' => $params['endDate']]
                )
            );

            $query->addShould($queryBool);
        }

        if ('inclusive' == $type) {
            // Any event date inside the range
            $query->addShould(
                new \Elastica\Query\Range('dates.startDate', $range)
            );
            $query->addShould(
                new \Elastica\Query\Range('dates.endDate', $range)
            );
        } else {
            // Both event dates inside the range
            $query->addMust(
                new \Elastica\Query\Range('dates.startDate', $range)
            );
            $query->addMust(new \Elastica\Query\Range('dates.endDate', $range));
        }

        // Use nested query and mapping object to allow querying
        // over the start and end dates from the same event
        $queryNested = new \Elastica\Query\Nested();
        $queryNested->setPath('dates');
        $queryNested->setQuery($query);

        return $queryNested;
    }
}
