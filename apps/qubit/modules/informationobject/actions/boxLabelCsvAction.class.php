<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
  *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class InformationObjectBoxLabelCsvAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    if (!isset($this->resource))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'read'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Use php://temp stream, max 2M
    $csv = fopen('php://temp/maxmemory:'. (2*1024*1024), 'r+');

    // Write CSV header
    fputcsv($csv, array('referenceCode', 'physicalObjectName', 'title', 'creationDates'));

    foreach ($this->resource->descendants->andSelf()->orderBy('rgt') as $informationObject)
    {
      // Ignore item if the user does not enough privileges
      if (!QubitAcl::check($informationObject, 'read'))
      {
        continue;
      }

      // Creation dates
      foreach ($informationObject->getDates(array('type_id' => QubitTerm::CREATION_ID)) as $item)
      {
        $creationDates[] = $item->getDate(array('cultureFallback' => true));
      }

      // Write reference code, container name, title, creation dates
      foreach ($informationObject->getPhysicalObjects() as $item)
      {
        fputcsv($csv, array(
          $informationObject->referenceCode,
          $item->__toString(),
          $informationObject->__toString(),
          implode($creationDates, '|')));
      }

      unset($creationDates);
    }

    // Rewind the position of the pointer
    rewind($csv);

    // Disable layout
    $this->setLayout(false);

    // Set the file name
    $this->getResponse()->setHttpHeader('Content-Disposition', "attachment; filename=report.csv");

    // Send $csv content as the response body
    $this->getResponse()->setContent(stream_get_contents($csv));

    return sfView::NONE;
  }
}
