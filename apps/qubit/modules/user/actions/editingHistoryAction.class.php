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

class UserEditingHistoryAction extends sfAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;
        $this->abortInvalidRequests();

        // Criteria to fetch user actions
        $criteria = new Criteria();
        $criteria->add(QubitAuditLog::USER_ID, $this->resource->id);
        $criteria->addDescendingOrderByColumn('created_at');

        // Page results
        $limit = sfConfig::get('app_hits_per_page');
        $page = (isset($request->page) && ctype_digit($request->page)) ? $request->page : 1;

        $pager = new QubitPager('QubitAuditLog');
        $pager->setCriteria($criteria);
        $pager->setPage($page);
        $pager->setMaxPerPage($limit);

        // Return results and paging data
        $data = [
            'results' => $this->summarizeCurrentPage($pager),
            'items' => $pager->getNbResults(),
            'pages' => $pager->getLastPage(),
        ];

        return $this->renderText(json_encode($data));
    }

    private function abortInvalidRequests()
    {
        if (!isset($this->resource)) {
            $this->forward404();
        }

        // Except for administrators, only allow users to see their own profile
        if (!$this->context->user->isAdministrator()) {
            if ($this->resource->id != $this->context->user->getAttribute('user_id')) {
                $this->redirect('admin/secure');
            }
        }
    }

    private function summarizeCurrentPage($pager)
    {
        $culture = $this->context->user->getCulture();
        $dateFormatter = new sfDateFormat($culture);

        // Summarize page results
        $results = [];

        foreach ($pager->getResults() as $modification) {
            $io = QubitInformationObject::getById($modification->objectId);

            $result = [
                'createdAt' => $dateFormatter->format($modification->createdAt, 'f'),
                'title' => $io->getTitle(['cultureFallback' => true]),
                'slug' => $io->slug,
                'actionType' => QubitTerm::getById($modification->actionTypeId)->name,
            ];

            array_push($results, $result);
        }

        return $results;
    }
}
