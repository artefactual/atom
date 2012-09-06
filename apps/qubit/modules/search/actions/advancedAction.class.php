<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class SearchAdvancedAction extends SearchIndexAction
{
  public static
    $NAMES = array(
      'copyrightStatus',
      'hasDigitalObject',
      'levelOfDescription',
      'materialType',
      'mediaType',
      'repository',
      'searchFields'
    );

  public function execute($request)
  {
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

    $this->form->bind($request->getRequestParameters() + $request->getGetParameters() + $request->getPostParameters());

    if ($this->form->isValid())
    {
      if (isset($request->searchFields))
      {
        $this->queryTerms = array();

        // we are handling a search request
        parent::execute($request);
      }
    }

  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'copyrightStatus':
        $this->form->setValidator('copyrightStatus', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator('copyrightStatus', new sfValidatorString);
        $this->form->setWidget('copyrightStatus', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'hasDigitalObject':
        $choices = array(
          '' => '',
          'true' => $this->context->i18n->__('Yes'),
          'false' => $this->context->i18n->__('No')
        );

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'levelOfDescription':
        $this->form->setValidator('levelOfDescription', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID) as $item)
        {
          $choices[$item->id] = $item->__toString();
        }

        $this->form->setValidator('levelOfDescription', new sfValidatorString);
        $this->form->setWidget('levelOfDescription', new sfWidgetFormSelect(array('choices' => $choices)));

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

      case 'mediaType':
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

      case 'repository':
        // Get list of repositories
        $criteria = new Criteria;

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

        $criteria->addAscendingOrderByColumn('authorized_form_of_name');

        $choices = array();
        $choices[null] = null;
        foreach (QubitRepository::get($criteria) as $repository)
        {
          $choices[$repository->id] = $repository->__toString();
        }

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'searchFields':

        break;
    }
  }

  public function parseQuery()
  {
    QubitSearch::getInstance();
    $queryBuilt = new Zend_Search_Lucene_Search_Query_Boolean();

    foreach ($this->request->searchFields as $searchField)
    {
      // if no terms for this field, skip it
      if (empty($searchField['query']))
      {
        continue;
      }

      // enclose phrase searches in quotes (strip existing ones)
      if ('phrase' == $searchField['match'])
      {
        $term = '"'.str_replace(array('"', "'"), '', strtolower($searchField['query'])).'"';
      }
      else
      {
        $term = strtolower($searchField['query']);
      }

      $matchString = $term;

      // limit to specified field
      if (!empty($searchField['field']))
      {
        $term = $searchField['field'] . ':' . $term;
      }

      if (!empty($searchField['field']))
      {
        $field = ucfirst($searchField['field']);
      }
      else
      {
        $field = ('phrase' == $searchField['match']) ? $this->context->i18n->__('Phrase') : $this->context->i18n->__('Keyword(s)');
      }

      $this->queryTerms[] = array('term' => $field.': '.$matchString, 'operator' => $searchField['operator']);

      // select which boolean operator to use
      if (!isset($searchField['operator'])) $searchField['operator'] = null;
      switch ($searchField['operator'])
      {
        case 'not':
          $token = false;
          break;

        case 'or':
          $token = null;
          break;

        case 'and':
        default:
          $token = true;
          break;
      }

      $queryBuilt->addSubquery(QubitSearch::getInstance()->parse($term), $token);
    }

    $query = new Zend_Search_Lucene_Search_Query_Boolean();
    $query->addSubquery($queryBuilt, true);

    return $query;
  }

  public function filterQuery($query)
  {
    // limit to a repository if selected
    if (!empty($this->request->repository))
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm($this->request->repository, 'repositoryId'), true);
      $this->queryTerms[] = array('term' => $this->context->i18n->__('Repository').': '.QubitRepository::getById($this->request->repository)->__toString(), 'operator' => 'and');
    }

    // digital object filters
    if ('true' == $this->request->hasDigitalObject)
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm('true', 'hasDigitalObject'), true);
      $this->queryTerms[] = array('term' => $this->context->i18n->__('Digital object is available'), 'operator' => 'and');
    }
    else if ('false' == $this->request->hasDigitalObject)
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm('false', 'hasDigitalObject'), true);
      $this->queryTerms[] = array('term' => $this->context->i18n->__('No digital object is available'), 'operator' => 'and');
    }

    // limit to a media type if selected
    if (!empty($this->request->mediaType))
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm($this->request->mediaType, 'mediaTypeId'), true);
      $this->queryTerms[] = array('term' => 'mediaType: '.QubitTerm::getById($this->request->mediaType)->__toString(), 'operator' => 'and');
    }

    // limit to a material type if selected
    if (!empty($this->request->materialType))
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm($this->request->materialType, 'materialTypeId'), true);
      $this->queryTerms[] = array('term' => 'materialType: '.QubitTerm::getById($this->request->materialType)->__toString(), 'operator' => 'and');
    }

    if (!empty($this->request->levelOfDescription))
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm($this->request->levelOfDescription, 'levelOfDescriptionId'), true);
      $this->queryTerms[] = array('term' => $this->context->i18n->__('Level of description').': '.$this->request->levelOfDescription, 'operator' => 'and');
    }

    // Copyright status
    if (!empty($this->request->copyrightStatus))
    {
      $query->addSubquery(QubitSearch::getInstance()->addTerm($this->request->copyrightStatus, 'copyrightStatusId'), true);
      $this->queryTerms[] = array('term' => 'copyrightStatus: '.QubitTerm::getById($this->request->copyrightStatus)->__toString(), 'operator' => 'and');
    }

    $query = parent::filterQuery($query);

    return $query;
  }
}
