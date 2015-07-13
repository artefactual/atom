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

class SearchAdvancedAction extends DefaultBrowseAction
{
  public static
    $NAMES = array(
      'c', // 'copyrightStatus',
      'h', // 'hasDigitalObject',
      'l', // 'levelOfDescription',
      'm', // 'materialType',
      't', // 'mediaType',
      'r', // 'repository',
      'f', // 'fonds/collection'
      's', // 'searchFields'
      'sd', // 'startDate'
      'ed' // 'endDate'
    );

  protected function addField($name)
  {
    switch ($name)
    {
      case 'c':
        $this->form->setValidator('c', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator('c', new sfValidatorString);
        $this->form->setWidget('c', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'h':
        $choices = array(
          '' => '',
          'true' => $this->context->i18n->__('Yes'),
          'false' => $this->context->i18n->__('No')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'l':
        $this->form->setValidator('l', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator('l', new sfValidatorString);
        $this->form->setWidget('l', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'm':
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

      case 't':
        // Get list of media types
        $criteria = new Criteria;
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::MEDIA_TYPE_ID);

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

      case 'r':
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

      case 'f':
        $this->form->setValidator($name, new sfValidatorString);

        $choices = array();
        if (isset($this->request->f) && strlen($this->request->f) > 0)
        {
          $params = sfContext::getInstance()->routing->parse(Qubit::pathInfo($this->request->f));
          $fonds = $params['_sf_route']->resource;

          if ($fonds instanceof QubitInformationObject)
          {
            $this->form->setDefault($name, $this->request->f);

            $choices[$this->request->f] = $params['_sf_route']->resource;
          }
        }

        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'sd':
      case 'ed':
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
      case 'c':
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

      case 'h':
        $query = new \Elastica\Query\Term;
        $query->setTerm('hasDigitalObject', $value);
        return $query;

        break;

      case 'l':
        $query = new \Elastica\Query\Term;
        $query->setTerm('levelOfDescriptionId', $value);
        return $query;

        break;

      case 'm':
        $query = new \Elastica\Query\Term;
        $query->setTerm('materialTypeId', $value);
        return $query;

        break;

      case 't':
        $query = new \Elastica\Query\Term;
        $query->setTerm('digitalObject.mediaTypeId', $value);
        return $query;

        break;

      case 'r':
        $query = new \Elastica\Query\Term;
        $query->setTerm('repository.id', $value);
        return $query;

        break;

      case 'f':
        $params = sfContext::getInstance()->routing->parse(Qubit::pathInfo($value));
        $fonds = $params['_sf_route']->resource;

        if ($fonds instanceof QubitInformationObject)
        {
          $query = new \Elastica\Query\Bool();

          $queryAncestors = new \Elastica\Query\Term;
          $queryAncestors->setTerm('ancestors', $fonds->id);
          $query->addShould($queryAncestors);

          $querySelf = new \Elastica\Query\Match();
          $querySelf->setFieldQuery('slug', $fonds->slug);
          $query->addShould($querySelf);

          return $query;
        }

        break;
    }
  }

  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();
    $culture = $this->context->user->getCulture();

    $count = -1;
    $this->criteria = array();
    while (null !== $query = $this->request->getParameter('sq'.++$count))
    {
      if (empty($query)) continue;

      $field = $this->request->getParameter('sf'.$count, '_all');
      $operator = $this->request->getParameter('so'.$count, 'or');

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
            || ($archivalStandard != 'isad' && $$archivalStandard != 'rad'))
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

  static function getDateRangeQuery($start, $end)
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

      // Start date before range and end date missing
      $queryStart = new \Elastica\Query\Range('dates.startDate', array('lt' => $start));
      $filter = new \Elastica\Filter\Missing;
      $filter->setField('dates.endDate');
      $filteredQuery = new \Elastica\Query\Filtered($queryStart, $filter);

      $query->addShould($filteredQuery);
    }

    if (!empty($end))
    {
      $range['lte'] = $end;

      // End date after range and start date missing
      $queryEnd = new \Elastica\Query\Range('dates.endDate', array('gt' => $end));
      $filter = new \Elastica\Filter\Missing;
      $filter->setField('dates.startDate');
      $filteredQuery = new \Elastica\Query\Filtered($queryEnd, $filter);

      $query->addShould($filteredQuery);
    }

    if (!empty($start) && !empty($end))
    {
      // Start date before range and end date after range
      $queryBool = new \Elastica\Query\Bool();
      $queryBool->addMust(new \Elastica\Query\Range('dates.startDate', array('lt' => $start)));
      $queryBool->addMust(new \Elastica\Query\Range('dates.endDate', array('gt' => $end)));

      $query->addShould($queryBool);
    }

    // Any event date inside the range
    $query->addShould(new \Elastica\Query\Range('dates.startDate', $range));
    $query->addShould(new \Elastica\Query\Range('dates.endDate', $range));

    // Use nested query and mapping object to allow querying
    // over the start and end dates from the same event
    $queryNested = new \Elastica\Query\Nested();
    $queryNested->setPath('dates');
    $queryNested->setQuery($query);

    return $queryNested;
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    // Get actual information object template to check archival history
    // visibility in _searchFields partial and in parseQuery function
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

    // Bulding a \Elastica\Query\Bool object from the search criteria
    if (null !== $criteria = $this->parseQuery())
    {
      $this->search->queryBool->addMust($criteria);
    }

    // Process sidebar filters (as sfForm fields)
    foreach ($this->form as $field)
    {
      if (isset($this->request[$field->getName()]))
      {
        // Bulding a \Elastica\Query\Bool object from the search criteria
        if (null !== $criteria = $this->processField($field))
        {
          $this->search->queryBool->addMust($criteria);
        }
      }
    }

    // Process date range
    if (null !== $criteria = $this->getDateRangeQuery($this->request['sd'], $this->request['ed']))
    {
      $this->search->queryBool->addMust($criteria);
    }

    // Stop execution if zero results
    if (1 > count($this->search->queryBool->getParams()))
    {
      return;
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
  }
}
