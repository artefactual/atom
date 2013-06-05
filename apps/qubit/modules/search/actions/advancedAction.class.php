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
      's'  // 'searchFields'
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
    }
  }

  protected function processField($field)
  {
    if (null === $value = $this->form->getValue($field->getName()))
    {
      return;
    }

    $this->hasFilters = true;

    switch ($field->getName())
    {
      case 'c':
        $query = new \Elastica\Query\Term;
        $query->setTerm('copyrightStatusId', $value);
        $this->queryBool->addMust($query);

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

      case 'm':
        $query = new \Elastica\Query\Term;
        $query->setTerm('digitalObject.mediaTypeId', $value);
        $this->queryBool->addMust($query);

        break;

      case 'r':
        $query = new \Elastica\Query\Term;
        $query->setTerm('repository.id', $value);
        $this->queryBool->addMust($query);

        break;
    }
  }

  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();

    if (!isset($this->request->s))
    {
      return;
    }

    $culture = $this->context->user->getCulture();

    // Iterate over search fields
    foreach ($this->request->s as $key => $item)
    {
      if (empty($item['query']))
      {
        continue;
      }

      $queryText = new \Elastica\Query\Text();

      switch ($item['field'])
      {
        case 'identifier':
          $queryText->setFieldQuery('identifier', $item['query']);

          break;

        case 'title':
          $queryText->setFieldQuery('i18n.'.$culture.'.title', $item['query']);

          break;

        case 'scopeAndContent':
          $queryText->setFieldQuery('i18n.'.$culture.'.scopeAndContet', $item['query']);

          break;

        case 'archivalHistory':
          $queryText->setFieldQuery('i18n.'.$culture.'.archivalHistory', $item['query']);

          break;

        case 'extentAndMedium':
          $queryText->setFieldQuery('i18n.'.$culture.'.extentAndMedium', $item['query']);

          break;

        case 'creatorHistory':
          $queryText->setFieldQuery('', $item['query']);

          break;

        case 'subject':
          $queryText->setFieldQuery('', $item['query']);

          break;

        case 'name':
          $queryText->setFieldQuery('', $item['query']);

          break;

        case 'place':
          $queryText->setFieldQuery('', $item['query']);

          break;

        default:
          $queryText->setFieldQuery('_all', $item['query']);

          break;
      }

      if (0 == $key)
      {
        $item['o'] == 'add';
      }

      switch ($item['o'])
      {
        case 'not':
          $queryBool->addMustNot($queryText);

          break;

        case 'or':
          $queryBool->addShould($queryText);

          break;

        case 'add':
        default:
          $queryBool->addMust($queryText);

          break;
      }
    }

    if (0 == count($queryBool->getParams()))
    {
      return;
    }

    return $queryBool;
  }

  public function execute($request)
  {
    # echo "<pre>";
    # var_dump($_GET);
    # die();
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
    $this->form->bind($request->getRequestParameters() + $request->getGetParameters() + $request->getPostParameters());
    if (!$this->form->isValid())
    {
      throw new sfException;
    }

    // Bulding a \Elastica\Query\Bool object from the search criterias
    if (null !== $criterias = $this->parseQuery())
    {
      $this->queryBool->addMust($criterias);
    }

    // Process form fields
    foreach ($this->form as $field)
    {
      if (isset($this->request[$field->getName()]))
      {
        $this->processField($field);
      }
    }

    // Filter drafts
    $this->query = QubitAclSearch::filterDrafts($this->query);

    // Sort
    # $this->query->setSort(array($field => 'desc'));

    $this->query->setQuery($this->queryBool);

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }
}
