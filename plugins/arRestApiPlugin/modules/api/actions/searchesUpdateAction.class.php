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

class ApiSearchesUpdateAction extends QubitApiAction
{
  protected function put($request, $payload)
  {
    if (!$this->context->user->isAuthenticated())
    {
      throw new QubitApiNotAuthorizedException();
    }

    if (null === $this->search = QubitSavedQuery::getById($request->id))
    {
      throw new QubitApi404Exception('Search not found');
    }

    // Check if user is the creator of the query or is an admin
    $allowed = true;
    if ($this->search->userId !== $this->context->user->getUserID())
    {
      $allowed = false;
      foreach ($this->context->user->getAclGroups() as $group)
      {
        if ($group->id == QubitAclGroup::ADMINISTRATOR_ID || $group->id == QubitAclGroup::ADMIN_ID)
        {
          $allowed = true;

          break;
        }
      }
    }

    if (!$allowed)
    {
      throw new QubitApiNotAuthorizedException();
    }

    foreach ($payload as $field => $value)
    {
      $this->processField($field, $value);
    }

    $this->search->save();

    $this->response->setStatusCode(201);

    return array(
      'id' => (int)$this->search->id);
  }

  protected function processField($field, $value)
  {
    switch ($field)
    {
      case 'name':
      case 'description':
        $this->search->$field = $value;

        break;

      case 'criteria':
        $this->search->params = serialize($value);

        break;

      case 'type':
        $this->search->scope = $value;

        break;
    }
  }
}
