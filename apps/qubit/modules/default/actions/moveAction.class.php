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
        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        $this->form = new sfForm();

        $this->resource = $this->getRoute()->resource;

        // Check that the object exists and that it is not the root
        if (!isset($this->resource) || !isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check authorization
        if (!QubitAcl::check($this->resource, 'update') && !$this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) {
            QubitAcl::forwardUnauthorized();
        }

        // Parent form field
        $this->form->setValidator('parent', new sfValidatorString(['required' => true]));
        $this->form->setWidget('parent', new sfWidgetFormInputHidden());

        // Get parent from GET parameters
        if (isset($request->parent)) {
            $this->form->setDefault('parent', $request->parent);
        } else {
            // Root is default parent
            if ($this->resource instanceof QubitInformationObject) {
                $this->form->setDefault('parent', QubitInformationObject::getById(QubitInformationObject::ROOT_ID)->slug);
            } elseif ($this->resource instanceof QubitTerm) {
                $this->form->setDefault('parent', QubitTerm::getById(QubitTerm::ROOT_ID)->slug);
            }
        }

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                $parent = QubitObject::getBySlug($this->form->parent->getValue());

                $params = [
                    'objectId' => $this->resource->id,
                    'parentId' => $parent->id,
                ];

                QubitJob::runJob('arObjectMoveJob', $params);

                // Notify user move has started
                sfContext::getInstance()->getConfiguration()->loadHelpers(['Url']);

                $jobManageUrl = url_for(['module' => 'jobs', 'action' => 'browse']);
                $jobManageLink = '<a class="alert-link" href="'.$jobManageUrl.'">'.$this->context->i18n->__('job management').'</a>';

                $message = '<strong>'.$this->context->i18n->__('Move initiated.').'</strong> ';
                $message .= $this->context->i18n->__("If job hasn't already completed, check %1% page to determine present status.", ['%1%' => $jobManageLink]);
                $this->getUser()->setFlash('notice', $message);

                if ($request->isXmlHttpRequest()) {
                    return $this->renderText('');
                }
            }
        }

        $this->parent = QubitObject::getBySlug($this->form->parent->getValue());

        $limit = sfConfig::get('app_hits_per_page', 10);
        if (isset($request->limit) && ctype_digit($request->limit)) {
            $limit = $request->limit;
        }

        $page = 1;
        if (isset($request->page) && ctype_digit($request->page)) {
            $page = $request->page;
        }

        // Avoid pagination over ES' max result window config (default: 10000)
        $maxResultWindow = arElasticSearchPluginConfiguration::getMaxResultWindow();

        if ((int) $limit * $page > $maxResultWindow) {
            // Show alert
            $message = $this->context->i18n->__(
                "We've redirected you to the first page of results. To avoid using vast amounts of memory, AtoM limits pagination to %1% records. Please, narrow down your results.",
                ['%1%' => $maxResultWindow]
            );
            $this->getUser()->setFlash('notice', $message);

            // Redirect to first page
            $params = $request->getParameterHolder()->getAll();
            unset($params['page']);
            $this->redirect($params);
        }

        $this->query = new \Elastica\Query();
        $this->query->setSize($limit);
        $this->query->setFrom(($page - 1) * $limit);

        $this->queryBool = new \Elastica\Query\BoolQuery();

        if (isset($request->query) || isset($request->subquery)) {
            $fields = [
                'identifier' => 1,
                'referenceCode' => 1,
                sprintf('i18n.%s.title', sfContext::getInstance()->user->getCulture()) => 1,
            ];
            $this->queryBool->addMust(
                arElasticSearchPluginUtil::generateQueryString(
                    $request->query ?: $request->subquery, $fields
                )
            );
        } else {
            $query = new \Elastica\Query\Term();
            $query->setTerm('parentId', $this->parent->id);
            $this->queryBool->addMust($query);
        }

        $this->query->setQuery($this->queryBool);

        if ($this->resource instanceof QubitInformationObject) {
            $resultSet = QubitSearch::getInstance()->index->getIndex('QubitInformationObject')->search($this->query);
        } elseif ($this->resource instanceof QubitTerm) {
            // TODO: Add parent_id for terms in ES, add move button
            $resultSet = QubitSearch::getInstance()->index->getIndex('QubitTerm')->search($this->query);
        }

        // Page results
        $this->pager = new QubitSearchPager($resultSet);
        $this->pager->setPage($page);
        $this->pager->setMaxPerPage($limit);
        $this->pager->init();

        $slugs = [];
        foreach ($this->pager->getResults() as $hit) {
            $data = $hit->getData();
            $slugs[] = $data['slug'];
        }

        $criteria = new Criteria();
        $criteria->addJoin(QubitObject::ID, QubitSlug::OBJECT_ID);
        $criteria->add(QubitSlug::SLUG, $slugs, Criteria::IN);

        $this->results = QubitObject::get($criteria);
    }
}
