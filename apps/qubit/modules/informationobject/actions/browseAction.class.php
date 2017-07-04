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
      'rangeType',
      'findingAidStatus'
    ),

    $AGGS = array(
      'languages' =>
        array('type' => 'term',
              'field' => 'i18n.languages',
              'size' => 10),
      'levels' =>
        array('type' => 'term',
              'field' => 'levelOfDescriptionId',
              'size' => 10),
      'mediatypes' =>
        array('type' => 'term',
              'field' => 'digitalObject.mediaTypeId',
              'size' => 10),
      'digitalobjects' =>
        array('type' => 'filter',
              'field' => array('hasDigitalObject' => true),
              'populate' => false),
      'repos' =>
        array('type' => 'term',
              'field' => 'repository.id',
              'size' => 10),
      'places' =>
        array('type'   => 'term',
              'field'  => 'places.id',
              'size'   => 10),
      'subjects' =>
        array('type'   => 'term',
              'field'  => 'subjects.id',
              'size'   => 10),
      'genres' =>
        array('type'   => 'term',
              'field'  => 'genres.id',
              'size'   => 10),
      'creators' =>
        array('type'   => 'term',
              'field'  => 'creators.id',
              'size'   => 10),
      'names' =>
        array('type'   => 'term',
              'field'  => 'names.id',
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
        $criteria = new Criteria;
        $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);

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
        $cacheKey = 'search:list-of-repositories:'.$this->context->user->getCulture();
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

      case 'findingAidStatus':
        $choices = array(
          ''  => '',
          'yes' => $this->context->i18n->__('Yes'),
          'no' => $this->context->i18n->__('No'),
          'generated' => $this->context->i18n->__('Generated'),
          'uploaded' => $this->context->i18n->__('Uploaded')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;
    }
  }

  protected function populateAgg($name, $buckets)
  {
    switch ($name)
    {
      case 'repos':
        $ids = array_column($buckets, 'key');
        $criteria = new Criteria;
        $criteria->add(QubitRepository::ID, $ids, Criteria::IN);

        foreach (QubitRepository::get($criteria) as $item)
        {
          $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
        }

        break;

      case 'levels':
      case 'mediatypes':
      case 'places':
      case 'subjects':
      case 'genres':
        $ids = array_column($buckets, 'key');
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, $ids, Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $buckets[array_search($item->id, $ids)]['display'] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'creators':
      case 'names':
        $ids = array_column($buckets, 'key');
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, $ids, Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
        }

        break;

      case 'collection':
        $ids = array_column($buckets, 'key');
        $criteria = new Criteria;
        $criteria->add(QubitInformationObject::ID, $ids, Criteria::IN);

        foreach (QubitInformationObject::get($criteria) as $item)
        {
          $buckets[array_search($item->id, $ids)]['display'] = $item->__toString();
        }

        break;

      default:
        return parent::populateAgg($name, $buckets);
    }

    return $buckets;
  }

  protected function setHiddenFields($request)
  {
    // Store current params (aggs, sort, etc) to add them as hidden inputs
    // in the form, to keep the selected aggs and all on submit
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
  }

  protected function setFilterTags($request)
  {
    // Store data for search-filters
    if (isset($request->repos) && ctype_digit($request->repos))
    {
      $this->repos = QubitRepository::getById($request->repos);

      // Add repo to the user session as realm
      $this->context->user->setAttribute('search-realm', $request->repos);
    }
    else if (sfConfig::get('app_enable_institutional_scoping') &&
      !(isset($request->collection) && ctype_digit($request->collection)))
    {
      // Remove realm
      $this->context->user->removeAttribute('search-realm');
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

    if (!empty($request->findingAidStatus))
    {
      $i18n = $this->context->i18n;

      switch ($request->findingAidStatus)
      {
        case 'yes':
          $this->findingAidStatusTag = $i18n->__('With finding aid');

          break;

        case 'no':
          $this->findingAidStatusTag = $i18n->__('Without finding aid');

          break;

        case 'generated':
          $this->findingAidStatusTag = $i18n->__('With generated finding aid');

          break;

        case 'uploaded':
          $this->findingAidStatusTag = $i18n->__('With uploaded finding aid');

          break;
      }
    }
  }

  public function execute($request)
  {
    // To keep the top level descriptions filter an agg in sync
    // the autocomplete value is converted to the resource id
    // before the agg filters are added to the query
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

    // Add first criterion to the search box if it's over any field
    if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->sq0) && !isset($request->sf0))
    {
      $request->query = $request->sq0;
      $this->getParameters['query'] = $request->sq0;
    }

    // And search box query to the first criterion
    if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->query))
    {
      $request->sq0 = $request->query;
      $this->getParameters['sq0'] = $request->query;
    }

    // Create the query and filter it with the selected aggs
    parent::execute($request);

    // Create advanced search form
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    // Get actual information object template to check archival history
    // visibility in _advancedSearch partial and in parseQuery function
    $this->template = 'isad';
    if (null !== $infoObjectTemplate = QubitSetting::getByNameAndScope('informationobject', 'default_template'))
    {
      $this->template = $infoObjectTemplate->getValue(array('sourceCulture'=>true));
    }

    // Add print preview style
    if ('print' == $request->media)
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    // Default to hide the advanced search panel
    $this->showAdvanced = false;
    if (filter_var($request->showAdvanced, FILTER_VALIDATE_BOOLEAN))
    {
      $this->showAdvanced = true;
    }

    // Default to show only top level descriptions
    $this->topLod = true;
    if (isset($request->topLod) && !filter_var($request->topLod, FILTER_VALIDATE_BOOLEAN))
    {
      $this->topLod = false;
    }

    // Defaults to inclusive date range type
    $this->rangeType = 'inclusive';
    if (isset($request->rangeType))
    {
      $this->rangeType = $request->rangeType;
    }

    $this->setHiddenFields($request);
    $this->setFilterTags($request);

    // Add advanced form filter to the query
    $this->search->addAdvancedSearchFilters($this::$NAMES, $this->getParameters, $this->template);

    // Stop if the input is not valid. It must be after the query is created but before
    // it's executed to keep the boolean search and other params for the next request
    $this->form->bind($request->getRequestParameters() + $request->getGetParameters());
    if (!$this->form->isValid())
    {
      return;
    }

    // Sort
    switch ($request->sort)
    {
      // Sort by highest ES score
      case 'relevance':
        $this->search->query->addSort(array('_score' => 'desc'));

        break;

      case 'identifier':
        $this->search->query->addSort(array('identifier' => 'asc'));

        break;

      case 'referenceCode':
        $this->search->query->addSort(array('referenceCode.untouched' => 'asc'));

        break;

      case 'alphabetic':
        $field = sprintf('i18n.%s.title.untouched', $this->selectedCulture);
        $this->search->query->addSort(array($field => 'asc'));

        break;

      case 'startDate':
        $this->search->query->setSort(array('dates.startDate' => 'asc'));

        break;

      case 'endDate':
        $this->search->query->setSort(array('dates.endDate' => 'desc'));

        break;

      case 'lastUpdated':
      default:
        $this->search->query->setSort(array('updatedAt' => 'desc'));
    }

    $this->setView($request);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->getQuery(false, true));

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($this->limit);
    $this->pager->init();

    $this->populateAggs($resultSet);
  }

  /**
   * Set browse page layout view
   */
  private function setView($request)
  {
    $this->cardView = 'card';
    $this->tableView = 'table';
    $allowedViews = array($this->cardView, $this->tableView);

    if (isset($request->view) && in_array($request->view, $allowedViews))
    {
      $this->view = $request->view;
    }
    else
    {
      $this->view = sfConfig::get('app_default_archival_description_browse_view', $this->tableView);
    }
  }
}
