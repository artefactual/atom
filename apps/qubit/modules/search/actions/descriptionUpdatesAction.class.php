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
 * Display a list of recently updates to the db
 *
 * @package AccesstoMemory
 * @subpackage search
 * @author David Juhasz <david@artefactual.com>
 */
class SearchDescriptionUpdatesAction extends sfAction
{
  public static
    $NAMES = array(
      'className',
      'startDate',
      'endDate',
      'dateOf',
      'publicationStatus',
      'repository'
    );

  protected function addField($name)
  {
    switch ($name)
    {
      case 'className':
        $choices = array(
          'QubitInformationObject' => sfConfig::get('app_ui_label_informationobject'),
          'QubitActor' => sfConfig::get('app_ui_label_actor'),
          'QubitRepository' => sfConfig::get('app_ui_label_repository'),
          'QubitTerm' => sfConfig::get('app_ui_label_term'),
          'QubitFunction' => sfConfig::get('app_ui_label_function'));

        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'startDate':
        $this->form->setValidator($name, new sfValidatorDate(array(), array('invalid' => $this->context->i18n->__('Invalid start date'))));
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'endDate':
        $this->form->setValidator($name, new sfValidatorDate(array(), array('invalid' => $this->context->i18n->__('Invalid end date'))));
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'dateOf':
        $choices = array(
          'CREATED_AT' => $this->context->i18n->__('Creation'),
          'UPDATED_AT' => $this->context->i18n->__('Revision'),
          'both' => $this->context->i18n->__('Both')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new arWidgetFormSelectRadio(array('choices' => $choices, 'class' => 'radio inline')));

        break;

      case 'publicationStatus':
        $choices = array(
          QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID => QubitTerm::getById(QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)->name,
          QubitTerm::PUBLICATION_STATUS_DRAFT_ID => QubitTerm::getById(QubitTerm::PUBLICATION_STATUS_DRAFT_ID)->name,
          'all' => $this->context->i18n->__('All')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new arWidgetFormSelectRadio(array('choices' => $choices, 'class' => 'radio inline')));

        break;

      case 'repository':
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
    }
  }

  public function execute($request)
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    $defaults = array(
      'className' => 'QubitInformationObject',
      'startDate' => date('Y-m-d', strtotime('-1 month')),
      'endDate' => date('Y-m-d'),
      'dateOf' => 'CREATED_AT',
      'publicationStatus' => 'all',
      'repository' => null
    );

    $this->form->bind($request->getGetParameters() + $defaults);

    if ($this->form->isValid())
    {
      $this->className = $this->form->getValue('className');
      $nameColumnDisplay = $this->className == ('QubitInformationObject') ? 'Title' : 'Name';
      $this->nameColumnDisplay = $this->context->i18n->__($nameColumnDisplay);
      $this->doSearch();
    }

