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
 * Custom ACL rules for QubitActor resources.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitActorAcl extends QubitAcl
{
    // Add viewDraft and publish actions to list
    public static $ACTIONS = [
        'read' => 'Read',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
        'translate' => 'Translate',
        'viewDraft' => 'View draft',
        'publish' => 'Publish',
        'readMaster' => 'Access master',
        'readReference' => 'Access reference',
        'readThumbnail' => 'Access thumbnail',
    ];

    protected static $_digitalObjectActions = ['readMaster', 'readReference', 'readThumbnail'];

    /**
     * Do custom ACL checks for QubitActor resources.
     *
     * @param myUser     $user     to authorize
     * @param QubitActor $resource target of the requested action
     * @param string     $action   requested for authorization (e.g. 'read')
     * @param null|array $options  optional parameters
     *
     * @return bool true if the access request is authorized
     */
    public static function isAllowed($user, $resource, $action, $options = [])
    {
        // Do custom ACL checks for digital object actions
        if (in_array($action, self::$_digitalObjectActions)) {
            return QubitActorAcl::isDigitalObjectActionAllowed(
                $user,
                $resource,
                $action,
                $options
            );
        }

        // Call QubitAcl::isAllowed(), when no special rules apply
        return parent::isAllowed($user, $resource, $action, $options);
    }

    /**
     * Check if $user is authorized to do $action on the digital object linked to
     * this actor.
     *
     * @param QubitUser $user     to authorize
     * @param string    $action   being requested (e.g. "readReference")
     * @param mixed     $resource
     * @param mixed     $options
     *
     * @return bool true if $user is authorized to perform $action
     */
    private static function isDigitalObjectActionAllowed(
        $user,
        $resource,
        $action,
        $options = []
    ) {
        // All users can access actor reference representations and thumbnails
        if (in_array($action, ['readReference', 'readThumbnail'])) {
            return true;
        }

        // All users are authorized to read text (PDF) masters
        if ('readMaster' == $action && $resource->hasTextDigitalObject()) {
            return true;
        }

        // All authenticated users are authorized to read actor master DOs
        return $user->isAuthenticated();
    }
}
