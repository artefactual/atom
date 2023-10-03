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

class aclGroupListAction extends sfAction
{
    public function execute($request)
    {
        $title = $this->context->i18n->__('List groups');
        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        $criteria = new Criteria();
        $criteria->add(QubitAclGroup::ID, QubitAclGroup::ROOT_ID, Criteria::NOT_EQUAL);
        $criteria->addAscendingOrderByColumn('name');

        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitAclGroup');

        // Page results
        $this->pager = new QubitPager('QubitAclGroup');
        $this->pager->setCriteria($criteria);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->setPage($request->page);

        $this->groups = $this->pager->getResults();
    }
}
