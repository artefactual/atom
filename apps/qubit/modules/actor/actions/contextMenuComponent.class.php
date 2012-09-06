<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Actor contextMenu component
 *
 * @package qubit
 * @subpackage actor
 * @version svn: $Id: contextMenuComponent.class.php 10296 2011-11-09 21:48:33Z david $
 * @author Peter Van Garderen <peter@artefactual.com>
 * @author David Juhasz <david@artefactual.com>
 */
class ActorContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    $this->resource = $request->getAttribute('sf_route')->resource;

    $criteria = new Criteria;
    $criteria->add(QubitEvent::ACTOR_ID, $this->resource->id);
    $criteria->addJoin(QubitEvent::INFORMATION_OBJECT_ID, QubitInformationObject::ID);
    $criteria->addAscendingOrderByColumn(QubitEvent::TYPE_ID);

    // Sort info objects alphabetically (w/ fallback)
    $criteria->addAscendingOrderByColumn('title');
    $criteria = QubitCultureFallback::addFallbackCriteria($criteria, 'QubitInformationObject');

    // Filter draft descriptions
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    $this->relatedInfoObjects = array();
    foreach (QubitEvent::get($criteria) as $item)
    {
      $this->relatedInfoObjects[$item->type->getRole()][] = $item->informationObject;
    }

    // Get "subject of" information objects (name access point)
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
    $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);

    $this->subjectInfoObjects = array();
    foreach (QubitRelation::get($criteria) as $item)
    {
      $this->subjectInfoObjects[] = $item->subject;
    }
  }
}
