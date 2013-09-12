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
class InformationObjectAutocompleteAction extends sfAction
{
  /**
   * Return all information objects (not just top-level) for ajax request
   */
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;

    // Exclude root
    $criteria->add(QubitInformationObject::ID, QubitInformationObject::ROOT_ID, Criteria::NOT_EQUAL);

    $criteria->addJoin(QubitInformationObject::ID, QubitInformationObjectI18n::ID);
    $criteria->add(QubitInformationObjectI18n::CULTURE, $this->context->user->getCulture());

    // Limit results by parent
    if (isset($request->parent))
    {
      $criteria->add(QubitInformationObject::PARENT_ID, $request->parent);
    }

    // Filter drafts
    if (isset($request->filterDrafts) && $request->filterDrafts)
    {
      $criteria = QubitAcl::addFilterDraftsCriteria($criteria);
    }

    // Search for matching title or identifier
    if (isset($request->query))
    {
      $criteria->add($criteria->getNewCriterion(QubitInformationObject::IDENTIFIER, "$request->query%", Criteria::LIKE)
        ->addOr($criteria->getNewCriterion(QubitInformationObjectI18n::TITLE, "$request->query%", Criteria::LIKE)));
    }

    $criteria->addAscendingOrderByColumn(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID);
    $criteria->addAscendingOrderByColumn(QubitInformationObject::IDENTIFIER);
    $criteria->addAscendingOrderByColumn(QubitInformationObjectI18n::TITLE);

    $this->pager = new QubitPager('QubitInformationObject');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage(1);

    $this->informationObjects = $this->pager->getResults();
  }
}
