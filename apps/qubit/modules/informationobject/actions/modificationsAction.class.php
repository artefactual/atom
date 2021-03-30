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

class InformationObjectModificationsAction extends sfAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;
        $this->abortInvalidRequests();

        // Criteria to fetch user actions
        $criteria = new Criteria();
        $criteria->add(QubitAuditLog::OBJECT_ID, $this->resource->id);
        $criteria->addDescendingOrderByColumn(QubitAuditLog::CREATED_AT);

        // Page results
        $limit = sfConfig::get('app_hits_per_page');
        $page = (isset($request->page) && ctype_digit($request->page)) ? $request->page : 1;

        $this->pager = new QubitPager('QubitAuditLog');
        $this->pager->setCriteria($criteria);
        $this->pager->setPage($page);
        $this->pager->setMaxPerPage($limit);

        $this->modifications = $this->pager->getResults();
    }

    private function abortInvalidRequests()
    {
        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'read') || !sfConfig::get('app_audit_log_enabled', false)) {
            QubitAcl::forwardToSecureAction();
        }
    }
}
