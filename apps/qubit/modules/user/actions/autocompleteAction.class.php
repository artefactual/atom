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
 * Return list of users for autocomplete (XHR) response.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class UserAutocompleteAction extends sfAction
{
    public function execute($request)
    {
        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page', 10);
        }

        $criteria = new Criteria();

        if (isset($request->query)) {
            if (sfConfig::get('app_markdown_enabled', true)) {
                $criteria->add(QubitUser::USERNAME, "%{$request->query}%", Criteria::LIKE);
            } else {
                $criteria->add(QubitUser::USERNAME, "{$request->query}%", Criteria::LIKE);
            }
        }

        // Page results
        $this->pager = new QubitPager('QubitUser');
        $this->pager->setCriteria($criteria);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->setPage(1);

        $this->users = $this->pager->getResults();
        $this->setTemplate('list');
    }
}