    $this->showForm = $this->request->getParameter('showForm');
  }

  public function doAuditLogSearch()
  {
    // Criteria to fetch user actions
    $criteria = new Criteria;
    $criteria->addJoin(QubitAuditLog::OBJECT_ID, QubitInformationObject::ID);

    // Add publication status filtering, if specified
    if ($this->form->getValue('publicationStatus') != 'all')
    {
      $criteria->addJoin(QubitAuditLog::OBJECT_ID, QubitStatus::OBJECT_ID);
      $criteria->add(QubitStatus::STATUS_ID, $this->form->getValue('publicationStatus'));
    }

    // Add user action type filtering, if specified
    if ($this->form->getValue('dateOf') != 'both')
    {
      switch($this->form->getValue('dateOf'))
      {
        case 'CREATED_AT':
          $criteria->add(QubitAuditLog::ACTION_TYPE_ID, QubitTerm::USER_ACTION_CREATION_ID);
          break;

        case 'UPDATED_AT':
          $criteria->add(QubitAuditLog::ACTION_TYPE_ID, QubitTerm::USER_ACTION_MODIFICATION_ID);
          break;
      }
    }

    // Add repository restriction, if specified
    if (null !== $this->form->getValue('repository'))
    {
      $criteria->add(QubitInformationObject::REPOSITORY_ID, $this->form->getValue('repository'));
    }

    // Add date restriction
    $criteria->add(QubitAuditLog::CREATED_AT , $this->form->getValue('startDate'), Criteria::GREATER_EQUAL);
    $endDateTime = new DateTime($this->form->getValue('endDate'));
    $criteria->add(QubitAuditLog::CREATED_AT, $endDateTime->modify('+1 day')->format('Y-m-d'), Criteria::LESS_THAN);

    // Sort in reverse chronological order
    $criteria->addDescendingOrderByColumn(QubitAuditLog::CREATED_AT);

    // Page results
    $limit = sfConfig::get('app_hits_per_page');
    $page = (isset($request->page) && ctype_digit($request->page)) ? $request->page : 1;

    $this->pager = new QubitPager('QubitAuditLog');
    $this->pager->setCriteria($criteria);
    $this->pager->setPage($page);
    $this->pager->setMaxPerPage($limit);

    $this->pager->init();
  }

  public function doSearch()
  {
    if ('QubitInformationObject' == $this->className && sfConfig::get('app_audit_log_enabled', false))
    {
      return $this->doAuditLogSearch();
    }

    $queryBool = new \Elastica\Query\BoolQuery;

    if ('QubitInformationObject' == $this->className)
    {
      if ('all' != $this->form->getValue('publicationStatus'))
      {
        $queryBool->addMust(new \Elastica\Query\Term(array('publicationStatusId' => $this->form->getValue('publicationStatus'))));
      }

      if (null !== $this->form->getValue('repository'))
      {
        $queryBool->addMust(new \Elastica\Query\Term(array('repository.id' => $this->form->getValue('repository'))));
      }
    }

    $this->addDateRangeQuery($queryBool, $this->form->getValue('dateOf'));

    $query = new \Elastica\Query($queryBool);

    $limit = sfConfig::get('app_hits_per_page', 10);
    if (isset($this->request->limit) && ctype_digit($this->request->limit))
    {
      $limit = $this->request->limit;
    }

    $page = 1;
    if (isset($this->request->page) && ctype_digit($this->request->page))
    {
      $page = $this->request->page;
    }

    // Avoid pagination over 10000 records
    if ((int)$limit * $page > 10000)
    {
      // Show alert
      $message = $this->context->i18n->__("We've redirected you to the first page of results. To avoid using vast amounts of memory, AtoM limits pagination to 10,000 records. Please, narrow down your results.");
      $this->getUser()->setFlash('notice', $message);

      // Redirect to fist page
      $params = $this->request->getParameterHolder()->getAll();
      unset($params['page']);
      $this->redirect($params);
    }

    $query->setSize($limit);
    $query->setFrom($limit * ($page - 1));
    $query->setSort(array('createdAt' => 'desc'));

    $resultSet = QubitSearch::getInstance()->index->getType($this->form->getValue('className'))->search($query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setMaxPerPage($limit);
    $this->pager->setPage($page);
    $this->pager->init();
  }

  private function addDateRangeQuery($queryBool, $dateOf)
  {
    switch ($dateOf)
    {
      case 'CREATED_AT':
        $this->addDateRangeQueryClause($queryBool, 'createdAt', $this->form->getValue('startDate'), $this->form->getValue('endDate'));

        break;

      case 'UPDATED_AT':
        $this->addDateRangeQueryClause($queryBool, 'updatedAt', $this->form->getValue('startDate'), $this->form->getValue('endDate'));

        break;

      default:
        // Subquery for finding created at dates within range
        $createdAtQueryBool = new \Elastica\Query\BoolQuery;
        $this->addDateRangeQueryClause($createdAtQueryBool, 'createdAt', $this->form->getValue('startDate'), $this->form->getValue('endDate'));

        // Subquery for finding updated at dates within range
        $updatedAtQueryBool = new \Elastica\Query\BoolQuery;
        $this->addDateRangeQueryClause($updatedAtQueryBool, 'updatedAt', $this->form->getValue('startDate'), $this->form->getValue('endDate'));

        // Combined subquery
        $bothDatesQueryBool = new \Elastica\Query\BoolQuery;
        $bothDatesQueryBool->addShould($createdAtQueryBool);
        $bothDatesQueryBool->addShould($updatedAtQueryBool);

        $queryBool->addMust($bothDatesQueryBool);
    }
  }

  private function addDateRangeQueryClause($queryBool, $field, $startDate, $endDate)
  {
    if (null !== $startDate)
    {
      $queryBool->addMust(new \Elastica\Query\Range($field, array('gte' => $startDate)));
    }

    if (null !== $endDate)
    {
      $queryBool->addMust(new \Elastica\Query\Range($field, array('lte' => $endDate)));
    }
  }
}
