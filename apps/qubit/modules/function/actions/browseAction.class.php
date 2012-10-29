<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package    AtoM
 * @subpackage repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 */
class FunctionBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    if ($this->getUser()->isAuthenticated())
    {
      $this->sortSetting = sfConfig::get('app_sort_browser_user');
    }
    else
    {
      $this->sortSetting = sfConfig::get('app_sort_browser_anonymous');
    }

    $criteria = new Criteria;

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
        if ('alphabetic' == $this->sortSetting)
        {
          $criteria->addAscendingOrderByColumn('authorized_form_of_name');
        }
        else if ('lastUpdated' == $this->sortSetting)
        {
          $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);
        }
    }

    // Do source culture fallback
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitFunction');

    // Page results
    $this->pager = new QubitPager('QubitFunction');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
