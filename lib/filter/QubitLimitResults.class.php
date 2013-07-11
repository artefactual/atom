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

class QubitLimitResults extends sfFilter
{
  public function execute($filterChain)
  {
    $request = $this->getContext()->getRequest();
    $hitsPerPage = sfConfig::get('app_hits_per_page');

     // Set request limit to app_hits_per_page if bigger
    if (isset($request->limit) && (true !== ctype_digit($request->limit) || $request->limit > $hitsPerPage))
    {
      $request->limit = $hitsPerPage;
    }

    // Execute next filter
    $filterChain->execute();
  }
}
