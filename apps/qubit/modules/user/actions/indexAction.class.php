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

class UserIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    if (!isset($this->resource))
    {
      $this->forward404();
    }

    foreach (array('restApiKey', 'oaiApiKey') as $key)
    {
      // Get API key value, if any
      $apiKeyProperty = QubitProperty::getOneByObjectIdAndName($this->resource->id, sfInflector::camelize($key));

      if (null != $apiKeyProperty)
      {
        $this->$key = $apiKeyProperty->value;
      }
    }

    // Except for administrators, only allow users to see their own profile
    if (!$this->context->user->isAdministrator())
    {
      if ($this->resource->id != $this->context->user->getAttribute('user_id'))
      {
        $this->redirect('admin/secure');
      }
    }
  }
}
