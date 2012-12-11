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

    // CSV header
    $data = "referenceCode,physicalObjectName,title,creationDates\n";

    foreach ($this->resource->descendants->andSelf()->orderBy('rgt') as $informationObject)
    {
      // Ignore item if the user does not enough privileges
      if (!QubitAcl::check($informationObject, 'read'))
      {
        continue;
      }

      // Creation dates
      foreach ($informationObject->getDates(array('type_id' => QubitTerm::CREATION_ID)) as $event)
      {
        $creationDates[] = $event->startDate;
      }

      // Reference code, container name, title, creation dates
      foreach ($informationObject->getPhysicalObjects() as $item)
      {
        $data .= sprintf("%s,%s,%s,%s\n",
          $informationObject->referenceCode,
          $item->__toString(),
          $informationObject->__toString(),
          implode($creationDates, '|'));
      }

      unset($creationDates);
    }

    // Disable layout
    $this->setLayout(false);

    // Set the file name
    $this->getResponse()->setHttpHeader('Content-Disposition', "attachment; filename=report.csv");

    // Send $data as the response body
    $this->getResponse()->setContent($data);

    return sfView::NONE;
  }
}
