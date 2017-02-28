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
 * Generate  listRecordsAction response of the OAI-PMH protocol for the Access to Memory (AtoM)
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class arOaiPluginListRecordsComponent extends arOaiPluginComponent
{
  public function execute($request)
  {
    $this->requestname = $request;

    $this->setUpdateParametersFromRequest($request);

    $options = ($request->metadataPrefix == 'oai_ead') ? array('topLevel' => true, 'limit' => 1) : array();
    $this->getUpdates($options);

    // If metadata requested is EAD and results were found, determine if any are missing corresponding cache files
    $this->identifiersWithMissingCacheFiles = array();
    if ($request->metadataPrefix == 'oai_ead' && count($this->publishedRecords))
    {
      foreach ($this->publishedRecords as $resource)
      {
        if (!arOaiPluginComponent::cachedMetadataExists($resource, 'oai_ead'))
        {
          array_push($this->identifiersWithMissingCacheFiles, $resource->getOaiIdentifier());
        }
      }
    }

    $this->metadataPrefix = $request->metadataPrefix;
    $this->setRequestAttributes($request);
  }
}
