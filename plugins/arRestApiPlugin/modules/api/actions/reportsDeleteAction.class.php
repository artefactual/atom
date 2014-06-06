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

class ApiReportsDeleteAction extends QubitApiAction
{
  protected function delete($request)
  {
    if (null === $report = QubitSavedQuery::getById($this->request->id))
    {
      throw new QubitApi404Exception('Report not found');
    }

    // Check if user is the creator of the query or is an admin
    $allowed = true;
    if ($report->userId !== $this->context->user->getUserID())
    {
      $allowed = false;
      foreach ($this->context->user->user->getAclGroups() as $group)
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

    $report->delete();
  }
}
