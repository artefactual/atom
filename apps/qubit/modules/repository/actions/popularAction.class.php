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
 * Show paginated list of hits to access log.
 *
 * @package    AccesstoMemory
 * @subpackage status
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class RepositoryPopularAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->context->user->isAdministrator())
    {
      // 403 - Forbidden
      $this->getResponse()->setStatusCode(403);

      return sfView::HEADER_ONLY;
    }

    $this->resource = $this->getRoute()->resource;
    if (!isset($this->resource))
    {
      $this->forward404();
    }

    // Expose these values to template
    $this->startDate = $request->start_date;
    $this->endDate = $request->end_date;

    // Get hits per object, and slug, in descending order of hits
    $criteria = new Criteria;
    $criteria->add(QubitAccessLog::ID, 0, Criteria::GREATER_THAN);
    $criteria->addSelectColumn(QubitAccessLog::OBJECT_ID);
    $criteria->addGroupByColumn(QubitAccessLog::OBJECT_ID);
    $criteria->addAsColumn('hits', 'COUNT('. QubitAccessLog::OBJECT_ID .')');

    // Filter by date
    if (
      (isset($request->start_date) && !empty($request->start_date))
      && (isset($request->end_date) && !empty($request->end_date))
    )
    {
      $criteria->add(
        QubitAccessLog::ACCESS_DATE,
        'DATE('. QubitAccessLog::ACCESS_DATE .") >= '". $request->start_date ."' AND DATE(". QubitAccessLog::ACCESS_DATE .") <= '". $request->end_date ."'",
        Criteria::CUSTOM
      );
    }
    else if (isset($request->start_date) && !empty($request->start_date)) 
    {
      $criteria->add(
        QubitAccessLog::ACCESS_DATE,
        'DATE('. QubitAccessLog::ACCESS_DATE .") >= '". $request->start_date ."'",
        Criteria::CUSTOM
      );
    }
    else if (isset($request->end_date) && !empty($request->end_date)) 
    {
      $criteria->add(
        QubitAccessLog::ACCESS_DATE,
        'DATE('. QubitAccessLog::ACCESS_DATE .") <= '". $request->end_date ."'",
        Criteria::CUSTOM
      );
    }

     // Sort results
    $criteria->addDescendingOrderByColumn('hits');
    $criteria->addAscendingOrderByColumn(QubitSlug::SLUG);

    // Join with slugs
    $criteria->addJoin(QubitAccessLog::OBJECT_ID, QubitSlug::OBJECT_ID);
    $criteria->addSelectColumn(QubitSlug::SLUG);

    // Join with information object tables
    $criteria->addJoin(QubitAccessLog::OBJECT_ID, QubitInformationObject::ID);
    $criteria->addSelectColumn(QubitInformationObject::ID);
    $criteria->addSelectColumn(QubitInformationObject::IDENTIFIER);
    $criteria->addSelectColumn(QubitInformationObject::PARENT_ID);

    $criteria->addJoin(QubitAccessLog::OBJECT_ID, QubitInformationObjectI18n::ID);
    $criteria->addSelectColumn(QubitInformationObjectI18n::TITLE);

    // "ancestor" will be a join of the information object table with itself
    // done to fetch the repository ID (if necessary)
    $criteria->addAlias('ancestor', QubitInformationObject::TABLE_NAME);

    $criteria->addMultipleJoin(
      array(
        array(QubitInformationObject::LFT, 'ancestor.LFT', Criteria::GREATER_THAN),
        array(QubitInformationObject::RGT, 'ancestor.RGT', Criteria::LESS_THAN)
      ),
      Criteria::LEFT_JOIN
    );

    // Find information objects in specified repository
    $c = new Criterion($criteria, QubitInformationObject::REPOSITORY_ID, $this->resource->id);
    $c2 = new Criterion($criteria, 'ancestor.repository_id', $this->resource->id);
    $c->addOr($c2);
    $criteria->add($c);

    // Paginate hits
    $this->pager = new QubitPager('QubitAccessLog');
    $this->pager->setMaxPerPage(10);
    $this->pager->setPage($request->page);
    $this->pager->setCriteria($criteria);

    $this->objects = $this->pager->getRows($criteria);

    // Look up reference codes and top-level parents
    $this->referenceCodes = array();
    $this->parents = array();
    foreach($this->objects as $object)
    {
      $resource = QubitInformationObject::getById($object['ID']);
      $this->referenceCodes[$object['ID']] = $resource->referenceCode;

      if ($object['PARENT_ID'] != QubitInformationObject::ROOT_ID)
      {
        foreach ($resource->ancestors->andSelf()->orderBy('rgt') as $item)
        {
          // Stop iteration before the root object is reached
          if (QubitInformationObject::ROOT_ID == $item->parentId)
          {
            $this->parents[($object['ID'])] = $item;
            break;
          }
        }
      }
    }

    // Calculate starting rank on this page
    $this->rank = (($this->pager->getPage() - 1) * $this->pager->getMaxPerPage()) + 1;
  }
}
