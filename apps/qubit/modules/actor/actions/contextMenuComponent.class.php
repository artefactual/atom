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
 * Actor contextMenu component
 *
 * @package AccesstoMemory
 * @subpackage actor
 * @author Peter Van Garderen <peter@artefactual.com>
 * @author David Juhasz <david@artefactual.com>
 */
class ActorContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $this->resource = $request->getAttribute('sf_route')->resource;

    // Arrays used to store all the descriptions related to this resource
    // and the paginated results related by the event type
    $this->relatedInfoObjects = $this->pagers = array();

    // Get "subject of" information objects (name access point)
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitInformationObject::ID);

    // Filter draft descriptions
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    // Paginate results
    $pager = new QubitPager('QubitInformationObject');
    $pager->setCriteria($criteria);
    $pager->setMaxPerPage($request->limit);
    $pager->setPage($request->page);

    $role = __('Subject');
    if (0 < count($pager->getResults()))
    {
      // Add pager only if needed
      if ($pager->getNbResults() > $request->limit)
      {
        $this->pagers[$role] = $pager;
      }

      $this->relatedInfoObjects[$role] = $pager->getResults();
    }

    // Iterate over event types to get the related descriptions for each type of event
    foreach (QubitTerm::getEventTypes() as $eventType)
    {
      $criteria = new Criteria;
      $criteria->add(QubitEvent::ACTOR_ID, $this->resource->id);
      $criteria->add(QubitEvent::TYPE_ID, $eventType->id);
      $criteria->addJoin(QubitEvent::OBJECT_ID, QubitInformationObject::ID);

      // Sort info objects alphabetically (w/ fallback)
      $criteria->addAscendingOrderByColumn('title');
      $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');

      // Filter draft descriptions
      $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

      // Paginate results
      $pager = new QubitPager('QubitInformationObject');
      $pager->setCriteria($criteria);
      $pager->setMaxPerPage($request->limit);
      $pager->setPage($request->page);

      if (0 < count($pager->getResults()))
      {
        // Add pager only if needed
        if ($pager->getNbResults() > $request->limit)
        {
          $this->pagers[$eventType->getRole()] = $pager;
        }

        $this->relatedInfoObjects[$eventType->getRole()] = $pager->getResults();
      }
    }
  }
}
