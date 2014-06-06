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

class ApiReportsCreateAction extends QubitApiAction
{
  protected function post($request, $payload)
  {
    if (!$this->context->user->isAuthenticated())
    {
      throw new QubitApiNotAuthorizedException();
    }

    $this->search = new QubitSavedQuery;
    $this->search->userId = $this->context->user->getUserID();
    $this->search->typeId = sfConfig::get('app_drmc_term_report_id');

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

      case 'range':
        $this->search->params = serialize($value);

        break;

      case 'type':
        $this->search->scope = $value;

        break;
    }
  }
}
