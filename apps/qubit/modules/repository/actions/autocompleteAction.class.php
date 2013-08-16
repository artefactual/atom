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

class RepositoryAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitActor::ID, QubitActorI18n::ID);
    $criteria->add(QubitActor::PARENT_ID, QubitRepository::ROOT_ID);
    $criteria->add(QubitActorI18n::AUTHORIZED_FORM_OF_NAME, "$request->query%", Criteria::LIKE);
    $criteria->addAscendingOrderByColumn('authorized_form_of_name');
    $criteria->setDistinct();
    $criteria->setLimit(sfConfig::get('app_hits_per_page', 10));

    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitActor');

    // Filter "denied" repositories if list for repository autocomplete on
    // information object form
    if (isset($request->aclAction))
    {
      $repositoryList = array();
      $repositoryAccess = QubitAcl::getRepositoryAccess($request->aclAction);

      // If all repositories are denied, no response
      if (1 == count($repositoryAccess) && QubitAcl::DENY == $repositoryAccess[0]['access'])
      {
        return sfView::NONE;
      }
      else
      {
        while ($repo = array_shift($repositoryAccess))
        {
          if ('*' != $repo['id'])
          {
            $repositoryList[] = $repo['id'];
          }
          else
          {
            if (QubitAcl::DENY == $repo['access'])
            {
              // Require repositories to be specifically allowed (all others
              // prohibited)
              $criteria->add(QubitRepository::ID, $repositoryList + array('null'), Criteria::IN);
            }
            else
            {
              // Prohibit specified repositories (all others allowed)
              $criteria->add(QubitRepository::ID, $repositoryList, Criteria::NOT_IN);
            }
          }
        }
      }
    }

    $this->repositories = QubitRepository::get($criteria);
  }
}
