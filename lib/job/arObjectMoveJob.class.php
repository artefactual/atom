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
 * A worker to, given the HTTP GET parameters sent to advanced search,
 * replicate the search and export the resulting decriptions to CSV.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arObjectMoveJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('objectId', 'parentId');

  public function runJob($parameters)
  {
    $this->job->addNoteText($this->i18n->__('Moving object.'));
    $this->job->save();

    // Fetch object
    $objectId = $parameters['objectId'];

    if (($object = QubitObject::getById($objectId)) === null)
    {
      $this->error("Invalid object id: $objectId");

      return false;
    }

    // Fetch parent object
    $parentId = $parameters['parentId'];

    if (($parent = QubitObject::getById($parentId)) === null)
    {
      $this->error("Invalid parent id: $parentId");

      return false;
    }

    // In term treeview, root node links (href) to taxonomy, but it represents the term root object
    if ($object instanceOf QubitTerm && $parent instanceof QubitTaxonomy)
    {
      $object->parentId = QubitTerm::ROOT_ID;
    }
    else
    {
      $object->parentId = $parent->id;
    }

    $object->save();

    // Mark job as complete
    $this->info('Move complete.');
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
