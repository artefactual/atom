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
    if (!$this->context->user->hasCredential(array('contributor', 'editor', 'administrator'), false))
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;

    // Do source culture fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

    switch ($request->sort)
    {
      case 'nameDown':
        $criteria->addDescendingOrderByColumn('authorized_form_of_name');

        break;

      case 'nameUp':
        $criteria->addAscendingOrderByColumn('authorized_form_of_name');

        break;

      case 'updatedDown':
        $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);

        break;

      case 'updatedUp':
        $criteria->addAscendingOrderByColumn(QubitObject::UPDATED_AT);

        break;

      default:
        if (!$this->getUser()->isAuthenticated())
        {
          $criteria->addAscendingOrderByColumn('authorized_form_of_name');
        }
        else
        {
          $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);
        }
    }

    // Page results
    $this->pager = new QubitPager('QubitDonor');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
