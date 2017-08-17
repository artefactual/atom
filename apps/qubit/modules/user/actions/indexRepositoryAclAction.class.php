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

class UserIndexRepositoryAclAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    if (!isset($this->resource))
    {
      $this->forward404();
    }

    // Except for administrators, only allow users to see their own profile
    if (!$this->context->user->isAdministrator())
    {
      if ($this->resource->id != $this->context->user->getAttribute('user_id'))
      {
        $this->redirect('admin/secure');
      }
    }

    // Get user's groups
    $this->userGroups = array();
    if (0 < count($aclUserGroups = $this->resource->aclUserGroups))
    {
      foreach ($aclUserGroups as $item)
      {
        $this->userGroups[] = $item->groupId;
      }
    }
    else
    {
      // User is *always* part of authenticated group
      $this->userGroups = array(QubitAclGroup::AUTHENTICATED_ID);
    }

    // Table width
    $this->tableCols = count($this->userGroups) + 3;

    // Get access control permissions for repositories
    $criteria = new Criteria;
    $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
    $c1 = $criteria->getNewCriterion(QubitAclPermission::USER_ID, $this->resource->id);
    if (1 == count($this->userGroups))
    {
      $c2 = $criteria->getNewCriterion(QubitAclPermission::GROUP_ID, $this->userGroups[0]);
    }
    else
    {
      $c2 = $criteria->getNewCriterion(QubitAclPermission::GROUP_ID, $this->userGroups, Criteria::IN);
    }
    $c1->addOr($c2);
    $c3 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, 'QubitRepository');
    $c4 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
    $c3->addOr($c4);
    $c1->addAnd($c3);

    $criteria->add($c1);
    $criteria->addAscendingOrderByColumn(QubitAclPermission::OBJECT_ID);
    $criteria->addAscendingOrderByColumn(QubitAclPermission::USER_ID);
    $criteria->addAscendingOrderByColumn(QubitAclPermission::GROUP_ID);

    // Add user as final "group"
    $this->userGroups[] = $this->resource->username;

    // Build ACL
    $this->acl = array();
    if (0 < count($permissions = QubitAclPermission::get($criteria)))
    {
      foreach ($permissions as $item)
      {
        // In this context permissions for all objects (null) and root repository
        // object are equivalent
        $objectId = (QubitRepository::ROOT_ID != $item->objectId) ? $item->objectId : null;

        // Use username as "group" for permissions specific to user
        $groupKey = (null !== $item->groupId) ? $item->groupId : $this->resource->username;

        $this->acl[$objectId][$item->action][$groupKey] = $item;
      }
    }
  }
}
