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

class RepositoryHoldingsComponent extends sfComponent
{
  public function execute($request)
  {
    $page = 1;
    $limit = sfConfig::get('app_hits_per_page', 10);
    $resultSet = RepositoryHoldingsAction::getHoldings($this->resource->id, $page, $limit);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($page);
    $this->pager->setMaxPerPage($limit);
    $this->pager->init();
  }
}
