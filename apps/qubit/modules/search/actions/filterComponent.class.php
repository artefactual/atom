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

class SearchFilterComponent extends sfComponent
{
  public function execute($request)
  {
    // Display nothing unless any object have been provided
    if (!isset($this->object))
    {
      return sfView::NONE;
    }

    // Remove selected parameter from the current GET parameters
    $this->params = $request->getGetParameters();
    unset($this->params[$this->param]);

    // Default module and action to the current module/action
    $this->module = isset($this->module) ? $this->module : $this->context->getModuleName();
    $this->action = isset($this->action) ? $this->action : $this->context->getActionName();
  }
}
