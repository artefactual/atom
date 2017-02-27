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
    $oaiLocalIdentifierId = QubitOai::getOaiIdNumber($request->identifier);
    $this->record = QubitInformationObject::getRecordByOaiID($oaiLocalIdentifierId);

    // If metadata requested is EAD and file doesn't exist, redirect to error response as EAD can take a long time to dynamically generate
    if ($request->metadataPrefix == 'oai_ead' && !arOaiPluginComponent::cachedMetadataExists($this->record, $request->metadataPrefix))
    {
      $this->errorCode = 'cannotDisseminateFormat';
      $this->errorMsg = 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.';
    }
    else
    {
      $this->metadataPrefix = $request->metadataPrefix;
      $request->setAttribute('record', $this->record);
      $this->setRequestAttributes($request);
    }
  }
}
