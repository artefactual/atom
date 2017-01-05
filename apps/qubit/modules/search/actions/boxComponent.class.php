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

class SearchBoxComponent extends sfComponent
{
  public function execute($request)
  {
    // if the institutional scoping setting is on, search is always global.
    if (sfConfig::get('app_enable_institutional_scoping'))
    {
      $this->repository = null;
      $this->altRepository = null;
      return;
    }

    // Check if the user is browsing a repo
    $route = $request->getAttribute('sf_route');
    if (isset($route->resource))
    {
      if ($route->resource instanceof QubitRepository)
      {
        $this->repository = $route->resource;
      }
      elseif ($route->resource instanceof QubitInformationObject)
      {
        $this->repository = $route->resource->getRepository(array('inherit' => true));
      }
    }

    if (null !== $realmId = $this->context->user->getAttribute('search-realm'))
    {
      if (isset($this->repository) && $realmId == $this->repository->id)
      {
        return;
      }

      $this->altRepository = QubitRepository::getById($realmId);
    }
  }
}
