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

class ApiUsersAuthenticateAction extends QubitApiAction
{
  protected function get($request)
  {
    if (!$this->context->user->isAuthenticated())
    {
      throw new QubitApiNotAuthorizedException();
    }

    return $this->currentUserData();
  }

  protected function post($request, $payload)
  {
    $results = array();
    $error = null;

    if (empty($payload->password))
    {
      throw new QubitApiNotAuthorizedException();
    }

    if (!$this->context->user->authenticate($payload->username, $payload->password))
    {
      throw new QubitApiNotAuthorizedException();
    }

    return $this->currentUserData();
  }

  protected function delete($request)
  {
    $this->context->user->signOut();
  }

  protected function currentUserData()
  {
    $groups = array();

    foreach ($this->context->user->user->getAclGroups() as $group)
    {
      $groupNames = array(
        QubitAclGroup::ROOT_ID          => 'root',
        QubitAclGroup::ANONYMOUS_ID     => 'anonymous',
        QubitAclGroup::AUTHENTICATED_ID => 'authenticated',
        QubitAclGroup::ADMINISTRATOR_ID => 'administrator',
        QubitAclGroup::ADMIN_ID         => 'administrator',
        QubitAclGroup::EDITOR_ID        => 'editor',
        QubitAclGroup::CONTRIBUTOR_ID   => 'contributor',
        QubitAclGroup::TRANSLATOR_ID    => 'translator'
      );

      array_push($groups, $groupNames[$group->id]);
    }

    return array(
      'username' => $this->context->user->user->username,
      'email'    => $this->context->user->user->email,
      'groups'   => $groups
    );
  }
}
