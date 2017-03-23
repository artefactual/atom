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

class InformationObjectFullWidthTreeViewMoveAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;
    $i18n = sfContext::getInstance()->i18n;

    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->response->setStatusCode(404);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move failed: resource not found'))));
    }

    if (!$this->getUser()->isAuthenticated())
    {
      $this->response->setStatusCode(401);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move not allowed: log in required'))));
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      $this->response->setStatusCode(403);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move not allowed: not enough permissions'))));
    }

    $oldPosition = $request->getParameter('oldPosition');
    $newPosition = $request->getParameter('newPosition');

    // Empty or non numeric positions are not allowed
    if (!is_numeric($oldPosition) || !is_numeric($newPosition))
    {
      $this->response->setStatusCode(400);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move failed: new and old positions required as numbers'))));
    }

    // Moving to the same position
    if ($oldPosition == $newPosition)
    {
      $this->response->setStatusCode(400);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move not needed: new and old positions are the same'))));
    }

    // Check current positions to avoid mismatch
    $sql = "SELECT id FROM information_object WHERE parent_id = :parentId ORDER BY lft;";
    $params = array(':parentId' => $this->resource->parentId);
    $children = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));

    if (array_search(array('id' => $this->resource->id), $children) != $oldPosition)
    {
      $this->response->setStatusCode(400);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move failed: mismatch in current position'))));
    }

    if ($newPosition >= count($children))
    {
      $this->response->setStatusCode(400);

      return $this->renderText(json_encode(array('error' => $i18n->__('Move failed: new position outside the range'))));
    }

    // TODO? Check if another move job is running over the same parent

    $params = array(
      'objectId' => $this->resource->id,
      'targetSiblingId' => $children[$newPosition]['id'],
      'targetPosition' => $newPosition > $oldPosition ? 'after' : 'before'
    );

    QubitJob::runJob('arObjectMoveJob', $params);

    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

    $jobManageUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
    $jobManageLink = '<a href="'. $jobManageUrl . '">'. $i18n->__('job management') .'</a>';

    $message = '<strong>'. $i18n->__('Move initiated.') .'</strong> ';
    $message .= $i18n->__("If job hasn't already completed, check %1% page to determine present status.", array('%1%' => $jobManageLink));

    $this->response->setStatusCode(201);

    return $this->renderText(json_encode(array('success' => $message)));
  }
}
