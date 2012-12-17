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

class AclGroupIndexTermAclAction extends sfAction
{
  public function execute($request)
  {
    $this->group = QubitAclGroup::getById($this->request->id);
    $this->forward404Unless($this->group);

    $this->groups = array();
    foreach ($this->group->ancestors->andSelf()->orderBy('lft') as $group)
    {
      if (QubitAclGroup::ROOT_ID < $group->id)
      {
        $this->groups[] = $group->id;
      }
    }

    // Table width
    $this->tableCols = count($this->groups) + 3;

    // Get access control permissions
    $criteria = new Criteria;
    if (1 == count($this->groups))
    {
      $criteria->add(QubitAclPermission::GROUP_ID, $this->groups[0]);
    }
    else
    {
      $criteria->add(QubitAclPermission::GROUP_ID, $this->groups, Criteria::IN);
    }

    // Add term criteria
    $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
    $c1 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, 'QubitTerm');
    $c2 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
    $c3 = $criteria->getNewCriterion(QubitAclPermission::ACTION, 'createTerm');
    $c1->addOr($c2);
    $c1->addOr($c3);
    $criteria->add($c1);

    // Sort
    $criteria->addAscendingOrderByColumn(QubitAclPermission::CONSTANTS);
    $criteria->addAscendingOrderByColumn(QubitAclPermission::OBJECT_ID);
    $criteria->addAscendingOrderByColumn(QubitAclPermission::GROUP_ID);

    // Build ACL
    $this->acl = array();
    if (0 < count($permissions = QubitAclPermission::get($criteria)))
    {
      foreach ($permissions as $permission)
      {
        if ('createTerm' != $permission->action)
        {
          $taxonomy = $permission->getConstants(array('name' => 'taxonomy'));
          $action = $permission->action;
        }
        else
        {
          // In this context permissions for all objects (null) and root object
          // are equivalent
          $taxonomy = (QubitTaxonomy::ROOT_ID != $permission->objectId) ? $permission->objectId : null;
          $action = 'create';
        }

        $this->acl[$taxonomy][$action][$permission->groupId] = $permission;
      }
    }
  }
}
