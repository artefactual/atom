<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Show paginated list of actors.
 *
 * @package    qubit
 * @subpackage actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     David Juhasz <david@artefactual.com>
 * @author     Wu Liu <wu.liu@usask.ca>
 * @version    svn:$Id: browseAction.class.php 11008 2012-03-01 19:07:08Z sevein $
 */
class ActorBrowseAction extends sfAction
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
    $criteria->add(QubitActor::PARENT_ID, QubitActor::ROOT_ID);

    // Add criteria to exclude actors that are users or repository objects
    $criteria = QubitActor::addGetOnlyActorsCriteria($criteria);

    $fallbackTable = 'QubitActor';

    switch ($request->sort)
    {
      case 'nameDown':
        $criteria->addDescendingOrderByColumn('authorized_form_of_name');

        break;

      case 'nameUp':
        $criteria->addAscendingOrderByColumn('authorized_form_of_name');

      break;

      case 'typeDown':
        $fallbackTable = 'QubitTerm';
        $criteria->addJoin(QubitActor::ENTITY_TYPE_ID, QubitTerm::ID, Criteria::LEFT_JOIN);
        $criteria->addDescendingOrderByColumn('name');

        break;

      case 'typeUp':
        $fallbackTable = 'QubitTerm';
        $criteria->addJoin(QubitActor::ENTITY_TYPE_ID, QubitTerm::ID, Criteria::LEFT_JOIN);
        $criteria->addAscendingOrderByColumn('name');

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
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, $fallbackTable);

    // Page results
    $this->pager = new QubitPager('QubitActor');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
