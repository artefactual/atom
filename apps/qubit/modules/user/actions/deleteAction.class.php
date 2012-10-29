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

class UserDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    if ($this->context->user->user === $this->resource || 0 < count($this->resource->notes))
    {
      QubitAcl::forwardUnauthorized();
    }

    if ($request->isMethod('delete'))
    {
      $this->resource->delete();

      $this->redirect(array('module' => 'user', 'action' => 'list'));
    }
  }
}
