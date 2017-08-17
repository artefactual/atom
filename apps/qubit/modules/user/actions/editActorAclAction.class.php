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

class UserEditActorAclAction extends DefaultEditAction
{
  public static
    $NAMES = array();

  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;
    }
    else
    {
      $this->forward404();
    }

    // Always include root actor permissions
    $this->actors = array(QubitActor::ROOT_ID => null);

    // Get actor permissions for this group
    $criteria = new Criteria;
    $criteria->addJoin(QubitAclPermission::OBJECT_ID, QubitObject::ID, Criteria::LEFT_JOIN);
    $criteria->add(QubitAclPermission::USER_ID, $this->resource->id);
    $c1 = $criteria->getNewCriterion(QubitAclPermission::OBJECT_ID, null, Criteria::ISNULL);
    $c2 = $criteria->getNewCriterion(QubitObject::CLASS_NAME, 'QubitActor');
    $c1->addOr($c2);
    $criteria->add($c1);

    if (null !== $permissions = QubitAclPermission::get($criteria))
    {
      foreach ($permissions as $item)
      {
        // In this context permissions for all objects (null) and root actor object are equivalent
        if ($item->objectId === null)
        {
          $this->actors[QubitActor::ROOT_ID][$item->action] = $item;
        }
        else
        {
          $this->actors[$item->objectId][$item->action] = $item;
        }
      }
    }

    // List of actions without translate
    $this->basicActions = QubitAcl::$ACTIONS;
    unset($this->basicActions['translate']);
  }

  protected function processForm()
  {
    foreach ($this->request->acl as $key => $value)
    {
      // If key has an underscore, then we are creating a new permission
      if (1 == preg_match('/([\w]+)_(.*)/', $key, $matches))
      {
        list ($action, $uri) = array_slice($matches, 1, 2);
        $params = $this->context->routing->parse(Qubit::pathInfo($uri));
        $resource = $params['_sf_route']->resource;

        if (QubitAcl::INHERIT != $value && isset(QubitAcl::$ACTIONS[$action]))
        {
          $aclPermission = new QubitAclPermission;
          $aclPermission->action = $action;
          $aclPermission->grantDeny = (QubitAcl::GRANT == $value) ? 1 : 0;
          $aclPermission->object = $resource;

          $this->resource->aclPermissions[] = $aclPermission;
        }
      }

      // Otherwise, update an existing permission
      else if (null !== $aclPermission = QubitAclPermission::getById($key))
      {
        if ($value == QubitAcl::INHERIT)
        {
          $aclPermission->delete();
        }
        else
        {
          $aclPermission->grantDeny = (QubitAcl::GRANT == $value) ? 1 : 0;
          $this->resource->aclPermissions[] = $aclPermission;
        }
      }
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'user', 'action' => 'indexActorAcl'));
      }
    }
  }
}
