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

class AclGroupEditInformationObjectAclAction extends AclGroupEditDefaultAclAction
{
  public static $NAMES = array(
    'informationObject',
    'repository'
  );

  public function execute($request)
  {
    parent::execute($request);

    // Build separate list of permissions by repository and by object
    $this->repositories = array();
    $this->informationObjects = array();
    $this->root = array();

    if (null != $this->resource->id)
    {
      // Get info object permissions for this resource
      $criteria = new Criteria;
      $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
      $criteria->add(QubitAclPermission::GROUP_ID, $this->resource->id);
      $c1 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
      $c2 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, 'QubitInformationObject');
      $c1->addOr($c2);
      $criteria->add($c1);

      $criteria->addAscendingOrderByColumn(QubitAclPermission::CONSTANTS);
      $criteria->addAscendingOrderByColumn(QubitAclPermission::OBJECT_ID);

      if (0 < count($permissions = QubitAclPermission::get($criteria)))
      {
        foreach ($permissions as $p)
        {
          if (null != ($repository = $p->getConstants(array('name' => 'repository'))))
          {
            $this->repositories[$repository][$p->action] = $p;
          }
          else if (null != $p->objectId && QubitInformationObject::ROOT_ID != $p->objectId)
          {
            $this->informationObjects[$p->objectId][$p->action] = $p;
          }
          else
          {
            $this->root[$p->action] = $p;
          }
        }
      }
    }

    // List of actions without translate
    $this->basicActions = QubitInformationObjectAcl::$ACTIONS;
    unset($this->basicActions['translate']);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();
        $this->redirect(array($this->resource, 'module' => 'aclGroup', 'action' => 'indexInformationObjectAcl'));
      }
    }
  }
}
