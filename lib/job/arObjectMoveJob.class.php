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
 * A job to move an object to a new parent or to another position among its siblings
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
    $this->info($this->i18n->__('Moving object (id: %1)', array('%1' => $parameters['objectId'])));

    // Fetch object
    if (($object = QubitObject::getById($parameters['objectId'])) === null)
    {
      $this->error($this->i18n->__('Invalid object id'));

      return false;
    }

    // Change parent if requested
    if (isset($parameters['parentId']))
    {
      if (($parent = QubitObject::getById($parameters['parentId'])) === null)
      {
        $this->error($this->i18n->__('Invalid parent (id: %1)', array('%1' => $parameters['parentId'])));

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
        $this->info($this->i18n->__('Moving object to parent (id: %1)', array('%1' => $parameters['parentId'])));

        $object->parentId = $newParentId;
        $object->save();
      }
    }

    // Move between siblings if requested
    if (isset($parameters['oldPosition']) && isset($parameters['newPosition']))
    {
      $this->info($this->i18n->__('Moving object between siblings'));

      // Check current positions to avoid mismatch
      $sql = "SELECT id FROM information_object WHERE parent_id = :parentId ORDER BY lft;";
      $params = array(':parentId' => $object->parentId);
      $children = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));

      if (array_search(array('id' => $object->id), $children) != $parameters['oldPosition'])
      {
        $this->error($this->i18n->__('Mismatch in current position'));

        return false;
      }

      if ($parameters['newPosition'] >= count($children))
      {
        $this->error($this->i18n->__('New position outside the range'));

        return false;
      }

      // Get target sibling and position in relation to it
      $targetSiblingId = $children[$parameters['newPosition']]['id'];
      $targetPosition = $parameters['newPosition'] > $parameters['oldPosition'] ? 'after' : 'before';

      if (($targetSibling = QubitObject::getById($targetSiblingId)) === null)
      {
        $this->error($this->i18n->__('Invalid target sibling (id: %1)', array('%1' => $targetSiblingId)));

        return false;
      }

      switch ($targetPosition)
      {
        case 'before':
          $this->info($this->i18n->__('Moving object before sibling (id: %1)', array('%1' => $targetSiblingId)));
          $object->moveToPrevSiblingOf($targetSibling);

          break;

        case 'after':
          $this->info($this->i18n->__('Moving object after sibling (id: %1)', array('%1' => $targetSiblingId)));
          $object->moveToNextSiblingOf($targetSibling);

          break;

        default:
          $this->error($this->i18n->__('Invalid target position (%1)', array('%1' => $targetPosition)));

          return false;
      }
    }

    // Mark job as completed
    $this->info('Move completed.');
    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
