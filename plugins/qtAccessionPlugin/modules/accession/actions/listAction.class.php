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

class AccessionListAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->context->user->hasCredential(array('contributor', 'editor', 'administrator'), false))
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;
    $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);

    if (isset($request->subquery))
    {
      $criteria->addJoin(QubitAccession::ID, QubitAccessionI18n::ID);
      $criteria->add(QubitAccessionI18n::CULTURE, $this->context->user->getCulture());

      // Query in all i18n columns
      $c1 = $criteria->getNewCriterion(QubitAccessionI18n::APPRAISAL, "%$request->subquery%", Criteria::LIKE);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::ARCHIVAL_HISTORY, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::LOCATION_INFORMATION, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::PHYSICAL_CHARACTERISTICS, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::PROCESSING_NOTES, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::RECEIVED_EXTENT_UNITS, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::SCOPE_AND_CONTENT, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::SOURCE_OF_ACQUISITION, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);
      $c2 = $criteria->getNewCriterion(QubitAccessionI18n::TITLE, "%$request->subquery%", Criteria::LIKE);
      $c1->addOr($c2);

      $criteria->add($c1);
    }
    else
    {
      $this->redirect(array('module' => 'accession', 'action' => 'browse'));
    }

    // Page results
    $this->pager = new QubitPager('QubitAccession');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);

    $this->accessions = $this->pager->getResults();
  }
}
