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

/**
 * @package    qubit
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class InformationObjectListAction extends sfAction
{
  /**
   * Display a paginated hitlist of information objects (top-level only)
   *
   * @param sfRequest $request
   */
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->resource = QubitInformationObject::getById(QubitInformationObject::ROOT_ID);
    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;
    }

    $query = QubitSearch::getInstance()->addTerm($this->resource->id, 'parentId');

    if (isset($request->query))
    {
      $query = $request->query;
    }

    $query = QubitAcl::searchFilterByRepository($query, 'read');
    $query = QubitAcl::searchFilterDrafts($query);

    $this->pager = new QubitArrayPager;
    $this->pager->hits = QubitSearch::getInstance()->getEngine()->getIndex()->find($query);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $ids = array();
    foreach ($this->pager->getResults() as $hit)
    {
      $ids[] = $hit->getDocument()->id;
    }

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::ID, $ids, Criteria::IN);

    $this->informationObjects = QubitInformationObject::get($criteria);
  }
}
