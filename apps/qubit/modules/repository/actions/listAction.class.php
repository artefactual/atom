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
 */
class RepositoryListAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (isset($request->subquery))
    {
      try
      {
        // Parse query string
        $query = QubitSearch::getInstance()->parse($request->subquery);
      }
      catch (Exception $e)
      {
        $this->error = $e->getMessage();

        return;
      }
    }
    else
    {
      $this->redirect(array('module' => 'repository', 'action' => 'browse'));
    }

    $query->addSubquery(QubitSearch::getInstance()->addTerm('QubitRepository', 'className'), true);
    $query = QubitAcl::searchFilterByResource($query, QubitRepository::getById(QubitRepository::ROOT_ID));

    $this->pager = new QubitArrayPager;

    try
    {
      $this->pager->hits = QubitSearch::getInstance()->getEngine()->getIndex()->find($query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return;
    }

    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $ids = array();
    foreach ($this->pager->getResults() as $hit)
    {
      $ids[] = $hit->getDocument()->id;
    }

    $criteria = new Criteria;
    $criteria->add(QubitRepository::ID, $ids, Criteria::IN);

    $this->repositories = QubitRepository::get($criteria);
  }
}
