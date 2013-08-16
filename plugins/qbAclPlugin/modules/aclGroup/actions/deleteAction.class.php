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

class AclGroupDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->group = QubitAclGroup::getById($request->id);

    // Check that object exists
    if (!isset($this->group))
    {
      $this->forward404();
    }

    // Check permissions
    if ($this->group->isProtected() || !QubitAcl::check($this->group, 'delete'))
    {
      QubitAcl::forwardUnauthorized();
    }

    if ($request->isMethod('delete'))
    {
      $this->group->delete();

      $this->redirect(array('module' => 'aclGroup', 'action' => 'list'));
    }
  }
}
