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

/**
 * Get current state data for information object edit form.
 *
 * @package    qubit
 * @subpackage oai
 * @version    svn: $Id: getRecordComponent.class.php 10288 2011-11-08 21:25:05Z mj $
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */

class qtOaiPluginGetRecordComponent extends sfComponents
{
  public function execute($request)
  {
    $request->setRequestFormat('xml');

    $oai_local_identifier_value = $request->identifier; //TODO: strip the trailing integer value from the full OAI Identifier to get the OaiLocalIdentifier
    $oai_local_identifier_id = QubitOai::getOaiIdNumber($oai_local_identifier_value);
    $this->informationObject = QubitInformationObject::getRecordByOaiID($oai_local_identifier_id);
    $request->setAttribute('informationObject', $this->informationObject);

    // just cut-and-paste from OaiIdentify action for now
    $this->date = gmdate('Y-m-d\TH:i:s\Z');
    $this->collectionsTable = QubitOai::getCollectionArray();
    $this->path = $request->getUriPrefix().$request->getPathInfo();
    $this->attributes = $this->request->getGetParameters();

    $this->attributesKeys = array_keys($this->attributes);
    $this->requestAttributes = '';
    foreach ($this->attributesKeys as $key)
    {
      $this->requestAttributes .= ' '.$key.'="'.$this->attributes[$key].'"';
    }
  }
}
