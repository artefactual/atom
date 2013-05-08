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
class arOaiPluginListRecordsComponent extends sfComponent
{
  public function execute($request)
  {
    $this->requestname = $request;
    $request->setRequestFormat('xml');
    $this->date = gmdate('Y-m-d\TH:i:s\Z');
    $this->attributes = $request->getGetParameters();

    /*
     * If limit dates are not supplied, define them as ''
     */
    if (!isset($request->from))
    {
      $this->from = '';
    }
    else
    {
      $this->from = $request->from;
    }

    if (!isset($request->until))
    {
      $this->until = '';
    }
    else
    {
      $this->until = $request->until;
    }

    $this->collectionsTable = QubitOai::getCollectionArray();

    /*
     * If set is not supplied, define it as ''
     */
    if (!isset($request->set))
    {
      $collection = '';
    }
    else
    {
      $collection = QubitOai::getCollectionInfo($request->set, $this->collectionsTable);
    }

    //Get the records according to the limit dates and collection
    $this->records = QubitInformationObject::getUpdatedRecords($this->from, $this->until, $collection);
    $this->publishedRecords = array();
    foreach ($this->records as $record)
    {
      if ($record->getPublicationStatus()->statusId == QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID)
      {
        $this->publishedRecords[] = $record;
      }
    }
    $this->recordsCount = count($this->publishedRecords);
    $this->path = $request->getUriPrefix().$request->getPathInfo();

    $this->attributesKeys = array_keys($this->attributes);
    $this->requestAttributes = '';
    foreach ($this->attributesKeys as $key)
    {
      $this->requestAttributes .= ' '.$key.'="'.$this->attributes[$key].'"';
    }
  }
}
