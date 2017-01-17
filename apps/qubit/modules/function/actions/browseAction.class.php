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

    if (sfConfig::get('app_enable_institutional_scoping'))
    {
      // remove search-realm
      $this->context->user->removeAttribute('search-realm');
    }

    if (!isset($request->sort))
    {
      if ($this->getUser()->isAuthenticated())
      {
        $request->sort = sfConfig::get('app_sort_browser_user');
      }
      else
      {
        $request->sort = sfConfig::get('app_sort_browser_anonymous');
      }
    }

    $criteria = new Criteria;

    if (isset($request->subquery))
    {
      $criteria->addJoin(QubitFunction::ID, QubitFunctionI18n::ID);
      $criteria->add(QubitFunctionI18n::CULTURE, $this->context->user->getCulture());
      $criteria->add(QubitFunctionI18n::AUTHORIZED_FORM_OF_NAME, "%$request->subquery%", Criteria::LIKE);
    }

    switch ($request->sort)
    {
      case 'identifier':
        $criteria->addAscendingOrderByColumn(QubitFunction::DESCRIPTION_IDENTIFIER);
        //And then back to authorised form of name
      case 'alphabetic':
        $criteria->addAscendingOrderByColumn('authorized_form_of_name');

        break;

      case 'lastUpdated':
      default:
        $criteria->addDescendingOrderByColumn(QubitObject::UPDATED_AT);

        break;
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
