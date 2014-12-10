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

class InformationObjectFindingAidAction extends sfAction
{
  // export an object w/relations as an XML document with selected schema
  public function execute($request)
  {
    if (isset($this->getRoute()->resource) && sfContext::getInstance()->user->isAuthenticated())
    {
      $id = $this->getRoute()->resource->id;

      $params = array('objectId' => $id);
      QubitJob::runJob('arGenerateFindingAidJob', $params);
    }

    $this->redirect($request->getHttpHeader('referer'));
  }
}
