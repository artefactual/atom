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
 * Browse list for digital objects
 *
 * @package    AccesstoMemory
 * @subpackage digitalobject
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = 25;
    }

    $this->mediaType = QubitTerm::getById($request->mediatype);
    if (!$this->mediaType instanceOf QubitTerm)
    {
      $this->forward404();
    }

    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::MEDIA_TYPE_ID, $this->mediaType->id);
    $criteria->add(QubitDigitalObject::SEQUENCE);
    $criteria->addJoin(QubitDigitalObject::INFORMATION_OBJECT_ID, QubitInformationObject::ID);

    // Sort by name ascending
    $criteria->addAscendingOrderByColumn(QubitDigitalObject::NAME);

    // Filter draft descriptions
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    $this->pager = new QubitPager('QubitDigitalObject');
    $this->pager->setCriteria($criteria);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->setPage($request->page);
  }
}
