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

class RightsHolderAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    // Check user authorization
    if (!$this->getUser()->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
    $criteria->add(QubitActorI18n::CULTURE, $this->context->user->getCulture());
    $criteria->add(QubitActor::CLASS_NAME, 'QubitRightsHolder');

    if (isset($request->query))
    {
      if (sfConfig::get('app_markdown_enabled', false))
      {
        $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, "%$request->query%", Criteria::LIKE);
      }
      else
      {
        $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, "$request->query%", Criteria::LIKE);
      }
    }

    $this->pager = new QubitPager('QubitActor');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->rightsHolders = $this->pager->getResults();

    $this->setTemplate('list');
  }
}
