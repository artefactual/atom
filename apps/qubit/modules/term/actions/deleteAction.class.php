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

class TermDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Don't delete protected terms
    if ($this->resource->isProtected())
    {
      $this->forward('admin', 'termPermission');
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'delete'))
    {
      QubitAcl::forwardUnauthorized();
    }

    if ($request->isMethod('delete'))
    {
      foreach ($this->resource->descendants->andSelf()->orderBy('rgt') as $item)
      {
        if (QubitAcl::check($item, 'delete'))
        {
          $item->delete();
        }
      }

      if (isset($this->resource->taxonomy))
      {
        $this->redirect(array($this->resource->taxonomy, 'module' => 'taxonomy'));
      }

      $this->redirect(array('module' => 'taxonomy', 'action' => 'list'));
    }
  }
}
