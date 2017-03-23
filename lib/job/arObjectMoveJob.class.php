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
  protected $extraRequiredParameters = array('objectId');

  public function runJob($parameters)
  {
    $this->job->addNoteText($this->i18n->__('Moving object.'));
    $this->job->save();

    // Fetch object
    if (($object = QubitObject::getById($parameters['objectId'])) === null)
    {
      $this->error("Invalid object id: {$parameters['objectId']}");

      return false;
    }

    // Change parent if requested
    if (isset($parameters['parentId']))
    {
      if (($parent = QubitObject::getById($parameters['parentId'])) === null)
      {
        $this->error("Invalid parent id: {$parameters['parentId']}");

        return false;
      }

      // In term treeview, root node links (href) to taxonomy, but it represents the term root object
      if ($object instanceOf QubitTerm && $parent instanceof QubitTaxonomy)
      {
        $newParentId = QubitTerm::ROOT_ID;
      }
      else
      {
        $newParentId = $parent->id;
      }

      // Avoid updating parent if not needed
      if ($object->parentId !== $newParentId)
      {
        $object->parentId = $newParentId;
        $object->save();
      }
    }

    // Move between siblings if requested
    if (isset($parameters['targetSiblingId']) && isset($parameters['targetPosition']))
    {
      if (($targetSibling = QubitObject::getById($parameters['targetSiblingId'])) === null)
      {
        $this->error("Invalid target sibling id: {$parameters['targetSiblingId']}");

        return false;
      }

      switch ($parameters['targetPosition'])
      {
        case 'before':
          $object->moveToPrevSiblingOf($targetSibling);

          break;

        case 'after':
          $object->moveToNextSiblingOf($targetSibling);

          break;

        default:
          $this->error("Invalid target position: {$parameters['targetPosition']}");

          return false;
      }
    }

    // Mark job as complete
    $this->info('Move complete.');
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
