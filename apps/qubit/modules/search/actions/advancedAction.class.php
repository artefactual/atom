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
      's' // 'searchFields'
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
          $params = $this->context->routing->parse(Qubit::pathInfo($this->request->f));
          $fonds = $params['_sf_route']->resource;

          if ($fonds instanceof QubitInformationObject)
          {
            $this->form->setDefault($name, $this->request->f);

            $choices[$this->request->f] = $params['_sf_route']->resource;
          }
        }

        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;
    }
  }

  protected function processField($field)
  {
    if (null === $value = $this->form->getValue($field->getName()))
    {
      return;
    }

    switch ($field->getName())
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

          $this->queryBool->addMust($queryBool);
        }
        else
        {
          $query = new \Elastica\Query\Term;
          $query->setTerm('copyrightStatusId', $value);
          $this->queryBool->addMust($query);
        }

        break;

      case 'h':
        $query = new \Elastica\Query\Term;
        $query->setTerm('hasDigitalObject', $value);
        $this->queryBool->addMust($query);

        break;

      case 'l':
        $query = new \Elastica\Query\Term;
        $query->setTerm('levelOfDescriptionId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'm':
        $query = new \Elastica\Query\Term;
        $query->setTerm('materialTypeId', $value);
        $this->queryBool->addMust($query);

        break;

      case 't':
        $query = new \Elastica\Query\Term;
        $query->setTerm('digitalObject.mediaTypeId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'r':
        $query = new \Elastica\Query\Term;
        $query->setTerm('repository.id', $value);
        $this->queryBool->addMust($query);

        break;

      case 'f':
        $params = $this->context->routing->parse(Qubit::pathInfo($value));
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

          $this->queryBool->addMust($query);
        }

        break;
    }
  }

  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();
    $culture = $this->context->user->getCulture();

    $count = -1;
    $this->criterias = array();
    while (null !== $query = $this->request->getParameter('sq'.++$count))
    {
      if (empty($query)) continue;

      $field = $this->request->getParameter('sf'.$count, '_all');
      $operator = $this->request->getParameter('so'.$count, 'or');

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
          $queryField = new \Elastica\Query\QueryString($query);
          $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.archivalHistory'));
          $queryField->setDefaultOperator('OR');

          break;

        case 'extentAndMedium':
          $queryField = new \Elastica\Query\QueryString($query);
          $queryField->setFields(arElasticSearchPluginUtil::getI18nFieldNames('i18n.%s.extentAndMedium'));
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

          arElasticSearchPluginUtil::setAllFields($queryField, $options = array('type' => 'informationObject'));

          break;
      }

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

      $this->criterias[] = array(
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

    // Stop if the input is not valid
    $this->form->bind($request->getRequestParameters() + $request->getGetParameters());
    if (!$this->form->isValid())
    {
      throw new sfException;
    }

    // Bulding a \Elastica\Query\Bool object from the search criterias
    if (null !== $criterias = $this->parseQuery())
    {
      $this->queryBool->addMust($criterias);
    }

    // Process sidebar filters (as sfForm fields)
    foreach ($this->form as $field)
    {
      if (isset($this->request[$field->getName()]))
      {
        $this->processField($field);
      }
    }

    // Stop execution if zero results
    if (1 > count($this->queryBool->getParams()))
    {
      return;
    }

    $this->query->setQuery($this->queryBool);

    // Filter drafts
    QubitAclSearch::filterDrafts($this->filterBool);

    // Set filter
    if (0 < count($this->filterBool->toArray()))
    {
      $this->query->setFilter($this->filterBool);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
