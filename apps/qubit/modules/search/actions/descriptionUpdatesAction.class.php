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
      'publicationStatus'
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

    $this->response->addJavaScript('clipboardToggleAll', 'last');

    $defaults = array(
      'className' => 'QubitInformationObject',
      'startDate' => date('Y-m-d', strtotime('-1 month')),
      'endDate' => date('Y-m-d'),
      'dateOf' => 'CREATED_AT',
      'publicationStatus' => 'all'
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

  public function doSearch()
  {
    $queryBool = new \Elastica\Query\BoolQuery;

    if ('QubitInformationObject' == $this->className && 'all' != $this->form->getValue('publicationStatus'))
    {
      $queryBool->addMust(new \Elastica\Query\Term(array('publicationStatusId' => $this->form->getValue('publicationStatus'))));
    }

    $this->addDateRangeQuery($queryBool, $this->form->getValue('dateOf'));

    $query = new \Elastica\Query($queryBool);
    $limit = sfConfig::get('app_hits_per_page', 10);
    $page = $this->request->getParameter('page', 1);

    $query->setLimit($limit);
    $query->setFrom($limit * ($page - 1));
    $query->setSort(array('createdAt' => 'desc'));

    $resultSet = QubitSearch::getInstance()->index->getType($this->form->getValue('className'))->search($query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setMaxPerPage($limit);
    $this->pager->setPage($this->request->getParameter('page', 1));
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
