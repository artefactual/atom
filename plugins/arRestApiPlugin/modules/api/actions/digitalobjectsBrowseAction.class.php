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

class ApiDigitalObjectsBrowseAction extends QubitApiAction
{
  protected function get($request)
  {
    $data = array();

    $results = $this->getResults($request);

    $data['results'] = $results['results'];
    $data['total'] = $results['total'];

    return $data;
  }

  protected function getResults($request)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, NULL);
    $criteria->addAscendingOrderByColumn(QubitDigitalObject::ID);

    // Page results
    $this->pager = new QubitPager('QubitDigitalObject');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage(empty($request->limit) ? 10 : $request->limit);
    $this->pager->setPage($request->page);

    $resultSet = $this->pager->getResults();

    // Build array from results
    $results = array();
    foreach ($resultSet as $do)
    {
      $fields = array(
        'name' => $do->name,
        'path' => $do->path,
        'byte_size' => intval($do->byteSize),
        'mime_type' => $do->mimeType
      );

      // Look up media type
      if (null !== $do->mediaTypeId)
      {
        $criteria = new Criteria;
        $criteria->addJoin(QubitTerm::ID, $do->mediaTypeId);

        if (null !== ($typeTerm = QubitTerm::getOne($criteria))) { $fields['media_type'] = $typeTerm->name; }
      }

      // Add related property data
      $propertyFields = array(
        'objectUUID' => 'file_uuid',
        'aipUUID' => 'aip_uuid'
      );

      foreach ($propertyFields as $propertyName => $apiFieldLabel)
      {
        $criteria = new Criteria;
        $criteria->add(QubitProperty::NAME, $propertyName);
        $criteria->add(QubitProperty::OBJECT_ID, $do->informationObjectId);

        if (null !== ($uuidProperty = QubitProperty::getOne($criteria))) { $fields[$apiFieldLabel] = $uuidProperty->value; }
      }

      $results[$do->id] = $fields;
    }

    return
      array(
        'results' => $results,
        'total' => $this->pager->getNbResults());
  }
}
