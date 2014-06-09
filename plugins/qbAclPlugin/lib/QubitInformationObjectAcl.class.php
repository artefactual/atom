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
 * Custom ACL for QubitInformationObject resources
 *
 * @package    qbAclPlugin
 * @subpackage acl
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitInformationObjectAcl extends QubitAcl
{
  // Add viewDraft and publish actions to list
  public static $ACTIONS = array(
    'read' => 'Read',
    'create' => 'Create',
    'update' => 'Update',
    'delete' => 'Delete',
    'translate' => 'Translate',
    'viewDraft' => 'View draft',
    'publish' => 'Publish',
    'readMaster' => 'Access master',
    'readReference' => 'Access reference',
    'readThumbnail' => 'Access thumbnail'
  );

  public static function getParentForIsAllowed($resource, $action)
  {
    // If trying to publish a new info object, check permissions against parent
    if ('publish' == $action)
    {
      return $resource->parent;
    }
  }
}

