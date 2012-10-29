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

/**
 * Digital Object coverflow component
 *
 * @package    AtoM
 * @subpackage digitalobject
 * @author     david juhasz <david@artefactual.com>
 */
class DigitalObjectImageflowComponent extends sfComponent
{
  public function execute($request)
  {
    $this->thumbnails = array();

    // Set limit (null for no limit)
    if (!isset($request->showFullImageflow) || 'true' != $request->showFullImageflow)
    {
      $this->limit = sfConfig::get('app_hits_per_page', 10);
    }

    // Add thumbs
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);
    $criteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_THAN);
    $criteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_THAN);
    if (isset($this->limit))
    {
      $criteria->setLimit($this->limit);
    }

    foreach (QubitDigitalObject::get($criteria) as $item)
    {
      $thumbnail = $item->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID);

      if (!$thumbnail)
      {
        $thumbnail = QubitDigitalObject::getGenericRepresentation($item->mimeType, QubitTerm::THUMBNAIL_ID);
        $thumbnail->setParent($item);
      }

      $this->thumbnails[] = $thumbnail;
    }

    // Get total number of descendant digital objects
    $this->total = 0;
    if (isset($this->resource))
    {
      $criteria = new Criteria;
      $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);
      $criteria->add(QubitInformationObject::LFT, $this->resource->lft, Criteria::GREATER_THAN);
      $criteria->add(QubitInformationObject::RGT, $this->resource->rgt, Criteria::LESS_THAN);

      $this->total = BasePeer::doCount($criteria)->fetchColumn(0);
    }

    if (1 > count($this->thumbnails))
    {
      return sfView::NONE;
    }
  }
}
