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

class ActorAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
    $criteria->add(QubitActorI18n::CULTURE, $this->context->user->getCulture());
    $criteria->addJoin(QubitActor::ID, QubitObject::ID);

    // Filter out non-authority Actors
    $filteredObjects = array('QubitUser', 'QubitDonor', 'QubitRightsHolder');
    $criteria->add(QubitObject::CLASS_NAME, $filteredObjects, Criteria::NOT_IN);

    // Sort alphabetically by name
    $criteria->addAscendingOrderByColumn('authorized_form_of_name');

    if (isset($request->showOnlyActors) && 'true' == $request->showOnlyActors)
    {
      $criteria->add(QubitObject::CLASS_NAME, 'QubitActor');
    }

    if (isset($request->query))
    {
      $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, "$request->query%", Criteria::LIKE);
    }

    // Exclude the calling actor from the list
    $params = $this->context->routing->parse(Qubit::pathInfo($request->getReferer()));
    $resource = $params['_sf_route']->resource;
    if (isset($resource->id))
    {
      $criteria->add(QubitActor::ID, $resource->id, Criteria::NOT_EQUAL);
    }

    $this->pager = new QubitPager('QubitActor');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->actors = $this->pager->getResults();
  }
}
