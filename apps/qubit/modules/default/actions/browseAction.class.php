<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class DefaultBrowseAction extends sfAction
{
  protected $query;

  protected function addFacet($name)
  {
    $facet = new Elastica_Facet_Terms($name);
    $facet->setField($name);
    $facet->setSize(50);

    $this->query->addFacet($facet);
  }

  public function execute($request)
  {
    $this->earlyExecute();

    if (empty($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->query->setLimit($request->limit);

    if (!empty($request->page))
    {
      $this->query->setFrom(($request->page - 1) * $request->limit);
    }

    foreach ($this::$FACETS as $name)
    {
      $this->addFacet($name);
    }
  }
}
