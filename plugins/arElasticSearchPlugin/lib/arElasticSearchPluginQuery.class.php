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
  public $query,
         $queryBool,
         $filters,
         $criteria;

  /**
   * Constructor
   *
   * @param int $limit  how many results should be returned
   * @param int $skip  how many results should be skipped
   *
   * @return void
   */
  public function __construct($limit = 10, $skip = 0)
  {
    $this->query = new \Elastica\Query();
    $this->query->setSize($limit);
    $this->query->setFrom($skip);

    $this->queryBool = new \Elastica\Query\BoolQuery;
  }

  /**
   * Translate internal representation of aggregations
   * to Elastica API, adding them to the query.
   *
   * @param array $aggs  search aggregations
   *
   * @return void
   */
  public function addAggs($aggs)
  {
    foreach ($aggs as $name => $item)
    {
      switch ($item['type'])
      {
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
      if (isset($item['size']))
      {
        $agg->setSize($item['size']);
      }

      $this->query->addAggregation($agg);
    }
  }

  /**
   * Add filters from aggregations to the query
   *
   * @param array $aggs  search aggregations
   * @param array $params  search filters from aggregations
   *
   * @return void
   */
  public function addAggFilters($aggs, $params)
  {
    $this->filters = array();

    // Filter languages only if the languages aggregation
    // is being used and languages is set in the request
    if (isset($aggs['languages']) && isset($params['languages']))
    {
      $this->filters['languages'] = $params['languages'];
      $term = new \Elastica\Query\Term(array($aggs['languages']['field'] => $params['languages']));

      $this->queryBool->addMust($term);
    }

    // Add agg selections as search criteria
    foreach ($params as $param => $value)
    {
      if ('languages' == $param
        || !array_key_exists($param, $aggs)
        || ('repos' == $param && (!ctype_digit($value)
        || null === QubitRepository::getById($value)))
        || 1 === preg_match('/^[\s\t\r\n]*$/', $value))
      {
        continue;
      }

      $this->filters[$param] = $value;

      $query = new \Elastica\Query\Term(array($aggs[$param]['field'] => $value));

      // Collection agg must select all descendants and itself
      if ($param == 'collection')
      {
        $collection = QubitInformationObject::getById($value);

        $querySelf = new \Elastica\Query\Match;
        $querySelf->setFieldQuery('slug', $collection->slug);

        $queryBool = new \Elastica\Query\BoolQuery;
        $queryBool->addShould($query);
        $queryBool->addShould($querySelf);

        $query = $queryBool;
      }

      $this->queryBool->addMust($query);
    }
  }

  /**
   * Add criteria to query based on advanced search form and other params
   *
   * @return void
   */
  public function addAdvancedSearchFilters($fieldNames, $params, $archivalStandard)
  {
    // Build query with the boolean criteria
    if (null !== $criteria = $this->parseQuery($params, $archivalStandard))
    {
      $this->queryBool->addMust($criteria);
    }

    // Process advanced search form fields
    // Some of them have the same name as a aggregation, this creates query
    // duplication but allows as to keep aggs and adv. search form syncronized
    foreach ($fieldNames as $name)
    {
      if (isset($params[$name]) && strlen(trim($params[$name])) > 0
        && (null !== $criteria = $this->fieldCriteria($name, $params[$name])))
      {
        $this->queryBool->addMust($criteria);
      }
    }

    if (null !== $criteria = $this->getDateRangeQuery($params))
    {
      $this->queryBool->addMust($criteria);
    }

    // Default to show only top level descriptions
    if (!isset($params['topLod']) || filter_var($params['topLod'], FILTER_VALIDATE_BOOLEAN))
    {
      $this->queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    }

    // Show descriptions related to an actor by an event type,
    // this parameters come from the actor related IOs lists
    if (isset($params['actorId']) && ctype_digit($params['actorId'])
      && isset($params['eventTypeId']) && ctype_digit($params['eventTypeId']))
    {
      $queryBool = new \Elastica\Query\BoolQuery;
      $queryBool->addMust(new \Elastica\Query\Term(array('dates.actorId' => $params['actorId'])));
      $queryBool->addMust(new \Elastica\Query\Term(array('dates.typeId' => $params['eventTypeId'])));

      // Use nested query and mapping object to allow querying
      // over the actor and event ids from the same event
      $queryNested = new \Elastica\Query\Nested();
      $queryNested->setPath('dates');
      $queryNested->setQuery($queryBool);

      $this->queryBool->addMust($queryNested);
    }
  }

  /**
   * Translate array of search parameters to query criteria.
   *
   * Modified version of parseQuery method in the SearchAdvancedAction class
   *
   * Each set of parameters is numbered, starting at zero, and includes three
   * properties: query text (prefixed by "sq"), operation (prefixed by "so": "and" or
   * "or"), and fields (prefixed by "sf") to return (defaulting to "_all").
   *
   * For example:
   *
   *   $this->searchParams = array(
   *     'so0' => 'and',
   *     'sq0' => 'cats',
   *     'sf0' => ''
   *   );
   *
   * @return object  \Elastica\Query\BoolQuery instance
   */
  protected function parseQuery($params, $archivalStandard)
  {
    $this->criteria = array();
    $queryBool = new \Elastica\Query\BoolQuery;
    $count = 0;

    while (isset($params['sq' . $count]))
    {
      $query = $params['sq' . $count];

      if (!empty($query))
      {
        $field = '_all';
        if (!empty($params['sf'.$count]))
        {
          $field = $params['sf'.$count];
        }

        $operator = 'and';
        if (!empty($params['so'.$count]))
        {
          $operator = $params['so'.$count];
        }

        $queryField = $this->queryField($field, $query, $archivalStandard);
        $this->addToQueryBool($queryBool, $operator, $queryField);

        $this->criteria[] = array(
          'query' => $query,
          'field' => $field,
          'operator' => $operator);
      }

      $count++;
    }

    if (0 == count($queryBool->getParams()))
    {
      return;
    }

    return $queryBool;
  }

  protected function queryField($field, $query, $archivalStandard)
  {
    $query = arElasticSearchPluginUtil::escapeTerm($query);

    switch ($field)
    {
      case 'identifier':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultField('identifier');
        $queryField->setDefaultOperator('AND');

        break;

      case 'referenceCode':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultField('referenceCode');
        $queryField->setDefaultOperator('AND');

        break;

      case 'title':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.title'));
        $queryField->setDefaultOperator('AND');

        break;

      case 'scopeAndContent':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.scopeAndContent'));
        $queryField->setDefaultOperator('AND');

        break;

      case 'archivalHistory':
        ProjectConfiguration::getActive()->loadHelpers(array('Asset', 'Qubit'));

        // Check archival history visibility
        if (($archivalStandard == 'rad' && check_field_visibility('app_element_visibility_rad_archival_history'))
          || ($archivalStandard == 'isad' && check_field_visibility('app_element_visibility_isad_archival_history'))
          || ($archivalStandard != 'isad' && $archivalStandard != 'rad'))
        {
          $queryField = new \Elastica\Query\QueryString($query);
          $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.archivalHistory'));
          $queryField->setDefaultOperator('AND');
        }

        break;

      case 'extentAndMedium':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.extentAndMedium'));
        $queryField->setDefaultOperator('AND');

        break;

      case 'genre':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('genres.i18n.%s.name'));
        $queryField->setDefaultOperator('AND');

        break;

      case 'subject':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('subjects.i18n.%s.name'));
        $queryField->setDefaultOperator('AND');

        break;

      case 'name':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('names.i18n.%s.authorizedFormOfName'));
        $queryField->setDefaultOperator('AND');

        break;

      case 'place':
        $queryField = new \Elastica\Query\BoolQuery;

        $queryPlaceTermName = new \Elastica\Query\QueryString($query);
        $queryPlaceTermName->setFields(arElasticSearchPluginUtil::getI18nFieldNames('places.i18n.%s.name'));
        $queryPlaceTermName->setDefaultOperator('AND');
        $queryField->addShould($queryPlaceTermName);

        $queryPlaceTermUseFor = new \Elastica\Query\QueryString($query);
        $queryPlaceTermUseFor->setFields(arElasticSearchPluginUtil::getI18nFieldNames('places.useFor.i18n.%s.name'));
        $queryPlaceTermUseFor->setDefaultOperator('AND');
        $queryField->addShould($queryPlaceTermUseFor);

        break;

      case 'findingAidTranscript':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultField('findingAid.transcript');
        $queryField->setDefaultOperator('AND');

        break;

      case 'digitalObjectTranscript':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultField('transcript');
        $queryField->setDefaultOperator('AND');

        break;

      case 'allExceptFindingAidTranscript':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultOperator('AND');
        $except = array('findingAid.transcript');
        arElasticSearchPluginUtil::setFields($queryField, 'informationObject', $except);

        break;

      case '_all':
      default:
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultOperator('AND');
        arElasticSearchPluginUtil::setFields($queryField, 'informationObject');

        break;
    }

    return $queryField;
  }

  protected function addToQueryBool(&$queryBool, $operator, $queryField)
  {
      switch ($operator)
      {
        case 'not':
          $queryBool->addMustNot($queryField);

          break;

        case 'or':
          // Build boolean query with all the previous queries
          // and the new one as 'shoulds'
          $queryOr = new \Elastica\Query\BoolQuery;
          $queryOr->addShould($queryBool);
          $queryOr->addShould($queryField);

          $queryBool = new \Elastica\Query\BoolQuery;
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
    switch ($name)
    {
      case 'copyrightStatus':
        // Get unknown copyright status term
        $criteria = new Criteria;
        $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::COPYRIGHT_STATUS_ID);
        $criteria->add(QubitTermI18n::NAME, 'Unknown');
        $term = QubitTerm::getOne($criteria);

        // If the user selected "Unknown copyright" make sure that we are
        // matching documents that either (1) copyright status is unknown or
        // (2) copyright status is not set.
        if (isset($term) && $term->id == $value)
        {
          // Query for documents without copyright status
          $exists = new \Elastica\Query\Exists('copyrightStatusId');
          $queryBoolMissing = new \Elastica\Query\BoolQuery;
          $queryBoolMissing->addMustNot($exists);

          // Query for unknown copyright status
          $query = new \Elastica\Query\Term;
          $query->setTerm('copyrightStatusId', $value);

          $queryBool = new \Elastica\Query\BoolQuery;
          $queryBool->addShould($query);
          $queryBool->addShould($queryBoolMissing);

          return $queryBool;
        }
        else
        {
          $query = new \Elastica\Query\Term;
          $query->setTerm('copyrightStatusId', $value);

          return $query;
        }

      case 'onlyMedia':
        $query = new \Elastica\Query\Term;
        $query->setTerm('hasDigitalObject', filter_var($value, FILTER_VALIDATE_BOOLEAN));

        return $query;

      case 'materialType':
        $query = new \Elastica\Query\Term;
        $query->setTerm('materialTypeId', $value);

        return $query;

      case 'findingAidStatus':
        switch ($value)
        {
          case 'yes':
            $query = new \Elastica\Query\Exists('findingAid.status');

            return $query;

          case 'no':
            $exists = new \Elastica\Query\Exists('findingAid.status');
            $query = new \Elastica\Query\BoolQuery;
            $query->addMustNot($exists);

            return $query;

          case 'generated':
            $query = new \Elastica\Query\Term;
            $query->setTerm('findingAid.status', arFindingAidJob::GENERATED_STATUS);

            return $query;

          case 'uploaded':
            $query = new \Elastica\Query\Term;
            $query->setTerm('findingAid.status', arFindingAidJob::UPLOADED_STATUS);

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
    if (empty($params['startDate']) && empty($params['endDate']))
    {
      return;
    }

    // Process date range, defaults to inclusive
    $type = $params['rangeType'];
    if (empty($type))
    {
      $type = 'inclusive';
    }

    $query = new \Elastica\Query\BoolQuery;
    $range = array();

    if (!empty($params['startDate']))
    {
      $range['gte'] = $params['startDate'];

      if ($type == 'inclusive')
      {
        // Start date before range and end date missing
        $queryBool = new \Elastica\Query\BoolQuery;
        $start = new \Elastica\Query\Range('dates.startDate', array('lt' => $params['startDate']));
        $exists = new \Elastica\Query\Exists('dates.endDate');
        $queryBool->addMust($start);
        $queryBool->addMustNot($exists);

        $query->addShould($queryBool);
      }
    }

    if (!empty($params['endDate']))
    {
      $range['lte'] = $params['endDate'];

      if ($type == 'inclusive')
      {
        // End date after range and start date missing
        $queryBool = new \Elastica\Query\BoolQuery;
        $end = new \Elastica\Query\Range('dates.endDate', array('gt' => $params['endDate']));
        $exists = new \Elastica\Query\Exists('dates.startDate');
        $queryBool->addMust($end);
        $queryBool->addMustNot($exists);

        $query->addShould($queryBool);
      }
    }

    if (!empty($params['startDate']) && !empty($params['endDate']) && $type == 'inclusive')
    {
      // Start date before range and end date after range
      $queryBool = new \Elastica\Query\BoolQuery;
      $queryBool->addMust(new \Elastica\Query\Range('dates.startDate', array('lt' => $params['startDate'])));
      $queryBool->addMust(new \Elastica\Query\Range('dates.endDate', array('gt' => $params['endDate'])));

      $query->addShould($queryBool);
    }

    if ($type == 'inclusive')
    {
      // Any event date inside the range
      $query->addShould(new \Elastica\Query\Range('dates.startDate', $range));
      $query->addShould(new \Elastica\Query\Range('dates.endDate', $range));
    }
    else
    {
      // Both event dates inside the range
      $query->addMust(new \Elastica\Query\Range('dates.startDate', $range));
      $query->addMust(new \Elastica\Query\Range('dates.endDate', $range));
    }

    // Use nested query and mapping object to allow querying
    // over the start and end dates from the same event
    $queryNested = new \Elastica\Query\Nested();
    $queryNested->setPath('dates');
    $queryNested->setQuery($query);

    return $queryNested;
  }

  /**
   * Returns the query
   *
   * @param boolean $allowEmpty  get all or none if the query is empty
   * @param boolean $filterDrafts  filter draft records
   *
   * @return \Elastica\Query
   */
  public function getQuery($allowEmpty = false, $filterDrafts = false)
  {
    if (!$allowEmpty && 1 > count($this->queryBool->getParams()))
    {
      $this->queryBool->addMust(new \Elastica\Query\MatchAll);
    }

    if ($filterDrafts)
    {
      QubitAclSearch::filterDrafts($this->queryBool);
    }

    $this->query->setQuery($this->queryBool);

    return $this->query;
  }
}
