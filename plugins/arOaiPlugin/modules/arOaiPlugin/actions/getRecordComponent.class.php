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
 * Get current state data for information object edit form.
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */

class arOaiPluginGetRecordComponent extends arOaiPluginComponent
{
  public function execute($request)
  {
    $request->setRequestFormat('xml');
    $this->date = gmdate('Y-m-d\TH:i:s\Z');

    $oai_local_identifier_id = QubitOai::getOaiIdNumber($request->identifier);
    $this->informationObject = QubitInformationObject::getRecordByOaiID($oai_local_identifier_id);
    $request->setAttribute('informationObject', $this->informationObject);

    $this->collectionsTable = QubitOai::getCollectionArray();

    $this->path = $request->getUriPrefix().$request->getPathInfo();

    $this->setRequestAttributes($request);
  }
}
