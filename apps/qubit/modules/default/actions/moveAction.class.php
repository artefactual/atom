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

class DefaultMoveAction extends sfAction
{
  public function execute($request)
  {
    // Default items per page
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    // Check that the object exists and that it is not the root
    if (!isset($this->resource) || !isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // "parent" form field
    $this->form->setValidator('parent', new sfValidatorString(array('required' => true)));
    $this->form->setWidget('parent', new sfWidgetFormInputHidden);

    // Root is default parent
    if ($this->resource instanceof QubitInformationObject)
    {
      $this->form->bind($request->getGetParameters() + array('parent' => QubitInformationObject::getById(QubitInformationObject::ROOT_ID)->slug, 'module' => 'informationobject'));
    }
    else if ($this->resource instanceof QubitTerm)
    {
      $this->form->bind($request->getGetParameters() + array('parent' => QubitTerm::getById(QubitTerm::ROOT_ID)->slug, 'module' => 'term'));
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $parent = QubitObject::getBySlug($this->form->parent->getValue());

        $params = array(
          'objectId' => $this->resource->id,
          'parentId' => $parent->id
        );

        QubitJob::runJob('arObjectMoveJob', $params);

        // Notify user move has started
        sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

        $jobManageUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
        $jobManageLink = '<a href="'. $jobManageUrl . '">'. $this->context->i18n->__('job management') .'</a>';

        $message = '<strong>'. $this->context->i18n->__('Move initiated.') .'</strong> ';
        $message .= $this->context->i18n->__("If job hasn't already completed, check %1% page to determine present status.", array('%1%' => $jobManageLink));
        $this->getUser()->setFlash('notice', $message);

        if ($request->isXmlHttpRequest())
        {
          return $this->renderText('');
        }
      }
    }

    $this->parent = QubitObject::getBySlug($this->form->parent->getValue());

    $this->query = new \Elastica\Query();
    $this->query->setSize($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    $this->queryBool = new \Elastica\Query\BoolQuery;

    if (isset($request->query))
    {
      $query = new \Elastica\Query\QueryString(arElasticSearchPluginUtil::escapeTerm($request->query));
      $query->setDefaultOperator('AND');
      $query->setFields(array(
        'identifier',
        'referenceCode',
        sprintf('i18n.%s.title', sfContext::getInstance()->user->getCulture())));
      $this->queryBool->addMust($query);
    }
    else
    {
      $query = new \Elastica\Query\Term;
      $query->setTerm('parentId', $this->parent->id);
      $this->queryBool->addMust($query);
    }

    $this->query->setQuery($this->queryBool);

    if ($this->resource instanceof QubitInformationObject)
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);
    }
    else if ($this->resource instanceof QubitTerm)
    {
      // TODO: Add parent_id for terms in ES, add move button
      $resultSet = QubitSearch::getInstance()->index->getType('QubitTerm')->search($this->query);
    }

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();

    $slugs = array();
    foreach ($this->pager->getResults() as $hit)
    {
      $data = $hit->getData();
      $slugs[] = $data['slug'];
    }

    $criteria = new Criteria;
    $criteria->addJoin(QubitObject::ID, QubitSlug::OBJECT_ID);
    $criteria->add(QubitSlug::SLUG, $slugs, Criteria::IN);

    $this->results = QubitObject::get($criteria);
  }
}
