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

class qtSwordPluginServicedocumentAction extends sfAction
{
  public function execute($request)
  {
    $this->version = '1.3';
    $this->verbose = 'false';
    $this->noOp = 'false';
    $this->maxUploadSize = QubitDigitalObject::getMaxUploadSize() / 1024; // From bytes to kilobytes
    $this->mediation = 'false'; // Should be based in auth + X-On-Behalf-Of

    if (isset($request->getAttribute('sf_route')->resource))
    {
      $this->resource = $this->getRoute()->resource;

      $this->title = $this->resource->__toString();

      $this->workspaces = $this->resource->getChildren();
    }
    else
    {
      $this->title = sfConfig::get('app_siteTitle');

      $criteria = new Criteria;
      $criteria->add(QubitInformationObject::PARENT_ID, QubitInformationObject::ROOT_ID, Criteria::IN);

      $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

      $this->workspaces = QubitInformationObject::get($criteria);
    }

    $this->response->setHttpHeader('Content-Type', 'application/atom+xml; charset="utf-8"');

    $request->setRequestFormat('xml');
  }
}
