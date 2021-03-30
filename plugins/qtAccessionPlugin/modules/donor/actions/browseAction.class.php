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

class DonorBrowseAction extends sfAction
{
    public function execute($request)
    {
        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        if (!isset($request->sort)) {
            if ($this->getUser()->isAuthenticated()) {
                $request->sort = sfConfig::get('app_sort_browser_user');
            } else {
                $request->sort = sfConfig::get('app_sort_browser_anonymous');
            }
        }

        // Default sort direction
        $sortDir = 'asc';
        if ('lastUpdated' == $request->sort) {
            $sortDir = 'desc';
        }

        // Set default sort direction in request if not present or not valid
        if (!isset($request->sortDir) || !in_array($request->sortDir, ['asc', 'desc'])) {
            $request->sortDir = $sortDir;
        }

        // Determine sorting function based on sort direction
        $sortFunction = 'addAscendingOrderByColumn';
        if ('desc' == $request->sortDir) {
            $sortFunction = 'addDescendingOrderByColumn';
        }

        $criteria = new Criteria();

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

        if (isset($request->subquery)) {
            $criteria->addJoin(QubitDonor::ID, QubitActorI18n::ID);
            $criteria->add(QubitActorI18n::CULTURE, $this->context->user->getCulture());
            $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, "%{$request->subquery}%", Criteria::LIKE);
        }

        switch ($request->sort) {
            case 'identifier':
                $criteria->{$sortFunction}(QubitActor::DESCRIPTION_IDENTIFIER);

                // no break
            case 'alphabetic':
                $criteria->{$sortFunction}('authorized_form_of_name');

                break;

            case 'lastUpdated':
            default:
                $criteria->{$sortFunction}(QubitObject::UPDATED_AT);

                break;
        }

        // Page results
        $this->pager = new QubitPager('QubitDonor');
        $this->pager->setCriteria($criteria);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->setPage($request->page);
    }
}
