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

class AclGroupIndexActorAclAction extends sfAction
{
  public function execute($request)
  {
    $this->group = QubitAclGroup::getById($request->id);
    $this->forward404Unless($this->group);

    // Check authorization
    if (!QubitAcl::check($this->group, 'read'))
    {
      $this->redirect('admin/secure');
    }

    // Add roles
    $this->roles = array();
    foreach ($this->group->ancestors->andSelf()->orderBy('lft') as $group)
    {
      // Omit ROOT group
      if (1 < $group->id)
      {
        $this->roles[] = $group->id;
      }
    }

    // Table width
    $this->tableCols = count($this->roles) + 3;

    // Get permissions for this group and parents
    $criteria = new Criteria;
    $criteria->add(QubitAclPermission::GROUP_ID, $this->roles, Criteria::IN);

    // Add actor criteria
    $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
    $c1 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, 'QubitActor');
    $c2 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
    $c1->addOr($c2);
    $criteria->add($c1);

    // Build ACL
    $this->acl = array();
    if (0 < count($permissions = QubitAclPermission::get($criteria)))
    {
      foreach ($permissions as $permission)
      {
        // In this context permissions for all objects (null) and root actor
        // object are equivalent
        $objectId = (QubitActor::ROOT_ID != $permission->objectId) ? $permission->objectId : null;

        $this->acl[$objectId][$permission->action][$permission->groupId] = $permission;
      }
    }
  }
}
