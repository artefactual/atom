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

class StaticPageIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    // HACK factor this into routing one day?
    switch (true)
    {
      case $this->resource instanceof QubitRepository:
        $this->forward('sfIsdiahPlugin', 'index');

        break;

      case $this->resource instanceof QubitDonor:
        $this->forward('donor', 'index');

        break;

      case $this->resource instanceof QubitRightsHolder:
        $this->forward('rightsholder', 'index');

        break;

      case $this->resource instanceof QubitUser:
        $this->forward('user', 'index');

        break;

      case $this->resource instanceof QubitActor:
        $this->forward('sfIsaarPlugin', 'index');

        break;

      case $this->resource instanceof QubitFunction:
        $this->forward('sfIsdfPlugin', 'index');

        break;

      case $this->resource instanceof QubitInformationObject:
        $this->forward('sfIsadPlugin', 'index');

        break;

      case $this->resource instanceof QubitAccession:
        $this->forward('accession', 'index');

        break;

      case $this->resource instanceof QubitDeaccession:
        $this->forward('deaccession', 'index');

        break;

      case $this->resource instanceof QubitStaticPage:

        break;

      default:
        $this->forward404();
    }
  }
}
