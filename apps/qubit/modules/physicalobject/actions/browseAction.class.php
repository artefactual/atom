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

class PhysicalObjectBrowseAction extends sfAction
{
    public function execute($request)
    {
        $title = $this->context->i18n->__('Physical storage');
        $this->response->setTitle("{$title} - {$this->response->getTitle()}");

        if (!isset($request->limit)) {
            $request->limit = sfConfig::get('app_hits_per_page');
        }

        if (sfConfig::get('app_enable_institutional_scoping')) {
            // remove search-realm
            $this->context->user->removeAttribute('search-realm');
        }

        $criteria = new Criteria();

        // Do source culture fallback
        $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitPhysicalObject');

        if (isset($request->subquery)) {
            // Get physical object data for culture
            $criteria->addJoin(QubitPhysicalObject::ID, QubitPhysicalObjectI18n::ID);
            $criteria->add(QubitPhysicalObjectI18n::CULTURE, $this->context->user->getCulture());

            // Get physical object's type term data for culture
            $criteria->addJoin(QubitPhysicalObject::TYPE_ID, QubitTerm::ID);
            $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
            $criteria->add(QubitTermI18n::CULTURE, $this->context->user->getCulture());

            // Match search query to either physical object name, location, or type
            $c1 = $criteria->getNewCriterion(QubitPhysicalObjectI18n::NAME, "%{$request->subquery}%", Criteria::LIKE);
            $c2 = $criteria->getNewCriterion(QubitPhysicalObjectI18n::LOCATION, "%{$request->subquery}%", Criteria::LIKE);
            $c1->addOr($c2);
            $c3 = $criteria->getNewCriterion(QubitTermI18n::NAME, "%{$request->subquery}%", Criteria::LIKE);
            $c1->addOr($c3);

            $criteria->add($c1);
        }

        switch ($request->sort) {
            case 'nameDown':
                $criteria->addDescendingOrderByColumn('name');

                break;

            case 'locationDown':
                $criteria->addDescendingOrderByColumn('location');

                break;

            case 'locationUp':
                $criteria->addAscendingOrderByColumn('location');

                break;

            case 'nameUp':
            default:
                $request->sort = 'nameUp';
                $criteria->addAscendingOrderByColumn('name');
        }

        // Page results
        $this->pager = new QubitPager('QubitPhysicalObject');
        $this->pager->setCriteria($criteria);
        $this->pager->setMaxPerPage($request->limit);
        $this->pager->setPage($request->page);
    }
}
