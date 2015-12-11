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
 * @package    AccesstoMemory
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class InformationObjectBrowseAction extends DefaultBrowseAction
{
  const INDEX_TYPE = 'QubitInformationObject';

  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'copyrightStatus',
      'onlyMedia',
      'levels',
      'materialType',
      'repos',
      'collection',
      'startDate',
      'endDate',
      'topLod',
      'rangeType'
    ),

    $FACETS = array(
      'languages' =>
        array('type' => 'term',
              'field' => 'i18n.languages',
              'filter' => 'hideDrafts',
              'size' => 10),
      'levels' =>
        array('type' => 'term',
              'field' => 'levelOfDescriptionId',
              'filter' => 'hideDrafts',
              'size' => 10),
      'mediatypes' =>
        array('type' => 'term',
              'field' => 'digitalObject.mediaTypeId',
              'filter' => 'hideDrafts',
              'size' => 10),
      'digitalobjects' =>
        array('type' => 'query',
              'field' => array('hasDigitalObject' => true),
              'filter' => 'hideDrafts',
              'populate' => false),
      'repos' =>
        array('type' => 'term',
              'field' => 'repository.id',
              'filter' => 'hideDrafts',
              'size' => 10),
      'places' =>
        array('type'   => 'term',
              'field'  => 'places.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'subjects' =>
        array('type'   => 'term',
              'field'  => 'subjects.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'genres' =>
        array('type'   => 'term',
              'field'  => 'genres.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'creators' =>
        array('type'   => 'term',
              'field'  => 'creators.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'names' =>
        array('type'   => 'term',
              'field'  => 'names.id',
              'filter' => 'hideDrafts',
              'size'   => 10),
      'collection' =>
        array('type'   => 'term',
              'field'  => 'partOf.id',
              'size'   => 10));

  protected function addField($name)
  {
    switch ($name)
    {
      case 'copyrightStatus':
        $this->form->setValidator($name, new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'onlyMedia':
        $choices = array(
          ''  => '',
          '1' => $this->context->i18n->__('Yes'),
          '0' => $this->context->i18n->__('No')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'levels':
        // Choices are added in the end with the LOD facet data
        $this->form->setValidator($name, new sfValidatorString);

        break;

      case 'materialType':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MATERIAL_TYPE_ID);

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitTerm');
        $criteria->addAscendingOrderByColumn('name');

        $choices = array();
        $choices[null] = null;
        foreach (QubitTerm::get($criteria) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'repos':
        // Get list of repositories
        $criteria = new Criteria;

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

        // Ignore root repository
        $criteria->add(QubitActor::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);

        $criteria->addAscendingOrderByColumn('authorized_form_of_name');

        $cache = QubitCache::getInstance();
        $cacheKey = 'advanced-search:list-of-repositories:'.$this->context->user->getCulture();
        if ($cache->has($cacheKey))
        {
          $choices = $cache->get($cacheKey);
        }
        else
        {
          $choices = array();
          $choices[null] = null;
          foreach (QubitRepository::get($criteria) as $repository)
          {
            $choices[$repository->id] = $repository->__toString();
          }

          $cache->set($cacheKey, $choices, 3600);
        }

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'collection':
        $this->form->setValidator($name, new sfValidatorString);

        $choices = array();
        if (isset($this->getParameters['collection']) && ctype_digit($this->getParameters['collection'])
          && null !== $collection = QubitInformationObject::getById($this->getParameters['collection']))
        {
          sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
          $collectionUrl = url_for($collection);

          $this->form->setDefault($name, $collectionUrl);
          $choices[$collectionUrl] = $collection;
        }

        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'startDate':
      case 'endDate':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput(array(), array('placeholder' => 'YYYY-MM-DD')));
        $this->form->setValidator($name, new sfValidatorDate(array(
          'date_format' => '/^(?P<year>\d{4})-(?P<month>\d{2})-(?P<day>\d{2})$/',
          'date_format_error' => 'YYYY-MM-DD')));

        break;
    }
  }

  protected function processField($field)
  {
    if (null === $value = $this->form->getValue($field->getName()))
    {
      return;
    }

    return $this->fieldCriteria($field->getName(), $value);
  }

  static function fieldCriteria($name, $value)
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
          // Filtered query for documents without copyright status
          $queryAll = new \Elastica\Query\MatchAll();
          $filter = new \Elastica\Filter\Missing;
          $filter->setField('copyrightStatusId');
          $filteredQuery = new \Elastica\Query\Filtered($queryAll, $filter);

          // Query for unknown copyright status
          $query = new \Elastica\Query\Term;
          $query->setTerm('copyrightStatusId', $value);

          $queryBool = new \Elastica\Query\Bool();
          $queryBool->addShould($query);
          $queryBool->addShould($filteredQuery);

          return $queryBool;
        }
        else
        {
          $query = new \Elastica\Query\Term;
          $query->setTerm('copyrightStatusId', $value);
          return $query;
        }

        break;

      case 'onlyMedia':
        $query = new \Elastica\Query\Term;
        $query->setTerm('hasDigitalObject', filter_var($value, FILTER_VALIDATE_BOOLEAN));
        return $query;

        break;

      case 'materialType':
        $query = new \Elastica\Query\Term;
        $query->setTerm('materialTypeId', $value);
        return $query;

        break;
    }
  }

  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();
    $this->criteria = array();
    $count = -1;

    // Parse adv. search boolean criteria
    while (null !== $query = $this->request->getParameter('sq'.++$count))
    {
      if (empty($query))
      {
        continue;
      }

      $field = $this->request->getParameter('sf'.$count, '_all');
      $operator = $this->request->getParameter('so'.$count, 'and');

      $queryField = $this->queryField($field, $query, $this->template);

      $this->addToQueryBool($queryBool, $operator, $queryField);

      $this->criteria[] = array(
        'query' => $query,
        'field' => $field,
        'operator' => $operator);
    }

    if (0 == count($queryBool->getParams()))
    {
      return;
    }

    return $queryBool;
  }

  static function queryField($field, $query, $archivalStandard)
  {
    switch ($field)
    {
      case 'identifier':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultField('identifier');
        $queryField->setDefaultOperator('OR');

        break;

      case 'referenceCode':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultField('referenceCode');
        $queryField->setDefaultOperator('OR');

        break;

      case 'title':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.title'));
        $queryField->setDefaultOperator('OR');

        break;

      case 'scopeAndContent':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.scopeAndContent'));
        $queryField->setDefaultOperator('OR');

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
          $queryField->setDefaultOperator('OR');
        }

        break;

      case 'extentAndMedium':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.extentAndMedium'));
        $queryField->setDefaultOperator('OR');

        break;

      case 'genre':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('genres.i18n.%s.name'));
        $queryField->setDefaultOperator('OR');

        break;

      case 'subject':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('subjects.i18n.%s.name'));
        $queryField->setDefaultOperator('OR');

        break;

      case 'name':
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('names.i18n.%s.authorizedFormOfName'));
        $queryField->setDefaultOperator('OR');

        break;

      case 'place':
        $queryField = new \Elastica\Query\Bool();

        $queryPlaceTermName = new \Elastica\Query\QueryString($query);
        $queryPlaceTermName->setFields(arElasticSearchPluginUtil::getI18nFieldNames('places.i18n.%s.name'));
        $queryPlaceTermName->setDefaultOperator('OR');
        $queryField->addShould($queryPlaceTermName);

        $queryPlaceTermUseFor = new \Elastica\Query\QueryString($query);
        $queryPlaceTermUseFor->setFields(arElasticSearchPluginUtil::getI18nFieldNames('places.useFor.i18n.%s.name'));
        $queryPlaceTermUseFor->setDefaultOperator('OR');
        $queryField->addShould($queryPlaceTermUseFor);

        break;

      case '_all':
      default:
        $queryField = new \Elastica\Query\QueryString($query);
        $queryField->setDefaultOperator('OR');
        arElasticSearchPluginUtil::setAllFields($queryField, 'informationObject');

        break;
    }

    return $queryField;
  }

  static function addToQueryBool(&$queryBool, $operator, $queryField)
  {
      switch ($operator)
      {
        case 'not':
          $queryBool->addMustNot($queryField);

          break;

        case 'or':
          // Build boolean query with all the previous queries
          // and the new one as 'shoulds'
          $queryOr = new \Elastica\Query\Bool();
          $queryOr->addShould($queryBool);
          $queryOr->addShould($queryField);

          $queryBool = new \Elastica\Query\Bool();
          $queryBool->addMust($queryOr);

          break;

        case 'and':
        default: // First criteria falls here
          $queryBool->addMust($queryField);

          break;
      }
  }

  /*
   * Greate date range boolean query based on the dates and type.
   * Types:
   * - 'inclusive': the event must be active inside the range (it may overlap)
   * - 'exact' (or others): the event must be active only inside range
   */
  static function getDateRangeQuery($start, $end, $type)
  {
    if (empty($start) && empty($end))
    {
      return;
    }

    $query = new \Elastica\Query\Bool();
    $range = array();

    if (!empty($start))
    {
      $range['gte'] = $start;

      if ($type == 'inclusive')
      {
        // Start date before range and end date missing
        $queryStart = new \Elastica\Query\Range('dates.startDate', array('lt' => $start));
        $filter = new \Elastica\Filter\Missing;
        $filter->setField('dates.endDate');
        $filteredQuery = new \Elastica\Query\Filtered($queryStart, $filter);

        $query->addShould($filteredQuery);
      }
    }

    if (!empty($end))
    {
      $range['lte'] = $end;

      if ($type == 'inclusive')
      {
        // End date after range and start date missing
        $queryEnd = new \Elastica\Query\Range('dates.endDate', array('gt' => $end));
        $filter = new \Elastica\Filter\Missing;
        $filter->setField('dates.startDate');
        $filteredQuery = new \Elastica\Query\Filtered($queryEnd, $filter);

        $query->addShould($filteredQuery);
      }
    }

    if (!empty($start) && !empty($end) && $type == 'inclusive')
    {
      // Start date before range and end date after range
      $queryBool = new \Elastica\Query\Bool();
      $queryBool->addMust(new \Elastica\Query\Range('dates.startDate', array('lt' => $start)));
      $queryBool->addMust(new \Elastica\Query\Range('dates.endDate', array('gt' => $end)));

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

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'levels':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        // Add LOD filter options here too, it needs to be
        // based on the levels available in the facet
        $choices = array();
        $choices[null] = null;
        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setWidget('levels', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'repos':
        $criteria = new Criteria;
        $criteria->add(QubitRepository::ID, array_keys($ids), Criteria::IN);

        foreach (QubitRepository::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'mediatypes':
      case 'places':
      case 'subjects':
      case 'genres':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'creators':
      case 'names':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      case 'collection':
        $criteria = new Criteria;
        $criteria->add(QubitInformationObject::ID, array_keys($ids), Criteria::IN);

        foreach (QubitInformationObject::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->__toString();
        }

        break;

      default:
        parent::populateFacet($name, $ids);
    }
  }

  public function execute($request)
  {
    // To keep the top level descriptions filter an facet in sync
    // the autocomplete value is converted to the resource id
    // before the facet filters are added to the query
    $this->getParameters = $request->getGetParameters();
    if (isset($this->getParameters['collection']) && !ctype_digit($this->getParameters['collection']))
    {
      $params = sfContext::getInstance()->routing->parse(Qubit::pathInfo($this->getParameters['collection']));
      $this->collection = $params['_sf_route']->resource;

      unset($this->getParameters['collection']);

      if ($this->collection instanceof QubitInformationObject)
      {
        $this->getParameters['collection'] = $this->collection->id;
      }
    }
    else if (isset($this->getParameters['collection']) && ctype_digit($this->getParameters['collection']))
    {
      $this->collection = QubitInformationObject::getById($this->getParameters['collection']);
    }

    // Create the query and filter it with the selected facets
    parent::execute($request);

    if ('print' == $request->media)
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    $this->showAdvanced = false;
    if (filter_var($request->showAdvanced, FILTER_VALIDATE_BOOLEAN))
    {
      $this->showAdvanced = true;
    }

    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    // Get actual information object template to check archival history
    // visibility in _advancedSearch partial and in parseQuery function
    $this->template = '';
    if (null !== $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template'))
    {
      $this->template = $infoObjectTemplate->getValue(array('sourceCulture'=>true));
    }

    // Stop if the input is not valid
    $this->form->bind($request->getRequestParameters() + $request->getGetParameters());
    if (!$this->form->isValid())
    {
      return;
    }

    // Store current params (facets, sort, etc) to add them as hidden inputs
    // in the form, to keep the selected facets and all on submit
    $this->hiddenFields = array();
    foreach ($request->getGetParameters() as $key => $value)
    {
      // Ignore:
      // - Params that exists in the form
      // - Criteria fields
      // - 'query' param (added to the criteria)
      // - 'showAdvanced' param (always added)
      if (in_array($key, $this::$NAMES) || 1 === preg_match('/^(sq|sf|so)\d+$/', $key)
        || in_array($key, array('query', 'showAdvanced')))
      {
        continue;
      }

      $this->hiddenFields[$key] = $value;
    }

    // Store data for search-filters
    if (isset($request->repos) && ctype_digit($request->repos))
    {
      $this->repos = QubitRepository::getById($request->repos);

      // Add repo to the user session as realm
      $this->context->user->setAttribute('search-realm', $request->repos);
    }

    if (isset($request->creators) && ctype_digit($request->creators))
    {
      $this->creators = QubitActor::getById($request->creators);
    }

    if (isset($request->names) && ctype_digit($request->names))
    {
      $this->names = QubitActor::getById($request->names);
    }

    if (isset($request->places) && ctype_digit($request->places))
    {
      $this->places = QubitTerm::getById($request->places);
    }

    if (isset($request->subjects) && ctype_digit($request->subjects))
    {
      $this->subjects = QubitTerm::getById($request->subjects);
    }

    if (isset($request->levels) && ctype_digit($request->levels))
    {
      $this->levels = QubitTerm::getById($request->levels);
    }

    if (isset($request->mediatypes) && ctype_digit($request->mediatypes))
    {
      $this->mediatypes = QubitTerm::getById($request->mediatypes);
    }

    if (isset($request->copyrightStatus) && ctype_digit($request->copyrightStatus))
    {
      $this->copyrightStatus = QubitTerm::getById($request->copyrightStatus);
    }

    if (isset($request->copyrightStatus) && ctype_digit($request->copyrightStatus))
    {
      $this->copyrightStatus = QubitTerm::getById($request->copyrightStatus);
    }

    if (isset($request->materialType) && ctype_digit($request->materialType))
    {
      $this->materialType = QubitTerm::getById($request->materialType);
    }

    if (isset($request->startDate) || isset($request->endDate))
    {
      $this->dateRange = '[ '.$request->startDate.' - '.$request->endDate.' ]';
    }

    // Add first criterion to the search box if it's over any field
    if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->sq0) && !isset($request->sf0))
    {
      $request->query = $request->sq0;
    }

    // And search box query to the first criterion
    if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->query))
    {
      $request->sq0 = $request->query;
    }

    // Build query with the boolean criteria
    if (null !== $criteria = $this->parseQuery())
    {
      $this->search->queryBool->addMust($criteria);
    }

    // Process date range, defaults to inclusive
    $this->rangeType = $this->request->rangeType;
    if (!isset($this->rangeType))
    {
      $this->rangeType = 'inclusive';
    }

    if (null !== $criteria = $this->getDateRangeQuery($request->startDate, $request->endDate, $this->rangeType))
    {
      $this->search->queryBool->addMust($criteria);
    }

    // Process advanced search form fields
    // Some of them have the same name as a facet, this creates query
    // duplication but allows as to keep facets and adv. search form syncronized
    foreach ($this->form as $field)
    {
      if (isset($this->request[$field->getName()]) && null !== $criteria = $this->processField($field))
      {
        $this->search->queryBool->addMust($criteria);
      }
    }

    // Default to show only top level descriptions
    $this->topLod = true;
    if (isset($request->topLod) && !filter_var($request->topLod, FILTER_VALIDATE_BOOLEAN))
    {
      $this->topLod = false;
    }
    else
    {
      $this->search->queryBool->addMust(new \Elastica\Query\Term(array('parentId' => QubitInformationObject::ROOT_ID)));
    }

    // Get all information objects if the query is empty
    if (1 > count($this->search->queryBool->getParams()))
    {
      $this->search->queryBool->addMust(new \Elastica\Query\MatchAll());
    }

    // Sort
    switch ($request->sort)
    {
      case 'identifier':
        $this->search->query->addSort(array('referenceCode.untouched' => 'asc'));

        break;

      // I don't think that this is going to scale, but let's leave it for now
      case 'alphabetic':
        $field = sprintf('i18n.%s.title.untouched', $this->selectedCulture);
        $this->search->query->addSort(array($field => 'asc'));

        break;

      case 'date':
        $this->search->query->setSort(array('dates.startDate' => 'asc'));

        break;

      case 'lastUpdated':
      default:
        $this->search->query->setSort(array('updatedAt' => 'desc'));
    }

    $this->search->query->setQuery($this->search->queryBool);

    // Filter drafts
    QubitAclSearch::filterDrafts($this->search->filterBool);

    // Set filter
    if (0 < count($this->search->filterBool->toArray()))
    {
      $this->search->query->setFilter($this->search->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $this->populateFacets($resultSet);
  }
}
