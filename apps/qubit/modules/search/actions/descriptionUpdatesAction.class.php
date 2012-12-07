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
      'dateStart',
      'dateEnd',
      'dateOf',
      'publicationStatus',
      'limit',
      'sort'
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

      case 'dateStart':
        $this->form->setValidator($name, new sfValidatorDate(array(), array('invalid' => $this->context->i18n->__('Invalid start date'))));
        $this->form->setWidget($name, new sfWidgetFormInput(array(), array('style' => 'width: auto')));

        break;

      case 'dateEnd':
        $this->form->setValidator($name, new sfValidatorDate(array(), array('invalid' => $this->context->i18n->__('Invalid end date'))));
        $this->form->setWidget($name, new sfWidgetFormInput(array(), array('style' => 'width: auto')));

        break;

      case 'dateOf':
        $choices = array(
          'CREATED_AT' => $this->context->i18n->__('Creation'),
          'UPDATED_AT' => $this->context->i18n->__('Revision'),
          'both' => $this->context->i18n->__('Both')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelectRadio(array('choices' => $choices, 'class' => 'radio inline')));

        break;

      case 'publicationStatus':
        $choices = array(
          QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID => QubitTerm::getById(QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)->name,
          QubitTerm::PUBLICATION_STATUS_DRAFT_ID => QubitTerm::getById(QubitTerm::PUBLICATION_STATUS_DRAFT_ID)->name,
          'all' => $this->context->i18n->__('All')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelectRadio(array('choices' => $choices, 'class' => 'radio inline')));

        break;

      case 'limit':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInputHidden);

        break;

      case 'sort':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInputHidden);

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
      'dateStart' => date('Y-m-d', strtotime('-1 month')),
      'dateEnd' => date('Y-m-d'),
      'dateOf' => 'CREATED_AT',
      'publicationStatus' => 'all',
      'limit' => '10',
      'sort' => 'updatedDown'
    );

    $this->form->bind($request->getRequestParameters() + $request->getGetParameters() + $defaults);

    if ($this->form->isValid())
    {
      $this->className = $this->form->getValue('className');

      $this->doSearch();
    }
  }

  public function doSearch()
  {
    $criteria = new Criteria;
    $this->sort = $this->request->getParameter('sort', 'updatedDown');

    // This join seems to be necessary to avoid cross joining the local table
    // with the QubitObject table
    $criteria->addJoin(constant($this->className.'::ID'), QubitObject::ID);

    switch ($this->form->getValue('className'))
    {
      case 'QubitActor':
        $nameColumn = 'authorized_form_of_name';
        $this->nameColumnDisplay = 'Name';
        $criteria = QubitActor::addGetOnlyActorsCriteria($criteria);
        $criteria->add(QubitActor::PARENT_ID, null, Criteria::ISNOTNULL);

        break;

      case 'QubitFunction':
        $nameColumn = 'authorized_form_of_name';
        $this->nameColumnDisplay = 'Name';

        break;

      case 'QubitRepository':
        $nameColumn = 'authorized_form_of_name';
        $this->nameColumnDisplay = 'Name';
        $criteria = QubitRepository::addGetOnlyRepositoryCriteria($criteria);

        break;

      case 'QubitTerm':
        $nameColumn = 'name';
        $this->nameColumnDisplay = 'Name';
        $criteria->add(QubitTerm::PARENT_ID, null, Criteria::ISNOTNULL);

        break;

      // Default: information object
      default:
        $nameColumn = 'title';
        $this->nameColumnDisplay = 'Title';
        $criteria->add(QubitInformationObject::PARENT_ID, null, Criteria::ISNOTNULL);
    }

    if ('QubitInformationObject' == $this->className && 'all' != $this->form->getValue('publicationStatus'))
    {
      $criteria->addJoin(QubitObject::ID, QubitStatus::OBJECT_ID);
      $criteria->add(QubitStatus::STATUS_ID, $this->form->getValue('publicationStatus'));
    }

    // End date at midnight
    if (null != $this->form->getValue('dateEnd'))
    {
      $dateEnd = $this->form->getValue('dateEnd').' 24:00:00';
    }

    // Add date criteria
    switch ($dateOf = $this->form->getValue('dateOf'))
    {
      case 'CREATED_AT':
      case 'UPDATED_AT':
        if (null !== $this->form->getValue('dateStart'))
        {
          $criteria->addAnd(constant('QubitObject::'.$dateOf), $this->form->getValue('dateStart'), Criteria::GREATER_EQUAL);
        }

        if (isset($dateEnd))
        {
          $criteria->addAnd(constant('QubitObject::'.$dateOf), $dateEnd, Criteria::LESS_EQUAL);
        }

        break;

      default:
        if (null !== $this->form->getValue('dateStart'))
        {
          $c1 = $criteria->getNewCriterion(QubitObject::CREATED_AT, $this->form->getValue('dateStart'), Criteria::GREATER_EQUAL);
          $c2 = $criteria->getNewCriterion(QubitObject::UPDATED_AT, $this->form->getValue('dateStart'), Criteria::GREATER_EQUAL);
          $c1->addOr($c2);
          $criteria->addAnd($c1);
        }

        if (isset($dateEnd))
        {
          $c3 = $criteria->getNewCriterion(QubitObject::CREATED_AT, $dateEnd, Criteria::LESS_EQUAL);
          $c4 = $criteria->getNewCriterion(QubitObject::UPDATED_AT, $dateEnd, Criteria::LESS_EQUAL);
          $c3->addOr($c4);
          $criteria->addAnd($c3);
        }
    }

    // Add sort criteria
    switch($this->sort)
    {
      case 'nameDown':
        $criteria->addDescendingOrderByColumn($nameColumn);

        break;

      case 'nameUp':
        $criteria->addAscendingOrderByColumn($nameColumn);

        break;

      case 'updatedUp':
        $criteria->addAscendingOrderByColumn(QubitObject::UPDATED_AT);

        break;

      case 'updatedDown':
      default:
       $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);
    }

    // Add fallback criteria for name
    if ('nameDown' == $this->sort || 'nameUp' == $this->sort)
    {
      $criteria = QubitCultureFallback::addFallbackCriteria($criteria, $this->form->getValue('className'));
    }

    // Page results
    $this->pager = new QubitPager($this->className);
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($this->form->getValue('limit'));
    $this->pager->setPage($this->request->getParameter('page', 1));
  }
}
