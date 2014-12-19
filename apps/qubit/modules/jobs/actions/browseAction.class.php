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
 * @subpackage jobs
 * @author     Mike G <mikeg@artefactual.com>
 */
class JobsBrowseAction extends DefaultBrowseAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->user = $this->context->user;

    $this->autoRefresh = false;
    if (isset($request->autoRefresh))
    {
      $this->autoRefresh = $request->autoRefresh;
    }

    $this->refreshInterval = 10000;
    $this->filter = $request->filter;

    if (!$this->user || !$this->user->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if (!isset($this->filter))
    {
      $this->filter = 'all';
    }

    $criteria = new Criteria;

    // Filter out the history of other users' jobs if not an administrator.
    if (!$this->user->isAdministrator())
    {
      $criteria->add(QubitJob::USER_ID, $this->user->getUserID());
    }

    if ($this->filter === 'active')
    {
      $criteria->add(QubitJob::STATUS_ID, QubitTerm::JOB_STATUS_IN_PROGRESS_ID);
    }

    $criteria->addJoin(QubitJob::ID, QubitObject::ID);
    $criteria->addDescendingOrderByColumn('created_at');

    // Page results
    $this->pager = new QubitPager('QubitJob');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
