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

/**
 * Return list of functions for autocomplete (XHR) response.
 *
 * @package    AccesstoMemory
 * @subpackage function
 * @author     David Juhasz <david@artefactual.com>
 */
class FunctionAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $criteria = new Criteria;
    $criteria->addJoin(QubitFunction::ID, QubitFunctionI18n::ID);
    $criteria->add(QubitFunctionI18n::CULTURE, $this->context->user->getCulture());

    if (isset($request->query))
    {
      $criteria->add(QubitFunctionI18n::AUTHORIZED_FORM_OF_NAME, "$request->query%", Criteria::LIKE);
    }

    // Exclude the calling function from the list
    $params = $this->context->routing->parse(Qubit::pathInfo($request->getReferer()));
    $resource = $params['_sf_route']->resource;
    if (isset($resource->id))
    {
      $criteria->add(QubitFunction::ID, $resource->id, Criteria::NOT_EQUAL);
    }

    // Page results
    $this->pager = new QubitPager('QubitFunction');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage(1);

    $this->setTemplate('list');
  }
}
